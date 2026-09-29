define(['jquery'], function($) {
    /**
     * A Module that handles Compilatio ajax/API calls
     */

    /**
     * disableCompilatioButtons
     * Disable Compilatio buttons (during multiple ajax/API calls)
     */
    function disableCompilatioButtons() {
        $('.cmp-action-btn').each(function() {
            $(this).attr('disabled', 'disabled');
            $(this).addClass('disabled');
            $(this).attr('href', '#');
        });
    }

    var exports = {};
    var isInMaintenance = false;
    var displayIntervals = {};

    $(document).ready(function() {
        if ($('#maintenance-modal').length) {
            isInMaintenance = true;
            disableCompilatioButtons();
        }
    });

    /**
     * Start analyses
     * @param {string} message
     * @param {string} basepath
     * @param {number} cmid
     * @param {Array|null} selectedstudents
     * @param {Array} selectedquestions
     * @param {number|null} quizid
     * @param {string} scope
     */
    function startAnalyses(message, basepath, cmid, selectedstudents = null, selectedquestions = [], quizid = null, scope = 'all') {
        disableCompilatioButtons();

        $('#cmp-dropdown-menu').removeClass('show');

        $("#cmp-alerts").append(`
                <div class='cmp-alert cmp-alert-info'>`
                + message +
                `<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>`
            );

        let params = {
            'cmid': cmid,
            'selectedquestions': selectedquestions,
            'quizid': quizid,
            'scope': scope,
            'sesskey': M.cfg.sesskey
        };

        if (scope === 'filtered') {
            addAssignFilterParams(params);
        }

        if (selectedstudents !== null) {
            params.selectedstudents = selectedstudents.toString();
        }

        $.post(basepath + '/plagiarism/compilatio/ajax/start_multiple_analyses.php', params, function() {
            window.location.reload();
        });
    }

    /**
     * Get selected student
     * @param {string} basepath
     * @param {number} cmid
     */
    exports.getSelectedStudent = function(basepath, cmid) {
        $(document).ready(function() {
            const studentSelector = $('#student-select');
            const statisticsContainer = $('#statistics-container');

            studentSelector.on('change', function() {
                const selectedstudent = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: basepath + '/plagiarism/compilatio/ajax/stats_per_student.php',
                    data: {selectedstudent: selectedstudent, cmid: cmid},
                    success: function(response) {
                        statisticsContainer.html(response);
                    },
                    error: function() {
                        // Handle error silently or show user-friendly message
                        statisticsContainer.html('<div class="alert alert-warning">Unable to load statistics.</div>');
                    }
                });
            });

            $('#previous-student').on('click', function() {
                changeSelectedStudent(studentSelector.prop('selectedIndex'), -1);
            });

            $('#next-student').on('click', function() {
                changeSelectedStudent(studentSelector.prop('selectedIndex'), 1);
            });

            /**
             * Change selected student.
             * @param {number} selectedIndex - Current selected index.
             * @param {number} direction - Direction to change index
             */
            function changeSelectedStudent(selectedIndex, direction) {
                var newIndex = selectedIndex + direction;
                const maxIndex = studentSelector.find('option').length - 1;

                if (newIndex === -1) {
                    newIndex = maxIndex;
                } else if (newIndex > maxIndex) {
                    newIndex = 0;
                }

                studentSelector.prop('selectedIndex', newIndex).change();
            }
        });
    };

    /**
     * Start all analyses
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     * @param {string} module
     * @param {boolean} assignhasfilters
     */
    exports.startAllAnalysis = function(basepath, cmid, message, module, assignhasfilters) {
        $(document).ready(function() {
            $('#cmp-dropdown-menu').on('click', function(event) {
                event.stopPropagation();
            });

            if (module !== 'assign') {
                $('.cmp-start-btn').click(function() {
                    startAnalyses(message, basepath, cmid);
                });
                return;
            }

            $('#cmp-start-visible-btn').click(function() {
                const selectedUsers = getSelectedAssignUsers();
                if (selectedUsers.length > 0) {
                    startAnalyses(message, basepath, cmid, selectedUsers, [], null, 'selected');
                    return;
                }

                if (hasApplicableAssignFilters(assignhasfilters)) {
                    startAnalyses(message, basepath, cmid, null, [], null, 'filtered');
                    return;
                }

                startAnalyses(message, basepath, cmid, getVisibleAssignUsers(), [], null, 'page');
            });

            $('#cmp-start-all-btn').click(function() {
                startAnalyses(message, basepath, cmid, null, [], null, 'all');
            });
        });
    };

    /**
     * Get Assign student selection checkboxes from the grading table.
     *
     * @return {jQuery}
     */
    function getAssignUserCheckboxes() {
        return $('input[name="selectedusers"]').filter(function() {
            return /^\d+$/.test(String($(this).val() || ''));
        });
    }

    /**
     * Get user ids displayed on the current Assign grading page.
     *
     * @return {Array}
     */
    function getVisibleAssignUsers() {
        return getAssignUserCheckboxes().map(function() {
            return $(this).val();
        }).get();
    }

    /**
     * Get explicitly selected user ids on the current Assign grading page.
     *
     * @return {Array}
     */
    function getSelectedAssignUsers() {
        return getAssignUserCheckboxes().filter(':checked').map(function() {
            return $(this).val();
        }).get();
    }

    /**
     * Check if Assign grading filters are active.
     *
     * Moodle may keep initials filters in the persistent grading table preferences.
     *
     * @return {boolean}
     */
    function hasAssignFilters(assignhasfilters) {
        if (assignhasfilters) {
            return true;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const hasUrlFilter = getAssignFilterParams().some(function(param) {
            return urlParams.has(param) && isActiveAssignFilterParam(param, urlParams.get(param));
        });

        return hasUrlFilter || hasAssignInitialsFilter() || hasAssignClearFiltersLink();
    }

    /**
     * Check if the current page exposes enough Assign filter state to safely use the filtered scope.
     *
     * Persisted Moodle preferences can make the server report active filters even when the current
     * page no longer carries the matching request state. In that case, fallback to the visible page.
     *
     * @param {boolean} assignhasfilters
     * @return {boolean}
     */
    function hasApplicableAssignFilters(assignhasfilters) {
        if (!hasAssignFilters(assignhasfilters)) {
            return false;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const hasApplicableUrlFilter = getAssignFilterParams().some(function(param) {
            return urlParams.has(param) && isActiveAssignFilterParam(param, urlParams.get(param));
        });

        return hasApplicableUrlFilter || hasAssignInitialsFilter();
    }

    /**
     * Get Assign filter params handled by the grading table.
     *
     * @return {Array}
     */
    function getAssignFilterParams() {
        return [
            'status',
            'group',
            'tifirst',
            'tilast',
            'search',
            'userid',
            'workflowfilter',
            'markingallocationfilter',
            'suspendedparticipantsfilter'
        ];
    }

    /**
     * Check if an Assign filter URL param has an active value.
     *
     * @param {string} param
     * @param {string|null} value
     * @return {boolean}
     */
    function isActiveAssignFilterParam(param, value) {
        if (value === null || value === '') {
            return false;
        }

        if (param === 'status') {
            return value !== 'none';
        }

        if (
            param === 'group'
                || param === 'userid'
                || param === 'markingallocationfilter'
                || param === 'suspendedparticipantsfilter'
        ) {
            return value !== '0';
        }

        return true;
    }

    /**
     * Check if Assign initials filters are active in the persistent grading table state.
     *
     * @return {boolean}
     */
    function hasAssignInitialsFilter() {
        const gradingTable = $('[data-table-uniqueid^="mod_assign_grading-"]');
        const firstInitial = gradingTable.attr('data-table-first-initial');
        const lastInitial = gradingTable.attr('data-table-last-initial');

        return Boolean(firstInitial || lastInitial);
    }

    /**
     * Check Moodle's reset filters link for filters stored in user preferences.
     *
     * @return {boolean}
     */
    function hasAssignClearFiltersLink() {
        return $('a[href*="action=grading"][href*="tifirst="][href*="tilast="][href*="status="]').length > 0;
    }

    /**
     * Add Assign filter URL params to the AJAX request.
     *
     * @param {Object} params
     */
    function addAssignFilterParams(params) {
        const urlParams = new URLSearchParams(window.location.search);
        getAssignFilterParams().forEach(function(param) {
            if (urlParams.has(param)) {
                params[param] = urlParams.get(param);
            }
        });
    }

    /**
     * Start analyses on selected students
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     */
    exports.startAnalysesOnSelectedStudents = function(basepath, cmid, message) {
        $(document).ready(function() {
            const startSelectedStudentsBtn = $('#start-selected-students-btn').hide();
            const checkboxes = getAssignUserCheckboxes().add('#selectall');

            /**
             * Update button visibility.
             */
            function updateButtonVisibility() {
                const selectedUsers = getSelectedAssignUsers();
                if (selectedUsers.length > 0) {
                    startSelectedStudentsBtn.show();
                } else {
                    startSelectedStudentsBtn.hide();
                }
            }

            checkboxes.on('change', updateButtonVisibility);
            startSelectedStudentsBtn.click(function() {
                const selectedUsers = getSelectedAssignUsers();
                if (selectedUsers.length > 0) {
                    startAnalyses(message, basepath, cmid, selectedUsers, [], null, 'selected');
                }
            });
        });
    };

    /**
     * Start analyses on selected questions
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     * @param {number} quizid
     */
    exports.startAnalysesOnSelectedQuestions = function(basepath, cmid, message, quizid) {
        $(document).ready(function() {
            const startSelectedQuestionsBtn = $('#start-selected-questions-btn');

            startSelectedQuestionsBtn.click(function() {
                var checkedcheckboxes = $('.checkbox-question-selector:checked');
                var selectedquestions = [];
                checkedcheckboxes.each(function() {
                    selectedquestions.push($(this).val());
                });

                if (selectedquestions.length > 0) {
                    startAnalyses(message, basepath, cmid, null, selectedquestions, quizid, 'selected');
                }
            });
        });
    };

    /**
     * Send unsent documents
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     */
    exports.sendUnsentDocs = function(basepath, cmid, message) {
        $(document).ready(function() {
            var sendUnsentDocs = $('#cmp-send-btn');
            sendUnsentDocs.click(function() {
                disableCompilatioButtons();
                $("#cmp-alerts").append("<div class='cmp-alert cmp-alert-info'>"
                    + message
                    + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");

                $.post(basepath + '/plagiarism/compilatio/ajax/send_unsent_docs.php', {
                    'cmid': cmid,
                    'sesskey': M.cfg.sesskey
                }, function() {
                    window.location.reload();
                });
            });
        });
    };

    /**
     * Reset documents in error
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     */
    exports.resetDocsInError = function(basepath, cmid, message) {
        $(document).ready(function() {
            var resetDocsInError = $('#cmp-reset-btn');
            resetDocsInError.click(function() {
                disableCompilatioButtons();
                $("#cmp-alerts").append("<div class='cmp-alert cmp-alert-info'>"
                    + message
                    + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");

                $.post(basepath + '/plagiarism/compilatio/ajax/reset_docs_in_error.php', {
                    'cmid': cmid,
                    'sesskey': M.cfg.sesskey
                }, function() {
                    window.location.reload();
                });
            });
        });
    };

    /**
     * Update score settings
     * @param {string} basepath
     * @param {number} cmid
     * @param {Array} scores
     */
    exports.updateScoreSettings = function(basepath, cmid, scores) {
        $(document).ready(function() {
            var scoresettings = $('#score-settings-ignored');

            scoresettings.click(function() {
                scoresettings.css('background-color', 'grey');
                scoresettings.html(`<div class="spinner-border spinner-border-sm" role="status">
                        <span class="sr-only">
                            Loading...
                        </span>
                    </div>`);

                $('.checkbox-score-settings').attr("disabled", true);

                var checkedcheckboxes = $('.checkbox-score-settings:checked');
                var checkedvalues = [];
                checkedcheckboxes.each(function() {
                    checkedvalues.push($(this).val());
                });

                $.ajax({
                    type: 'POST',
                    url: basepath + '/plagiarism/compilatio/ajax/update_score_settings.php',
                    data: {
                        cmid: cmid,
                        checkedvalues: checkedvalues,
                        scores: scores,
                        sesskey: M.cfg.sesskey
                    },
                    success: function() {
                        let url = new URL(window.location.href);

                        if (!url.searchParams.get('refreshAllDocs')) {
                            url.searchParams.append('refreshAllDocs', 'true');
                            window.location.href = url.href;
                        } else {
                            window.location.reload();
                        }
                    },
                    error: function() {
                        // Handle error silently or reload on error
                        window.location.reload();
                    }
                });
            });
        });
    };

    /**
     * Check user info
     * @param {string} basepath
     * @param {number} cmid
     */
    exports.checkUserInfo = function(basepath, cmid) {
        $(document).ready(function() {
            $.post(basepath + '/plagiarism/compilatio/ajax/check_user_info.php', {
                'cmid': cmid,
                'sesskey': M.cfg.sesskey
            });
        });
    };

    /**
     * Display document frame
     * @param {string} basepath
     * @param {boolean} cantriggeranalysis
     * @param {number} cmpfileid
     * @param {boolean} canviewreport
     * @param {boolean} isteacher
     * @param {string} url
     * @param {string} domid
     */
    function displayDocumentFrame(basepath, cantriggeranalysis, cmpfileid, canviewreport, isteacher, url, domid) {
        $.post(basepath + '/plagiarism/compilatio/ajax/display_document_frame.php',
            {cantriggeranalysis, cmpfileid, canviewreport, isteacher, url},
        function(response) {
            if (!response || response.error || typeof response.html !== 'string') {
                clearInterval(displayIntervals[domid]);
                delete displayIntervals[domid];
                return;
            }

            let el = $('#cmp-' + domid);
            el.empty().append(response.html);

            setTimeout(() => {
                if (isteacher) {
                    var refreshScoreBtn = $('#cmp-' + domid + ' .cmp-similarity');
                    refreshScoreBtn.on("mouseover", () => {
                        refreshScoreBtn.find('i').show();
                        refreshScoreBtn.find('span').hide();
                    });
                    refreshScoreBtn.on("mouseout", () => {
                        refreshScoreBtn.find('i').hide();
                        refreshScoreBtn.find('span').show();
                    });
                    refreshScoreBtn.click(function() {
                        if (isInMaintenance) {
                            return;
                        }
                        $('#cmp-' + domid + ' #cmp-score-icons').remove();
                        refreshScoreBtn.empty();
                        $.post(basepath + '/plagiarism/compilatio/ajax/update_score.php', {
                            'docId': cmpfileid,
                            'sesskey': M.cfg.sesskey
                        }, function(res) {
                            refreshScoreBtn.replaceWith(res);
                        });
                    });
                }

                var toogleIndexingStateBtn = $('#cmp-' + domid + ' .cmp-library');
                toogleIndexingStateBtn.click(function() {
                    if (isInMaintenance) {
                        return;
                    }
                    var i = $(this).find('i');
                    var indexingState = i.is('.cmp-library-in') ? 0 : 1;
                    i.removeClass();
                    i.parent().attr('title', '');
                    $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php',
                        {
                            'docId': cmpfileid,
                            'indexingState': indexingState,
                            'sesskey': M.cfg.sesskey
                        },
                    function(res) {
                        if (res.status === 'ok') {
                            if (indexingState === 0) {
                                i.addClass('cmp-library-out fa-times-circle fa');
                            } else {
                                i.addClass('cmp-library-in fa-check-circle fa');
                            }
                            i.parent().attr('title', res.text);
                        }
                    });
                });

                var startAnalysisBtn = $('#cmp-' + domid + ' .cmp-start-btn');
                startAnalysisBtn.click(function() {
                    if (isInMaintenance) {
                        return;
                    }
                    startAnalysisBtn.find('i').removeClass('fa-play-circle').addClass('fa-spinner fa-spin');

                    $.post(basepath + '/plagiarism/compilatio/ajax/start_analysis.php', {
                        'docId': cmpfileid,
                        'sesskey': M.cfg.sesskey
                    }, function(res) {
                        if ('error' in res) {
                            $('#cmp-' + domid + ' p').remove();
                            $('#cmp-' + domid).append("<p class='cmp-color-red'>" + res.error + "</p>");
                            startAnalysisBtn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-play-circle');
                        } else {
                            startAnalysisBtn.replaceWith(res.documentFrame);
                        }
                    });
                });
            }, 500);
        });
    }

    /**
     * Display document frame
     * @param {string} basepath
     * @param {boolean} cantriggeranalysis
     * @param {number} cmpfileid
     * @param {boolean} canviewreport
     * @param {boolean} isteacher
     * @param {string} url
     * @param {string} domid
     */
    exports.displayDocumentFrame = function(basepath,
        cantriggeranalysis,
        cmpfileid,
        canviewreport,
        isteacher,
        url,
        domid
    ) {

        $(document).ready(function () {
            displayDocumentFrame(basepath,
                cantriggeranalysis,
                cmpfileid,
                canviewreport,
                isteacher,
                url,
                domid
            );

            if (displayIntervals[domid]) {
                clearInterval(displayIntervals[domid]);
            }

            displayIntervals[domid] = setInterval(function () {
                displayDocumentFrame(basepath,
                    cantriggeranalysis,
                    cmpfileid,
                    canviewreport,
                    isteacher,
                    url,
                    domid
                );
            }, 3 * 60000);
        });
    };

    /**
     * Get notifications
     * @param {string} basepath
     * @param {number} userid
     */
    exports.getNotifications = function(basepath, userid) {
        $(document).ready(function() {
            let notificationsRead = localStorage.getItem("notifications-read");
            let notificationsIgnored = localStorage.getItem("notifications-ignored");

            $.post(basepath + '/plagiarism/compilatio/ajax/get_notifications.php', {
                'userid': userid,
                'read': notificationsRead ? JSON.parse(notificationsRead) : [],
                'ignored': notificationsIgnored ? JSON.parse(notificationsIgnored) : [],
            }, function(notifications) {

                $('#cmp-count-notifications').html(notifications.count === 0 ? '' : notifications.count);
                $('#cmp-notifications').html(notifications.content);

                $('#cmp-alerts').append(notifications.floating);

                $('.cmp-notifications-title').on('click', function() {
                    let count = $('#cmp-count-notifications').html();
                    count--;
                    $('#cmp-count-notifications').html(count <= 0 ? '' : count);

                    ignoreNotifications();

                    $('#cmp-show-notifications').toggleClass('active');

                    $('#cmp-notifications').show();
                    $('#cmp-notifications-titles').hide();

                    let notifId = $(this).attr('id').split("-").pop();
                    $('#cmp-notifications-content-' + notifId).show();

                    $('#cmp-notifications-' + notifId).children().first().removeClass('text-primary');

                    let notificationsRead = localStorage.getItem("notifications-read");
                    notificationsRead = notificationsRead ? JSON.parse(notificationsRead) : [];
                    if (!notificationsRead.includes(notifId)) {
                        notificationsRead.push(notifId);
                        localStorage.setItem("notifications-read", JSON.stringify(notificationsRead));
                    }
                });

                $('.cmp-show-notifications').on('click', function() {
                    $('#cmp-notifications-titles').show();
                    $('.cmp-notifications-content').hide();
                });

                $('#cmp-ignore-notifications').on('click', function() {
                    ignoreNotifications();
                });

                $('#cmp-show-notifications').on('click', function() {
                    ignoreNotifications();
                });


                /**
                 * Ignore Compilatio notifications
                 */
                function ignoreNotifications() {
                    $('.cmp-alert-notifications').remove();
                    localStorage.setItem("notifications-ignored", JSON.stringify(notifications.ids));
                }
            });
        });
    };

    exports.getAlerts = function(basepath, userid, module, cmid) {
        $(document).ready(function() {
            $.post(
                basepath + '/plagiarism/compilatio/ajax/get_alerts.php',
                {'userid': userid, 'module': module, 'cmid': cmid},
                function(compilatioAlerts) {
                    compilatioAlerts.forEach(alerts => {
                        $('#cmp-alerts').append(alerts);
                        $('.cmp-close').on('click', function() {
                            $(this).parent().remove();
                        });
                    });
                }
            );
        });
    };


    /**
     * Compilatio tabs
     * @param {number} docid
     */
    exports.compilatioTabs = function(docid) {
        $(document).ready(function() {
            if ($('.moove.secondary-navigation')[0]) {
                $('#cmp-container').css('margin-top', '140px');
            }

            // Convert markdown to HTML.
            $('.cmp-md').each(function() {
                var text = $.trim($(this).text());
                // Simple markdown-like converter
                var html = text
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
                    .replace(/\n/g, '<br>');
                $(this).html(html);
            });

            $('#cmp-tabs').show();

            $('#cmp-alerts > .cmp-alert > .fa-times').on('click', function() {
                $('#cmp-alert-' + $(this).attr('id').split("-").pop()).parent().remove();
            });

            if (docid) {
                $('#cmp-search').show();
            }

            $('#cmp-show-notifications').on('click', function() {
                tabClick($(this), $('#cmp-notifications'));
            });
            $('#show-stats').on('click', function() {
                tabClick($(this), $('#cmp-stats'));
            });
            $('#show-stats-per-student').on('click', function() {
                tabClick($(this), $('#cmp-stats-per-student'));
            });
            $('#show-help').on('click', function() {
                tabClick($(this), $('#cmp-help'));
            });
            $('#show-search').on('click', function() {
                tabClick($(this), $('#cmp-search'));
            });
            $('#cmp-show-settings').on('click', function() {
                tabClick($(this), $('#cmp-settings'));
            });

            var tabs = $(`#cmp-show-notifications,
                #show-stats,
                #show-stats-per-student,
                #show-help,
                #show-search,
                #cmp-show-settings`);

            var elements = $(`#cmp-notifications,
                #cmp-stats,
                #cmp-stats-per-student,
                #cmp-help,
                #cmp-search,
                #cmp-settings`);

            /**
             * TabClick
             * Show clicked tab.
             *
             * @param {object} tabClicked
             * @param {object} contentToShow
             */
            function tabClick(tabClicked, contentToShow) {
                if (!contentToShow.is(':visible')) {
                    contentToShow.show();
                    elements.not(contentToShow).hide();

                    tabs.not(tabClicked).removeClass('active');

                    tabClicked.toggleClass('active');
                } else {
                    elements.hide();
                    tabs.removeClass('active');
                }

                if ($('#show-stats').hasClass('active')) {
                    $('#cmp-container').css('max-width', 'none');
                }
            }

            $('#cmp-logo').on('click', function() {
                elements.hide();
                tabs.removeClass('active');
            });
        });
    };

    return exports;
});

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
     */
    function startAnalyses(message, basepath, cmid, selectedstudents = null, selectedquestions = [], quizid = null) {
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
            'quizid': quizid
        };

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
                    error: function(error) {
                        console.error('Error:', error);
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
     */
    exports.startAllAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            $('#cmp-dropdown-menu').on('click', function(event) {
                event.stopPropagation();
            });

            var startAllAnalysis = $('.cmp-start-btn');
            startAllAnalysis.click(function() {
                startAnalyses(message, basepath, cmid);
            });
        });
    };

    /**
     * Start analyses on selected students
     * @param {string} basepath
     * @param {number} cmid
     * @param {string} message
     */
    exports.startAnalysesOnSelectedStudents = function(basepath, cmid, message) {
        $(document).ready(function() {
            const startSelectedStudentsBtn = $('#start-selected-students-btn').hide();
            const checkboxes = $('td.c0 input, #selectall');

            /**
             * Get selected lines.
             */
            function getSelectedLines() {
                return checkboxes.filter(':checked').map(function() {
                    return $(this).val() !== 'on' ? $(this).val() : null;
                }).get();
            }

            /**
             * Update button visibility.
             */
            function updateButtonVisibility() {
                const selectedUsers = getSelectedLines();
                selectedUsers.length > 0 ? startSelectedStudentsBtn.show() : startSelectedStudentsBtn.hide();
            }

            checkboxes.on('change', updateButtonVisibility);
            startSelectedStudentsBtn.click(function() {
                startAnalyses(message, basepath, cmid, getSelectedLines());
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
                    startAnalyses(message, basepath, cmid, null, selectedquestions, quizid);
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

                $.post(basepath + '/plagiarism/compilatio/ajax/send_unsent_docs.php', {'cmid': cmid}, function() {
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

                $.post(basepath + '/plagiarism/compilatio/ajax/reset_docs_in_error.php', {'cmid': cmid}, function() {
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
                    data: {cmid: cmid, checkedvalues: checkedvalues, scores: scores},
                    success: function() {
                        let url = new URL(window.location.href);

                        if (!url.searchParams.get('refreshAllDocs')) {
                            url.searchParams.append('refreshAllDocs', 'true');
                            window.location.href = url.href;
                        } else {
                            window.location.reload();
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
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
            $.post(basepath + '/plagiarism/compilatio/ajax/check_user_info.php', {'cmid': cmid});
        });
    };

    /**
     * Display document frame
     * @param {string} basepath
     * @param {boolean} cantriggeranalysis
     * @param {boolean} isstudentanalyse
     * @param {number} cmpfileid
     * @param {boolean} canviewreport
     * @param {boolean} isteacher
     * @param {string} url
     * @param {string} domid
     */
    function displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, domid) {
        $.post(basepath + '/plagiarism/compilatio/ajax/display_document_frame.php',
            {cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url},
        function(button) {
            let el = $('#cmp-' + domid);
            el.empty().append(button);

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
                        $.post(basepath + '/plagiarism/compilatio/ajax/update_score.php', {'docId': cmpfileid}, function(res) {
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
                        {'docId': cmpfileid, 'indexingState': indexingState},
                    function(res) {
                        let response = JSON.parse(res);
                        if (response.status === 'ok') {
                            if (indexingState === 0) {
                                i.addClass('cmp-library-out fa-times-circle fa');
                            } else {
                                i.addClass('cmp-library-in fa-check-circle fa');
                            }
                            i.parent().attr('title', response.text);
                        }
                    });
                });

                var startAnalysisBtn = $('#cmp-' + domid + ' .cmp-start-btn');
                startAnalysisBtn.click(function() {
                    if (isInMaintenance) {
                        return;
                    }
                    startAnalysisBtn.find('i').removeClass('fa-play-circle').addClass('fa-spinner fa-spin');

                    $.post(basepath + '/plagiarism/compilatio/ajax/start_analysis.php', {'docId': cmpfileid}, function(res) {
                        res = JSON.parse(res);

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
     * @param {boolean} isstudentanalyse
     * @param {number} cmpfileid
     * @param {boolean} canviewreport
     * @param {boolean} isteacher
     * @param {string} url
     * @param {string} domid
     */
    exports.displayDocumentFrame = function(basepath,
        cantriggeranalysis,
        isstudentanalyse,
        cmpfileid,
        canviewreport,
        isteacher,
        url,
        domid
    ) {

        $(document).ready(function() {
            displayDocumentFrame(basepath,
                cantriggeranalysis,
                isstudentanalyse,
                cmpfileid,
                canviewreport,
                isteacher,
                url,
                domid
            );

            setInterval(function() {
                displayDocumentFrame(basepath,
                    cantriggeranalysis,
                    isstudentanalyse,
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
                notifications = JSON.parse(notifications);

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
                $(this).html(markdown($.trim($(this).text())));
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

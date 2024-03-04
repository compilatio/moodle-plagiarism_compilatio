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

    function startAnalysis(message, basepath, cmid, selectedusers) {
        disableCompilatioButtons();
        $("#cmp-alerts").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");        
        
        $.post(basepath + '/plagiarism/compilatio/ajax/start_all_analysis.php',
            {'cmid': cmid, 'selectedUsers': selectedusers != null ? selectedusers.toString() : ''}, function() {
                window.location.reload();
            });
    }

    exports.getSelectedStudent = function(basepath, cmid) {
        $(document).ready(function(){
            const dropdown = $('#student-select');
            const statisticsContainer = $('#statistics-container');
            
            dropdown.on('change', function() {
                const selectedstudent = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: basepath + '/plagiarism/compilatio/ajax/stats_per_student.php',  
                    data: { selectedstudent: selectedstudent, cmid: cmid },
                    success: function(response) {
                        statisticsContainer.html(response);
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#previous-student').on('click', function() {
                changeSelectedTruc(dropdown.prop('selectedIndex'), -1)
            });

            $('#next-student').on('click', function() {
                changeSelectedTruc(dropdown.prop('selectedIndex'), 1)
            });

            function changeSelectedTruc (selectedIndex, direction) {
                var newIndex = selectedIndex + direction;
                const maxIndex = dropdown.find('option').length - 1;

                if (newIndex == -1) {
                    newIndex = maxIndex;
                } else if (newIndex > maxIndex) {
                    newIndex = 0;
                }

                dropdown.prop('selectedIndex', newIndex).change();
            }
        });
    }

    exports.startAllAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            var startAllAnalysis = $('.cmp-start-btn');
            startAllAnalysis.click(function() {
                startAnalysis(message, basepath, cmid, null);
            });
        });
    };

    exports.startAnalysesOnSelectedFiles = function(basepath, cmid, message) {
        $(document).ready(function() {
            const startSelectedAnalysesBtn = $('#start-selected-analyses-btn').hide();
            const checkboxes = $('td.c0 input, #selectall');
            function getSelectedLines() {
                return checkboxes.filter(':checked').map(function() {
                    return $(this).val() != 'on' ? $(this).val() : null;
                }).get();
            }
            function updateButtonVisibility() {
                const selectedUsers = getSelectedLines();
                selectedUsers.length > 0 ? startSelectedAnalysesBtn.show() : startSelectedAnalysesBtn.hide();
            }
            checkboxes.on('change', updateButtonVisibility);
            startSelectedAnalysesBtn.click(function() {
                startAnalysis(message, basepath, cmid, getSelectedLines());
            });
        });
    };
    
    exports.sendUnsentDocs = function(basepath, cmid, message) {
        $(document).ready(function() {
            var sendUnsentDocs = $('#cmp-send-btn');
            sendUnsentDocs.click(function() {
                disableCompilatioButtons();
                $("#cmp-alerts").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/send_unsent_docs.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.resetDocsInError = function(basepath, cmid, message) {
        $(document).ready(function() {
            var resetDocsInError = $('#cmp-reset-btn');
            resetDocsInError.click(function() {
                disableCompilatioButtons();
                $("#cmp-alerts").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/reset_docs_in_error.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.optionsanalysescores = function(basepath, cmid, scores) {
        $(document).ready(function() {
            var optionsscores = $('#option-score-ignored');
            optionsscores.click(function() {
                optionsscores.css('background-color', 'grey');
                optionsscores.html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
                $('#loading-blur').css('filter', 'blur(2px)');
                var checkedcheckboxes = $('.checkbox-score-options:checked');
                var checkedvalues = [];
                checkedcheckboxes.each(function() {
                    checkedvalues.push($(this).val());
                });
                $.ajax({
                    type: 'POST',
                    url: basepath + '/plagiarism/compilatio/ajax/update_score_options.php',  
                    data: {cmid: cmid, checkedvalues: checkedvalues, scores: scores},
                    success: function() {
                        window.location.reload();
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    };
    

    exports.validateTermsOfService = function(basepath, userid) {
        $(document).ready(function() {
            $('#tos-btn').click(function() {
                $.post(basepath + '/plagiarism/compilatio/ajax/validate_terms_of_service.php',
                {'userid': userid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.checkUserInfo = function(basepath, userid) {
        $(document).ready(function() {
            $.post(basepath + '/plagiarism/compilatio/ajax/check_user_info.php', {'userid': userid});
        });
    };

    function displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, domid) {
        $.post(basepath + '/plagiarism/compilatio/ajax/display_document_frame.php', {cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url}, function(button) {
            let el = $('#cmp-' + domid);
            el.empty().append(button);

            setTimeout(e => {
                if (isteacher) {
                    var refreshScoreBtn = $('#cmp-' + domid + ' .cmp-similarity');
                    refreshScoreBtn.on("mouseover", (e) => {
                        refreshScoreBtn.find('i').show();
                        refreshScoreBtn.find('span').hide();
                    });
                    refreshScoreBtn.on("mouseout", (e) => {
                        refreshScoreBtn.find('i').hide();
                        refreshScoreBtn.find('span').show();
                    });
                    refreshScoreBtn.click(function() {
                        $('#cmp-' + domid + ' #cmp-score-icons').remove();
                        refreshScoreBtn.empty();
                        $.post(basepath + '/plagiarism/compilatio/ajax/update_score.php', {'docId': cmpfileid}, function(res) {
                            refreshScoreBtn.replaceWith(res);
                        });
                    });
                }

                var toogleIndexingStateBtn = $('#cmp-' + domid + ' .cmp-library');
                toogleIndexingStateBtn.click(function() {
                    var i = $(this).find('i');
                    if (i.is('.cmp-library-in')) {
                        var indexingState = 0;
                    }
                    if (i.is('.cmp-library-out')) {
                        var indexingState = 1;
                    }
                    i.removeClass();
                    $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php', {'docId': cmpfileid, 'indexingState': indexingState}, function(res) {
                        if (res == 'true') {
                            if (indexingState == 0) {
                                i.addClass('cmp-library-out fa-times-circle fa');
                            } else {
                                i.addClass('cmp-library-in fa-check-circle fa');
                            }
                        }
                    });
                });

                var startAnalysisBtn = $('#cmp-' + domid + ' .cmp-start-btn');
                startAnalysisBtn.click(function() {
                    startAnalysisBtn.find('i').removeClass('fa-play-circle').addClass('fa-spinner fa-spin');

                    $.post(basepath + '/plagiarism/compilatio/ajax/start_analysis.php',
                    {'docId': cmpfileid}, function(res) {
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

    exports.displayDocumentFrame = function(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, domid) {
        $(document).ready(function() {
            displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, domid);

            setInterval(function() {
                displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, domid);
            }, 3 * 60000);
        });
    };

    exports.getNotifications = function(basepath, userid) {
        $(document).ready(function() {
            $.post(basepath + '/plagiarism/compilatio/ajax/get_notifications.php', {
                'userid': userid,
                'read': JSON.parse(localStorage.getItem("notifications-read")) ?? [],
                'ignored': JSON.parse(localStorage.getItem("notifications-ignored")) ?? [],
            }, function(notifications) {
                notifications = JSON.parse(notifications);

                $('#cmp-count-notifications').html(notifications.count == 0 ? '' : notifications.count);
                $('#cmp-notifications').html(notifications.content);

                $('#cmp-alerts').append(notifications.floating);

                $('.cmp-notifications-title').on('click', function() {
                    let count = $('#cmp-count-notifications').html();
                    count--;
                    $('#cmp-count-notifications').html(count <= 0 ? '' : count);

                    ignoreNotifications()

                    $('#cmp-show-notifications').toggleClass('active');

                    $('#cmp-notifications').show();
                    $('#cmp-notifications-titles').hide();

                    let notifId = $(this).attr('id').split("-").pop();
                    $('#cmp-notifications-content-' + notifId).show();

                    $('#cmp-notifications-' + notifId).children().first().removeClass('text-primary')

                    let notificationsRead = JSON.parse(localStorage.getItem("notifications-read")) ?? []
                    if (!notificationsRead.includes(notifId)) {
                        notificationsRead.push(notifId)
                        localStorage.setItem("notifications-read", JSON.stringify(notificationsRead))
                    }
                });
    
                $('.cmp-show-notifications').on('click', function() {
                    $('#cmp-notifications-titles').show();
                    $('.cmp-notifications-content').hide();
                });

                $('#cmp-ignore-notifications').on('click', function() {
                    ignoreNotifications()
                });

                $('#cmp-show-notifications').on('click', function() {
                    ignoreNotifications()
                });

                function ignoreNotifications() {
                    $('.cmp-alert-notifications').remove();
                    localStorage.setItem("notifications-ignored", JSON.stringify(notifications.ids))
                }
            });
        });
    };

    exports.compilatioTabs = function(docid) {
        $(document).ready(function() {
            if ($('.moove.secondary-navigation')[0]) {
                $('#cmp-container').css('margin-top', '140px');
            }

            // Convert markdown to HTML.
            $('.cmp-md').each(function() {
                $(this).html(markdown($.trim($(this).text())))
            });

            $('#cmp-tabs').show();

            $('#cmp-alerts > .cmp-alert > .fa-times').on('click', function() {
                $('#cmp-alert-' + $(this).attr('id').split("-").pop()).parent().remove()
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
            $('#cmp-show-options').on('click', function() {
                tabClick($(this), $('#cmp-options'));
            });

            var tabs = $('#cmp-show-notifications, #show-stats, #show-stats-per-student, #show-help, #show-search, #cmp-show-options');
            var elements = $('#cmp-notifications, #cmp-stats, #cmp-stats-per-student, #cmp-help, #cmp-search, #cmp-options');

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

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
        $("#cmp-notices").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");        
        
        $.post(basepath + '/plagiarism/compilatio/ajax/start_all_analysis.php',
            {'cmid': cmid, 'selectedUsers': selectedusers.toString()}, function() {
                window.location.reload();
            });
    }

    exports.startAllAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            var startAllAnalysis = $('#cmp-start-btn');
            startAllAnalysis.click(function() {
                startAnalysis(message, basepath, cmid, null)
            });
        });
    };

    exports.startSelectedFilesAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            const startSelectedFilesAnalysis = $('#cmp-start-selected-btn').hide();
            const checkboxes = $('td.c0 input, #selectall');
            function getSelectedUsers() {
                return checkboxes.filter(':checked').map(function() {
                    return $(this).val() != 'on' ? $(this).val() : null;
                }).get();
            }
            function updateButtonVisibility() {
                const selectedUsers = getSelectedUsers();
                selectedUsers.length > 0 ? startSelectedFilesAnalysis.show() : startSelectedFilesAnalysis.hide();
            }
            checkboxes.on('change', updateButtonVisibility);
            startSelectedFilesAnalysis.click(function() {
                console.log(getSelectedUsers());
                startAnalysis(message, basepath, cmid, getSelectedUsers());
            });
        });
    };
    

    
    

    exports.sendUnsentDocs = function(basepath, cmid, message) {
        $(document).ready(function() {
            var sendUnsentDocs = $('#cmp-send-btn');
            sendUnsentDocs.click(function() {
                disableCompilatioButtons();
                $("#cmp-notices").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");
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
                $("#cmp-notices").append("<div class='cmp-alert cmp-alert-info'>" + message + "<i class='ml-3 fa fa-lg fa-spinner fa-spin'></i></div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/reset_docs_in_error.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
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
                        refreshScoreBtn.find('i').removeClass('fa-circle').addClass('fa-refresh');
                    });
                    refreshScoreBtn.on("mouseout", (e) => {
                        refreshScoreBtn.find('i').removeClass('fa-refresh').addClass('fa-circle');
                    });
                    refreshScoreBtn.click(function() {
                        refreshScoreBtn.empty();
                        $('#cmp-score-icons').remove();
                        $.post(basepath + '/plagiarism/compilatio/ajax/update_score.php', {'docId': cmpfileid}, function(res) {
                            refreshScoreBtn.replaceWith(res);
                        });
                    });
                }

                var toogleIndexingStateBtn = $('#cmp-' + domid + ' .cmp-library');
                toogleIndexingStateBtn.click(function() {
                    i = toogleIndexingStateBtn.find('i');
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

    exports.compilatioTabs = function(alertsCount, docid) {
        $(document).ready(function() {

            if ($('.moove.secondary-navigation')[0]) {
                $('#cmp-container').css('margin-top', '140px');
                $('#cmp-display-frame').css('margin-top', '140px');
            }

            // Convert markdown to HTML.
            $('.cmp-md').each(function() {
                $(this).html(markdown($.trim($(this).text())))
            });

            // Display or hide Compilatio container
            if (localStorage.getItem("cmp-container-displayed") == 0) {
                $('#cmp-container').hide();
                $('#cmp-display-frame').show();
            }
            if (alertsCount > 0) {
                $('#cmp-bell').show();
            }
            $('#cmp-display-frame').on('click', function() {
                localStorage.setItem("cmp-container-displayed", 1);
                $(this).hide();
                $('#cmp-container').show();
            });
            $('#cmp-hide-frame').on('click', function() {
                localStorage.setItem("cmp-container-displayed", 0);
                $('#cmp-container').hide();
                $('#cmp-display-frame').show();
            });

            $('#cmp-tabs').show();

            $('#cmp-notices > .cmp-alert > .fa-times').on('click', function() {
                $('#cmp-notices').empty()
            });

            var selectedElement = '';
            if (docid) {
                selectedElement = '#cmp-search';
            } else {
                selectedElement = '#cmp-home';
            }

            $(selectedElement).show();

            $('#cmp-show-notifications').on('click', function() {
                tabClick($(this), $('#cmp-notifications'));
            });
            $('#show-stats').on('click', function() {
                tabClick($(this), $('#cmp-stats'));
            });
            $('#show-help').on('click', function() {
                tabClick($(this), $('#cmp-help'));
            });
            $('#show-search').on('click', function() {
                tabClick($(this), $('#cmp-search'));
            });

            var tabs = $('#cmp-show-notifications, #show-stats, #show-help, #show-search');
            var elements = $('#cmp-notifications, #cmp-stats, #cmp-help, #cmp-home, #cmp-search');

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
                }

                if ($('#cmp-stats').hasClass('active')) {
                    $('#cmp-container').css('max-width', 'none');
                }
            }

            $('#cmp-logo').on('click', function() {
                var elementClicked = $('#cmp-home');
                elementClicked.show();
                elements.not(elementClicked).hide();
                tabs.removeClass('active');
            });
        });
    };

    return exports;
});

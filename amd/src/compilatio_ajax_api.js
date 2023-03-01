define(['jquery'], function($) {
    /**
     * A Module that handles Compilatio ajax/API calls
     */

    /**
     * disableCompilatioButtons
     * Disable Compilatio buttons (during multiple ajax/API calls)
     */
    function disableCompilatioButtons() {
        $('.cmp-btn-lg').each(function() {
            $(this).attr('disabled', 'disabled');
            $(this).addClass('disabled');
            $(this).attr('href', '#');
        });
    }

    var exports = {};

    // TODO Factoriser.
    exports.startAllAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            var startAllAnalysis = $('button.cmp-start-btn');
            startAllAnalysis.click(function() {
                disableCompilatioButtons();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search, #cmp-notifications').hide();
                $('#cmp-tabs-separator').after("<div class='cmp-alert cmp-alert-info'>" + message + "</div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/start_all_analysis.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.sendUnsentDocs = function(basepath, cmid, message) {
        $(document).ready(function() {
            var sendUnsentDocs = $('button.cmp-send-btn');
            sendUnsentDocs.click(function() {
                disableCompilatioButtons();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search, #cmp-notifications').hide();
                $('#cmp-tabs-separator').after("<div class='cmp-alert cmp-alert-info'>" + message + "</div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/send_unsent_docs.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.resetDocsInError = function(basepath, cmid, message) {
        $(document).ready(function() {
            var resetDocsInError = $('button.cmp-reset-btn');
            resetDocsInError.click(function() {
                disableCompilatioButtons();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search, #cmp-notifications').hide();
                $('#cmp-tabs-separator').after("<div class='cmp-alert cmp-alert-info'>" + message + "</div>");
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

    function displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid) {
        $.post(basepath + '/plagiarism/compilatio/ajax/display_document_frame.php', {cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid}, function(button) {
            let el = $('#cmp-' + domid);
            el.empty().append(button);

            //setTimeout(e => { // TODO
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
            //}, 1000);

            var startAnalysisBtn = $('#cmp-' + domid + ' .cmp-btn');
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
                        $('#cmp-' + domid + ' .cmp-area').removeClass('cmp-bg-primary').addClass('cmp-bg-' + res.bgcolor);
                        startAnalysisBtn.replaceWith(res.documentFrame);
                    }
                });
            });
        });
    }

    exports.displayDocumentFrame = function(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid) {
        $(document).ready(function() {
            displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid);

            setInterval(function() {
                displayDocumentFrame(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid);
            }, 1 * 60000);
        });
    };

    exports.compilatioTabs = function(alertsCount, docid) {
        $(document).ready(function() {
            $('#cmp-tabs').show();

            var selectedElement = '';
            if (docid) {
                selectedElement = '#cmp-search';
            } else if (alertsCount > 0) {
                selectedElement = '#cmp-notifications';
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
                    $('#cmp-hide-area').fadeIn();
                    $('#cmp-tabs-separator').show();
                }
            }

            $('#cmp-logo').on('click', function() {
                var elementClicked = $('#cmp-home');
                elementClicked.show();
                elements.not(elementClicked).hide();
                tabs.removeClass('active');
                $('#cmp-hide-area').fadeIn();
                $('#cmp-tabs-separator').show();
            });
            $('#cmp-hide-area').on('click', function() {
                elements.hide();
                $(this).fadeOut();
                tabs.removeClass('active');
                $('#cmp-tabs-separator').hide();
            });
        });
    };

    return exports;
});

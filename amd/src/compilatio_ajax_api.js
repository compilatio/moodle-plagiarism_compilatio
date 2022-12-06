define(['jquery'], function($) {
    /**
     * A Module that handles Compilatio ajax/API calls
     */

    /**
     * disableCompilatioButtons
     * Disable Compilatio buttons (during multiple ajax/API calls)
     */
    function disableCompilatioButtons() {
        $(".cmp-btn-lg").each(function() {
            $(this).attr("disabled", "disabled");
            $(this).addClass("disabled");
            $(this).attr("href", "#");
        });
    }

    var exports = {};

    exports.toggleIndexingState = function(basepath, eltId, docId) {
        $(document).ready(function() {
            setTimeout(function() {
                var btn = $(".cmp-" + eltId + " .cmp-library");
                btn.click(function() {
                    el = btn.find("i");
                    if (el.is('.cmp-library-in')) {
                        var indexingState = 0;
                    }
                    if (el.is('.cmp-library-out')) {
                        var indexingState = 1;
                    }
                    $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php', {'idDoc': docId, 'indexingState': indexingState}, function(res) {
                        if (res == 'true') {
                            if (indexingState == 0) {
                                el.removeClass('cmp-library-in');
                                el.addClass('cmp-library-out');
                                el.removeClass('fa-check-circle');
                                el.addClass('fa-times-circle');
                            } else {
                                el.removeClass('cmp-library-out');
                                el.addClass('cmp-library-in');
                                el.removeClass('fa-times-circle');
                                el.addClass('fa-check-circle');
                            }
                        }
                    });
                });
            }, 3000);
        });
    };

    exports.startAllAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            var startAllAnalysis = $("button.cmp-start-btn");
            startAllAnalysis.click(function() {
                disableCompilatioButtons();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search, #cmp-notifications').hide();
                $("#cmp-tabs-separator").after("<div class='cmp-alert cmp-alert-info'>" + message + "</div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/start_all_analysis.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.startAnalysis = function(basepath, eltId, docId) {
        $(document).ready(function() {
            setTimeout(function() {
                $(".cmp-" + eltId + " .cmp-btn").click(function() {
                    $.post(basepath + '/plagiarism/compilatio/ajax/start_analysis.php',
                    {'docId': docId}, function() {
                        window.location.reload();
                    });
                });
            }, 300);
        });
    };

    exports.restartFailedAnalysis = function(basepath, cmid, message) {
        $(document).ready(function() {
            var restartFailedAnalysis = $("button.cmp-restart-btn");
            restartFailedAnalysis.click(function() {
                disableCompilatioButtons();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search, #cmp-notifications').hide();
                $("#cmp-tabs-separator").after("<div class='cmp-alert cmp-alert-info'>" + message + "</div>");
                $.post(basepath + '/plagiarism/compilatio/ajax/restart_failed_analysis.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.validateTermsOfService = function(basepath, userid) {
        $(document).ready(function() {
            $("#tos-btn").click(function() {
                $.post(basepath + '/plagiarism/compilatio/ajax/validate_terms_of_service.php',
                {'userid': userid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.displayButton = function(basepath, cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid) {
        $(document).ready(function() {
            $.post(basepath + '/plagiarism/compilatio/ajax/display_button.php', {cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid}, function(button) {
                let el = $('#cmp-' + domid);
                el.append(button);
            });
            setInterval(function() {
                $.post(basepath + '/plagiarism/compilatio/ajax/display_button.php', {cantriggeranalysis, isstudentanalyse, cmpfileid, canviewreport, isteacher, url, filename, domid}, function(button) {
                    let el = $('#cmp-' + domid);
                    el.empty();
                    el.append(button);
                });
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

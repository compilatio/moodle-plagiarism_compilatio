define(['jquery'], function($) {
    /**
     * A Module that handles Compilatio ajax/API calls
     */

    /**
     * disableCompilatioButtons
     * Disable Compilatio buttons (during multiple ajax/API calls)
     */
    function disableCompilatioButtons() {
        $(".compilatio-button").each(function() {
            $(this).attr("disabled", "disabled");
            $(this).addClass("disabled");
            $(this).attr("href", "#");
        });
    }

    var exports = {};

    var getIndexingState = exports.getIndexingState = function(basepath, eltId, docId, apiconfigid) {
        $(document).ready(function() {
            $.post(basepath + '/plagiarism/compilatio/ajax/get_indexing_state.php',
            {'idDoc': docId, 'apiconfigid': apiconfigid}, function(data) {
                $(".compi-" + eltId + " .compilatio-library").detach();
                $(".compi-" + eltId).prepend(data);

                setTimeout(function() {
                    $(".compi-" + eltId + " > div:first-child").click(function() {
                        toggleIndexingState(basepath, eltId, docId, apiconfigid);
                    });
                }, 250); // Wait for all DOM updates be finished before binding events handlers.
            });
        });
    };

    var toggleIndexingState = exports.toggleIndexingState = function(basepath, eltId, docId, apiconfigid) {
        var indexingState;
        if ($(".compi-" + eltId + " > div:first-child").is('.compilatio-library-in')) {
            $(".compi-" + eltId + " > div:first-child").removeClass('compilatio-library-in');
            indexingState = 0;
        }
        if ($(".compi-" + eltId + " > div:first-child").is('.compilatio-library-out')) {
            $(".compi-" + eltId + " > div:first-child").removeClass('compilatio-library-out');
            indexingState = 1;
        }
        $(".compi-" + eltId + " > div:first-child").addClass('compilatio-library');
        $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php',
        {'idDoc': docId, 'indexingState': indexingState, 'apiconfigid': apiconfigid}, function(data) {
            if (data == 'true') {
                getIndexingState(basepath, eltId, docId, apiconfigid);
            }
        });
    };

    exports.refreshButton = function(basepath, fileIds, infoStr) {
        $(document).ready(function() {
            var n = fileIds.length;
            var i = 0;
            var refreshButton = $("i.fa-refresh").parent("button");
            if (n == 0) {
                disableCompilatioButtons();
            } else {
                refreshButton.click(function() {
                    disableCompilatioButtons();
                    // Display progress bar.
                    $("#compilatio-home").html("<p>" + infoStr + "<progress id='compi-update-progress' value='"
                        + i + "' max='" + n + "'></progress></p>");
                    $("#compilatio-logo").click();
                    // Launch ajax requests.
                    fileIds.forEach(function(id) {
                        $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_check_analysis.php',
                        {'id': id}, function() {
                            i++;
                            $("#compi-update-progress").val(i);
                            if (i == n) {
                                window.location.reload();
                            }
                        });
                    });
                });
            }
        });
    };

    exports.startAllAnalysis = function(basepath, cmid) {
        $(document).ready(function() {
            var startAllAnalysis = $("button.comp-start-btn");
            startAllAnalysis.click(function() {
                disableCompilatioButtons();
                $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_start_all_analysis.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.startAnalysis = function(basepath, eltId, docId, cmid = false) {
        $(document).ready(function() {
            setTimeout(function() {
                $(".compi-" + eltId + " > div:last-child").click(function() {
                    $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_start_analysis.php',
                    {'docId': docId, 'cmid': cmid}, function() {
                        window.location.reload();
                    });
                });
            }, 250);
        });
    };

    exports.restartFailedAnalysis = function(basepath, cmid) {
        $(document).ready(function() {
            var restartFailedAnalysis = $("button.comp-restart-btn");
            restartFailedAnalysis.click(function() {
                disableCompilatioButtons();
                $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_restart_failed_analysis.php',
                {'cmid': cmid}, function() {
                    window.location.reload();
                });
            });
        });
    };

    exports.compilatioTabs = function(alerts, idcourt) {
        $(document).ready(function() {
            var selectedElement = '';
            if (idcourt) {
                selectedElement = '#compilatio-search';
            } else if (alerts.length > 0) {
                selectedElement = '#compilatio-notifications';
            } else {
                selectedElement = '#compilatio-home';
            }

            $('#compilatio-container').css('height', 'auto');
            $('#compilatio-tabs').show();

            var tabs = $('#compilatio-show-notifications, #show-stats, #show-help, #show-search');
            var elements = $('#compilatio-notifications, #compilatio-stats, #compilatio-help, #compilatio-home, #compilatio-search');

            elements.not($(selectedElement)).hide();

            $('#compilatio-show-notifications').on('click', function() {
                    tabClick($(this), $('#compilatio-notifications'));
            });
            $('#show-stats').on('click', function() {
                    tabClick($(this), $('#compilatio-stats'));
            });
            $('#show-help').on('click', function() {
                    tabClick($(this), $('#compilatio-help'));
            });
            $('#show-search').on('click', function() {
                    tabClick($(this), $('#compilatio-search'));
            });

            /**
             * TabClick
             * Show clicked tab.
             *
             * @param tabClicked
             * @param contentToShow
             */
            function tabClick(tabClicked, contentToShow) {
                if (!contentToShow.is(':visible')) {
                    contentToShow.show();

                    elements.not(contentToShow).hide();

                    tabs.not(tabClicked).removeClass('active');

                    tabClicked.toggleClass('active');
                    $('#compilatio-hide-area').fadeIn();
                }
            }

            $('#compilatio-logo').on('click', function() {
                var elementClicked = $('#compilatio-home');
                elementClicked.show();
                elements.not(elementClicked).hide();
                tabs.removeClass('active');
                $('#compilatio-hide-area').fadeIn();
            });
            $('#compilatio-hide-area').on('click', function() {
                elements.hide();
                $(this).fadeOut();
                tabs.removeClass('active');
            });
        });
    };

    return exports;
});
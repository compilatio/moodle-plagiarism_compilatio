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

    exports.toggleIndexingState = function(basepath, eltId, docId) {
        $(document).ready(function() {
            setTimeout(function() {
                var btn = $(".compi-" + eltId + " > div:first-child");
                btn.click(function() {
                    if (btn.is('.compilatio-library-in')) {
                        var indexingState = 0;
                    }
                    if (btn.is('.compilatio-library-out')) {
                        var indexingState = 1;
                    }
                    $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php', {'idDoc': docId, 'indexingState': indexingState}, function(res) {
                        if (res == 'true') {
                            if (indexingState == 0) {
                                btn.removeClass('compilatio-library-in');
                                btn.addClass('compilatio-library-out');
                            } else {
                                btn.removeClass('compilatio-library-out');
                                btn.addClass('compilatio-library-in');
                            }
                        }
                    });
                });
            }, 300);
        });
    };

    exports.refreshButton = function(basepath, fileIds, docNotUploaded, infoStr) {
        $(document).ready(function() {
            var n = fileIds.length;
            var i = 0;
            var refreshButton = $("i.fa-refresh").parent("button");
            if (n == 0) {
                disableCompilatioButtons();
                if (docNotUploaded > 0) {
                    $(".comp-start-btn").each(function() {
                        $(this).removeAttr("disabled");
                        $(this).removeClass("disabled");
                    });
                }
            } else {
                refreshButton.click(function() {
                    disableCompilatioButtons();
                    // Display progress bar.
                    $("#compi-home").html("<p>" + infoStr + "<progress id='compi-update-progress' value='"
                        + i + "' max='" + n + "'></progress></p>");
                    $("#compilatio-logo").click();
                    // Launch ajax requests.
                    fileIds.forEach(function(id) {
                        $.post(basepath + '/plagiarism/compilatio/ajax/check_analysis.php',
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

    exports.startAllAnalysis = function(basepath, cmid, title, message) {
        $(document).ready(function() {
            var startAllAnalysis = $("button.comp-start-btn");
            startAllAnalysis.click(function() {
                disableCompilatioButtons();
                $('#compi-notifications').show();
                $('#compi-stats, #compi-help, #compi-home, #compi-search').hide();
                $("#compi-notif-title").after(
                    "<div class='compilatio-alert compilatio-alert-info'><strong>" + title
                    + "</strong><br/>" + message + "</div>"
                );
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
                $(".compi-" + eltId + " > div:last-child").click(function() {
                    $.post(basepath + '/plagiarism/compilatio/ajax/start_analysis.php',
                    {'docId': docId}, function() {
                        window.location.reload();
                    });
                });
            }, 300);
        });
    };

    exports.restartFailedAnalysis = function(basepath, cmid, title, message) {
        $(document).ready(function() {
            var restartFailedAnalysis = $("button.comp-restart-btn");
            restartFailedAnalysis.click(function() {
                disableCompilatioButtons();
                $('#compi-notifications').show();
                $('#compi-stats, #compi-help, #compi-home, #compi-search').hide();
                $("#compi-notif-title").after(
                    "<div class='compilatio-alert compilatio-alert-info'><strong>" + title
                    + "</strong><br/>" + message + "</div>"
                );
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

    exports.compilatioTabs = function(basepath, alertsCount, idcourt, notifIcon, notifTitle) {
        $(document).ready(function() {
            $('#compilatio-tabs').show();

            var selectedElement = '';
            if (idcourt) {
                selectedElement = '#compi-search';
            } else if (alertsCount > 0) {
                selectedElement = '#compi-notifications';
            } else {
                selectedElement = '#compi-home';
            }

            $(selectedElement).show();

            $('#compilatio-show-notifications').on('click', function() {
                    tabClick($(this), $('#compi-notifications'));
            });
            $('#show-stats').on('click', function() {
                    tabClick($(this), $('#compi-stats'));
            });
            $('#show-help').on('click', function() {
                    tabClick($(this), $('#compi-help'));
            });
            $('#show-search').on('click', function() {
                    tabClick($(this), $('#compi-search'));
            });

            var tabs = $('#compilatio-show-notifications, #show-stats, #show-help, #show-search');
            var elements = $('#compi-notifications, #compi-stats, #compi-help, #compi-home, #compi-search');

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
                    $('#compilatio-hide-area').fadeIn();
                }
            }

            $('#compilatio-logo').on('click', function() {
                var elementClicked = $('#compi-home');
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

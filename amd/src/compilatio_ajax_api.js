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
                var btn = $(".cmp-" + eltId + " > div:first-child > i");
                btn.click(function() {
                    if (btn.is('.cmp-library-in')) {
                        var indexingState = 0;
                    }
                    if (btn.is('.cmp-library-out')) {
                        var indexingState = 1;
                    }
                    $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php', {'idDoc': docId, 'indexingState': indexingState}, function(res) {
                        if (res == 'true') {
                            if (indexingState == 0) {
                                btn.removeClass('cmp-library-in');
                                btn.addClass('cmp-library-out');
                                btn.removeClass('fa-check-circle');
                                btn.addClass('fa-times-circle');
                            } else {
                                btn.removeClass('cmp-library-out');
                                btn.addClass('cmp-library-in');
                                btn.removeClass('fa-times-circle');
                                btn.addClass('fa-check-circle');
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
                    $(".cmp-start-btn").each(function() {
                        $(this).removeAttr("disabled");
                        $(this).removeClass("disabled");
                    });
                }
            } else {
                refreshButton.click(function() {
                    disableCompilatioButtons();
                    // Display progress bar.
                    $("#cmp-home").html("<p>" + infoStr + "<progress id='cmp-update-progress' value='"
                        + i + "' max='" + n + "'></progress></p>");
                    $("#cmp-logo").click();
                    // Launch ajax requests.
                    fileIds.forEach(function(id) {
                        $.post(basepath + '/plagiarism/compilatio/ajax/check_analysis.php',
                        {'id': id}, function() {
                            i++;
                            $("#cmp-update-progress").val(i);
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
            var startAllAnalysis = $("button.cmp-start-btn");
            startAllAnalysis.click(function() {
                disableCompilatioButtons();
                $('#cmp-notifications').show();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search').hide();
                $("#cmp-notif-title").after(
                    "<div class='cmp-alert cmp-alert-info'><strong>" + title
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
                $(".cmp-" + eltId + " > div:last-child").click(function() {
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
            var restartFailedAnalysis = $("button.cmp-restart-btn");
            restartFailedAnalysis.click(function() {
                disableCompilatioButtons();
                $('#cmp-notifications').show();
                $('#cmp-stats, #cmp-help, #cmp-home, #cmp-search').hide();
                $("#cmp-notif-title").after(
                    "<div class='cmp-alert cmp-alert-info'><strong>" + title
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
            $('#cmp-tabs').show();

            var selectedElement = '';
            if (idcourt) {
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
                    $('.cmp-tabs-separator').show();
                }
            }

            $('#cmp-logo').on('click', function() {
                var elementClicked = $('#cmp-home');
                elementClicked.show();
                elements.not(elementClicked).hide();
                tabs.removeClass('active');
                $('#cmp-hide-area').fadeIn();
                $('.cmp-tabs-separator').show();
            });
            $('#cmp-hide-area').on('click', function() {
                elements.hide();
                $(this).fadeOut();
                tabs.removeClass('active');
                $('.cmp-tabs-separator').hide();
            });
        });
    };

    return exports;
});

define(['jquery'], function ($) {
    /**
     * A Module that handles Compilatio ajax/API calls
     */

    /**
     * disableCompilatioButtons
     * Disable Compilatio buttons (during multiple ajax/API calls)
     *
     * @return void
     */
    function disableCompilatioButtons() {
        $(".compilatio-button").each(function () {
            $(this).attr("disabled", "disabled");
            $(this).addClass("disabled");
            $(this).attr("href", "#");
        });
    }

    var exports = {};

    var getIndexingState = exports.getIndexingState = function (basepath, eltId, docId) {
        $(document).ready(function () {
            $.post(basepath + '/plagiarism/compilatio/ajax/get_indexing_state.php', { 'idDoc': docId }, function (data) {
                $(".compi-" + eltId + " .library").detach();
                $(".compi-" + eltId).prepend(data);

                setTimeout(function () {
                    $(".compi-" + eltId + " > div:first-child").click(function () {
                        toggleIndexingState(basepath, eltId, docId);
                    });
                }, 250); // Wait for all DOM updates be finished before binding events handlers.
            });
        });
    };

    var toggleIndexingState = exports.toggleIndexingState = function (basepath, eltId, docId) {
        var indexingState;
        if ($(".compi-" + eltId + " > div:first-child").is('.library-in')) {
            $(".compi-" + eltId + " > div:first-child").removeClass('library-in');
            indexingState = 0;
        }
        if ($(".compi-" + eltId + " > div:first-child").is('.library-out')) {
            $(".compi-" + eltId + " > div:first-child").removeClass('library-out');
            indexingState = 1;
        }
        $(".compi-" + eltId + " > div:first-child").addClass('library');
        $.post(basepath + '/plagiarism/compilatio/ajax/set_indexing_state.php', { 'idDoc': docId, 'indexingState': indexingState }, function (data) {
            if (data == 'true') {
                getIndexingState(basepath, eltId, docId);
            }
        });
    };

    var refreshButton = exports.refreshButton = function (basepath, fileIds, infoStr) {
        $(document).ready(function () {
            var n = fileIds.length;
            var i = 0;
            var refreshButton = $("i.fa-refresh").parent("button");
            if (n == 0) {
                disableCompilatioButtons();
            } else {
                refreshButton.click(function () {
                    disableCompilatioButtons();
                    // Display progress bar.
                    $("#compilatio-home").html("<p>" + infoStr + "<progress id='compi-update-progress' value='" + i + "' max='" + n + "'></progress></p>");
                    $("#compilatio-logo").click();
                    // Launch ajax requests.
                    fileIds.forEach(function (id) {
                        $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_check_analysis.php', { 'id': id }, function (data) {
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

    return exports;

});
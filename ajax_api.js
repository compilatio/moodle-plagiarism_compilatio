define(['jquery'], function($) {

    function disableCompilatioButtons() {
        $(".compilatio-button").each(function() {
            $(this).attr("disabled", "disabled");
            $(this).addClass("disabled");
            $(this).attr("href", "#");
        });
    }

    return {
        get_indexing_state: function(basepath, eltId, docId) {
            $(document).ready(function() {
                $.post(basepath + '/plagiarism/compilatio/ajax/get_indexing_state.php', {'idDoc': docId}, function(data) {
                    $(".compi-" + eltId + " .library").detach();
                    $(".compi-" + eltId).prepend(data);
                });
            });
        },
        refresh_button: function(basepath, fileIds, infoStr) {
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
                        $("#compilatio-home").html("<p>" + infoStr + 
                            "<progress id='compi-update-progress' value='" + i + "' max='" + n + "'></progress></p>");
                        $("#compilatio-logo").click();
                        // Launch ajax requests.
                        fileIds.forEach(function(id) {
                            $.post(basepath + '/plagiarism/compilatio/ajax/compilatio_check_analysis.php',
                                    {'id': id}, function(data) {
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
        }
    };
});
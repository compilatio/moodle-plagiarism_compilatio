define(['jquery'], function($) {
    /**
     * A Module for Compilatio's plugin form
     */

    var exports = {};

    exports.afterPercentValues = function(strSimilarityPercent, strRedTreshold) {
        $(document).ready(function() {
            var txtGreen = $("<span>", {"class": "compilatio-after-input"}).text(strSimilarityPercent);
            var txtOrange = $("<span>", {"class": "compilatio-after-input"})
                .text(strSimilarityPercent + ", " + strRedTreshold + ".");
            $("#green_threshold").after(txtGreen);
            $("#orange_threshold").after(txtOrange);
        });
    };

    exports.requiredTermsOfService = function() {
        $(document).ready(function() {
            let activated = $("#id_activated");
            let tos = $("#id_termsofservice");

            if (activated.val() == 0) {
                tos.prop('checked', true);
                tos.closest('.form-group').hide();
            }

            activated.on('change', function() {
                if (this.value == 0) {
                    tos.prop('checked', true);
                    tos.closest('.form-group').hide();
                } else {
                    tos.prop('checked', false);
                    tos.closest('.form-group').show();
                }
            });
        });
    };

    return exports;
});
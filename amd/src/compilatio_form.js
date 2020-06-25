define(['jquery'], function ($) {
    /**
     * A Module for Compilatio's plugin form
     */

    var exports = {};

    var afterPercentValues = exports.afterPercentValues = function (strSimilarityPercent, strRedTreshold) {
        $(document).ready(function () {
            var txtGreen = $("<span>", {class:"compilatio-after-input"}).text(strSimilarityPercent);
            var txtOrange = $("<span>", {class:"compilatio-after-input"}).text(strSimilarityPercent + ", " + strRedTreshold + ".");
            $("#green_threshold").after(txtGreen);
            $("#orange_threshold").after(txtOrange);
        });
    };

    return exports;
});
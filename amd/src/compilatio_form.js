define(['jquery'], function($) {
    /**
     * A Module for Compilatio's plugin form
     */

    var exports = {};

    exports.afterPercentValues = function(strSimilarityPercent, strRedTreshold) {
        $(document).ready(function() {
            var txtGreen = $('<span>', {'class': 'ml-2'}).text(strSimilarityPercent);
            var txtOrange = $('<span>', {'class': 'ml-2'}).text(strSimilarityPercent + ', ' + strRedTreshold + '.');
            $('#warningthreshold').after(txtGreen);
            $('#criticalthreshold').after(txtOrange);
        });
    };

    return exports;
});
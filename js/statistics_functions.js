/**
 * CompilatioPercentage
 *
 * @param {int} v
 * @return {string} pourcentage.
 */
function compilatioPercentage(v) {
    return v + "%";
}

/**
 * CompilatioUrlSorter
 *
 * @param {string} a
 * @param {string} b
 * @return {Number}
 */
function compilatioUrlSorter(a, b) {

    // Strip tags to compare their content.
    a = a.replace(/(<([^>]+)>)/ig, "");
    b = b.replace(/(<([^>]+)>)/ig, "");
    return a.localeCompare(b);
}

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("cmp-table-no-js").style.display = "none";

    document.querySelectorAll('#cmp-table-js thead tr th').forEach(function(el, index) {
        switch (index) {
            case 0:
                el.setAttribute('data-field', 'course');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-sorter', 'compilatioUrlSorter');
                break;
            case 1:
                el.setAttribute('data-field', 'teacher');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-sorter', 'compilatioUrlSorter');
                break;
            case 2:
                el.setAttribute('data-field', 'assign');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-sorter', 'compilatioUrlSorter');
                break;
            case 3:
                el.setAttribute('data-field', 'analyzed_documents_count');
                el.setAttribute('data-sortable', 'true');
                break;
            case 4:
                el.setAttribute('data-field', 'minimum_rate');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-formatter', 'compilatioPercentage');
                break;
            case 5:
                el.setAttribute('data-field', 'maximum_rate');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-formatter', 'compilatioPercentage');
                break;
            case 6:
                el.setAttribute('data-field', 'average_rate');
                el.setAttribute('data-sortable', 'true');
                el.setAttribute('data-formatter', 'compilatioPercentage');
                break;
            case 7:
                el.setAttribute('data-field', 'errors');
                el.setAttribute('data-sortable', 'false');
                break;

            default:
                break;
        }
    });
});

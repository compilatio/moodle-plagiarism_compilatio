
document.addEventListener("DOMContentLoaded", function(event) {
    document.getElementById("table-no-js").style.display ="none";

    document.querySelectorAll('#table-js thead tr th').forEach((el, index) => {
        switch (index) {
            case 0:
                el.setAttribute('data-field','course');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-sorter','urlSorter');
                break;
            case 1:
                el.setAttribute('data-field','teacher');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-sorter','urlSorter');
                break;
            case 2:
                el.setAttribute('data-field','assign');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-sorter','urlSorter');
                break;
            case 3:
                el.setAttribute('data-field','analyzed_documents_count');
                el.setAttribute('data-sortable','true');
                break;
            case 4:
                el.setAttribute('data-field','minimum_rate');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-formatter','percentage');
                break;
            case 5:
                el.setAttribute('data-field','maximum_rate');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-formatter','percentage');
                break;
            case 6:
                el.setAttribute('data-field','average_rate');
                el.setAttribute('data-sortable','true');
                el.setAttribute('data-formatter','percentage');
                break;
            
            default:
                break;
        }
    });
});





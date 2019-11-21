var wait_message = '<?php echo get_string("loading", "plagiarism_compilatio"); ?>';

function percentage(v) {
    return v + "%";
}

function urlSorter(a, b) {
    
    //Strip tags to compare their content:
    a = a.replace(/(<([^>]+)>)/ig, "");
    b = b.replace(/(<([^>]+)>)/ig, "");
    console.log(a,b);
    return a.localeCompare(b)
}
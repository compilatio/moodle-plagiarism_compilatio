<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * statistics.php - Display global statistics about assignments
 *
 * @package   plagiarism_compilatio
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2012 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/output_helper.php');
require_login();
admin_externalpage_setup('plagiarismcompilatio');

$PAGE->requires->jquery();
$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$plagiarismplugin = new plagiarism_plugin_compilatio();

$plagiarismsettings = (array) get_config('plagiarism');

echo $OUTPUT->header();
$currenttab = 'compilatiostatistics';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$rows = compilatio_get_global_statistics();

if (count($rows) === 0) {
    echo get_string("no_statistics_yet", "plagiarism_compilatio");
} else {

    $url = new moodle_url('/plagiarism/compilatio/CSV.php');
    echo html_writer::tag('legend', get_string("global_statistics", "plagiarism_compilatio"), array('class'=>'compilatio_legend'));
    echo html_writer::tag('a', get_string("export_raw_csv", "plagiarism_compilatio"), array('href'=>$url, 'style'=>'margin-bottom:20px;', 'class'=>'comp-button'));
    echo html_writer::tag('legend', get_string("assign_statistics", "plagiarism_compilatio"), array('class'=>'compilatio_legend',));

    //bootstrap 
    echo html_writer::empty_tag('link', array('rel'=>'stylesheet','href'=>'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css'));
    echo html_writer::script('', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.js');
    //scripts function
    echo html_writer::script('', $CFG->wwwroot . '/plagiarism/compilatio/js/statistics_functions.js');

    $url = new moodle_url("/plagiarism/compilatio/stats_json.php");
    //noscript
    echo html_writer::start_tag('noscript');
        echo html_writer::empty_tag('link', array('rel'=>'stylesheet','href'=>$CFG->wwwroot. '/plagiarism/compilatio/css/no_js_styles.css'));
        echo get_string("enable_javascript", "plagiarism_compilatio");
    echo html_writer::end_tag('noscript');

    echo html_writer::tag('h5',"Compilatio - " . get_string("similarities", "plagiarism_compilatio"), array('colspan'=>'4'));
    
    $table = new html_table();
    $table->id = 'compilatio-table-js';
    $table->attributes['data-toggle'] = 'table';
    $table->attributes['data-url'] = $url;

    $table_head_js = array(get_string("course"), get_string("teacher", "plagiarism_compilatio"), get_string("modulename", "assign"), str_replace(" ", "<br/>", get_string("documents_number", "plagiarism_compilatio")), get_string("minimum", "plagiarism_compilatio"), get_string("maximum", "plagiarism_compilatio"), get_string("average", "plagiarism_compilatio"));
    
    $table->head  = $table_head_js;
    echo html_writer::table($table);
    
    $table_no_js = new html_table();
    $table_no_js->id = 'compilatio-table-no-js';
    $table_no_js->attributes['class'] = 'table table-striped table-bordered table-hover';
    $table_head = array(get_string("course"), get_string("teacher", "plagiarism_compilatio"), get_string("modulename", "assign"), str_replace(" ", "<br/>", get_string("documents_number", "plagiarism_compilatio")), get_string("minimum", "plagiarism_compilatio"), get_string("maximum", "plagiarism_compilatio"), get_string("average", "plagiarism_compilatio"));
    $table_no_js->head = $table_head;
    foreach ($rows as $row) {
        $table_no_js->data[] = array ($row["course"],$row["teacher"], $row["assign"], $row["analyzed_documents_count"], $row["minimum_rate"], $row["maximum_rate"], $row["average_rate"]);
    }
    echo html_writer::table($table_no_js);
    $url = new moodle_url('/plagiarism/compilatio/CSV.php', array("raw" => 0));
    echo html_writer::tag('a', get_string("export_global_csv", "plagiarism_compilatio"), array('href'=>$url, 'style'=>'margin-bottom:20px;', 'class'=>'comp-button'));
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

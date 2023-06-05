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
 * statistics.php - Display global statistics about course modules
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');
require_login();
admin_externalpage_setup('plagiarismcompilatio');

$PAGE->requires->jquery();
$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

$plagiarismplugin = new plagiarism_plugin_compilatio();

$plagiarismsettings = (array) get_config('plagiarism_compilatio');

echo $OUTPUT->header();
$currenttab = 'compilatiostatistics';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$rows = CompilatioStatistics::get_global_statistics();

if (count($rows) === 0) {
    echo get_string('no_statistics_yet', 'plagiarism_compilatio');
} else {

    $url = new moodle_url('/plagiarism/compilatio/CSV.php');
    echo html_writer::tag('legend', get_string('global_statistics', 'plagiarism_compilatio'), [
        'class' => 'cmp-legend'
    ]);
    echo html_writer::tag('p', get_string('global_statistics_description', 'plagiarism_compilatio'));
    echo html_writer::tag('a', get_string('export_raw_csv', 'plagiarism_compilatio'), [
        'href' => $url,
        'class' => 'mb-3 cmp-btn cmp-btn-primary'
    ]);
    echo html_writer::tag('legend', get_string('activities_statistics', 'plagiarism_compilatio'), [
        'class' => 'cmp-legend'
    ]);

    // Bootstrap.
    echo html_writer::empty_tag('link', [
        'rel' => 'stylesheet',
        'href' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css'
    ]);
    echo html_writer::script('', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.js');
    // Scripts function.
    echo html_writer::script('', $CFG->wwwroot . '/plagiarism/compilatio/js/statistics_functions.js');

    $url = new moodle_url('/plagiarism/compilatio/stats_json.php');

    echo html_writer::tag('h5', 'Compilatio - ' . get_string('similarities', 'plagiarism_compilatio'), ['colspan' => '4']);

    $table = new html_table();
    $table->id = 'cmp-table-js';
    $table->attributes['data-toggle'] = 'table';
    $table->attributes['data-url'] = $url;

    $tableheadjs = [
        get_string('course'),
        get_string('teacher', 'plagiarism_compilatio'),
        get_string('activity', 'plagiarism_compilatio'),
        str_replace(' ', '<br/>', get_string('documents_number', 'plagiarism_compilatio')),
        get_string('minimum', 'plagiarism_compilatio'),
        get_string('maximum', 'plagiarism_compilatio'),
        get_string('average', 'plagiarism_compilatio'),
        get_string('stats_errors', 'plagiarism_compilatio')
    ];

    $table->head  = $tableheadjs;
    echo html_writer::table($table);

    $tablenojs = new html_table();
    $tablenojs->id = 'cmp-table-no-js';
    $tablenojs->attributes['class'] = 'table table-striped table-bordered table-hover';
    $tablehead = [
        get_string('course'),
        get_string('teacher', 'plagiarism_compilatio'),
        get_string('activity', 'plagiarism_compilatio'),
        str_replace(' ', '<br/>', get_string('documents_number', 'plagiarism_compilatio')),
        get_string('minimum', 'plagiarism_compilatio'),
        get_string('maximum', 'plagiarism_compilatio'),
        get_string('average', 'plagiarism_compilatio'),
        get_string('stats_errors', 'plagiarism_compilatio')
    ];

    $tablenojs->head = $tablehead;
    foreach ($rows as $row) {
        $tablenojs->data[] = [
            $row['course'],
            $row['teacher'],
            $row['activity'],
            $row['analyzed_documents_count'],
            $row['minimum_rate'],
            $row['maximum_rate'],
            $row['average_rate'],
            $row['errors']
        ];
    }

    echo html_writer::table($tablenojs);
    $url = new moodle_url('/plagiarism/compilatio/CSV.php', ['raw' => 0]);
    echo html_writer::tag('a', get_string('export_global_csv', 'plagiarism_compilatio'), [
        'href' => $url,
        'class' => 'mb-3 cmp-btn cmp-btn-primary'
    ]);
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

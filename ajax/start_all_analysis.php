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
 * Start analysis for all document in course module
 * 
 * @package    plagiarism_cmp
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/cmp/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/cmp/classes/compilatio/send_file.php');
require_once($CFG->dirroot . '/plagiarism/cmp/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/cmp/lib.php');

require_login();

global $DB, $SESSION;

$cmid = required_param('cmid', PARAM_TEXT);

$plugincm = cmp_cm_use($cmid);

// Counter incremented on success.
$countsuccess = 0;
$plagiarismfiles = $docsfailed = $docsinextraction = [];

if ($plugincm->analysistype == 'manual') {

    $sql = "cm = ? AND status = 'sent'";
    $plagiarismfiles = $DB->get_records_select('plagiarism_cmp_files', $sql, [$cmid]);

    foreach ($plagiarismfiles as $file) {

        if (cmp_student_analysis($plugincm->studentanalyses, $cmid, $file->userid)) {
            continue;
        }

        $status = CompilatioAnalyses::start_analysis($file);

        if ($status == 'queue') {
            $countsuccess++;
        } else if ($status == get_string('extraction_in_progress', 'plagiarism_cmp')) {
            $docsinextraction[] = $file->filename;
        } else {
            $docsfailed[] = $file->filename;
        }
    }
}

if (count($plagiarismfiles) === 0) {
    $SESSION->compilatio_alert = [
        'class' => 'info',
        'content' => get_string('no_document_available_for_analysis', 'plagiarism_cmp'),
    ];
} else {
    if ($countsuccess > 0) {
        $SESSION->compilatio_alert = [
            'class' => 'info',
            'content' => get_string('analysis_started', 'plagiarism_cmp', $countsuccess)
        ];
    }

    if (count($docsfailed) > 0) {
        $SESSION->compilatio_alert = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed', 'plagiarism_cmp')
                . '<ul><li>' . implode('</li><li>', $docsfailed) . '</li></ul></div>',
        ];
    }

    if (count($docsinextraction) > 0) {
        $SESSION->compilatio_alert = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed_extracting', 'plagiarism_cmp')
                . '<ul><li>' . implode('</li><li>', $docsinextraction) . '</li></ul></div>',
        ];
    }
}

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
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();

global $DB, $SESSION;

$cmid = required_param('cmid', PARAM_TEXT);
$selectedlines = optional_param('selectedUsers', "", PARAM_TEXT);
$plugincm = compilatio_cm_use($cmid);
$module = get_coursemodule_from_id(null, $cmid);
// Counter incremented on success.
$countsuccess = 0;
$plagiarismfiles = $docsfailed = $docsinextraction = $SESSION->compilatio_alerts = [];


if ($plugincm->analysistype == 'manual') {
    if ($module->modname == "quiz" && !empty($selectedlines)) {
        $sql = "SELECT cmpFile.* 
            FROM {plagiarism_compilatio_file} cmpFile
            INNER JOIN {user} ON {user}.id = cmpFile.userid 
            INNER JOIN {quiz_attempts} ON {quiz_attempts}.userid = {user}.id 
            WHERE {quiz_attempts}.id IN ('".$selectedlines."') AND cmpFile.status='sent' AND cmpFile.cm = ?";
        $plagiarismfiles = $DB->get_records_sql($sql, [$cmid]);
    } else {
        $sql = "cm = ? AND status = 'sent'";
        $sql .= !empty($selectedlines) ? " AND userid IN (" . $selectedlines . ")" : "";
        $plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_file', $sql, [$cmid]);
    }
    error_log(var_export($plagiarismfiles, true));

    foreach ($plagiarismfiles as $file) {

        if (compilatio_student_analysis($plugincm->studentanalyses, $cmid, $file->userid)) {
            continue;
        }
        $status = CompilatioAnalyses::start_analysis($file);
        if ($status == 'queue') {
            $countsuccess++;
        } else if ($status == get_string('extraction_in_progress', 'plagiarism_compilatio')) {
            $docsinextraction[] = $file->filename;
        } else {
            $docsfailed[] = $file->filename;
        }
    }
}

if (count($plagiarismfiles) === 0) {
    $SESSION->compilatio_alerts[] = [
        'class' => 'info',
        'content' => get_string('no_document_available_for_analysis', 'plagiarism_compilatio'),
    ];
} else {
    if ($countsuccess > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'info',
            'content' => get_string('analysis_started', 'plagiarism_compilatio', $countsuccess),
        ];
    }

    if (count($docsfailed) > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsfailed) . '</li></ul></div>',
        ];
    }

    if (count($docsinextraction) > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed_extracting', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsinextraction) . '</li></ul></div>',
        ];
    }
}

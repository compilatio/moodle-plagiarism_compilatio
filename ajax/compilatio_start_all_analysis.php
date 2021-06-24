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
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

// Get constants.
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

require_login();

global $DB, $SESSION;

$cmid = required_param('cmid', PARAM_TEXT);

$plugincm = compilatio_cm_use($cmid);

// Counter incremented on success.
$countsuccess = 0;
$plagiarismfiles = array();
$docsfailed = array();

if ($plugincm['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_MANUAL) {

    $sql = "cm = ? AND statuscode = ?";
    $params = array($cmid, COMPILATIO_STATUSCODE_ACCEPTED);
    $plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);

    foreach ($plagiarismfiles as $file) {

        if (compilatio_student_analysis($plugincm['compi_student_analyses'], $cmid, $file->userid)) {
            continue;
        }

        if (compilatio_startanalyse($file)) {
            $countsuccess++;
        } else {
            $docsfailed[] = $file["filename"];
        }
    }
}

// Handle not sent documents :.
$files = compilatio_get_non_uploaded_documents($cmid);
$countbegin = count($files);

if ($countbegin != 0) {
    define("COMPILATIO_MANUAL_SEND", true);
    compilatio_upload_files($files, $cmid);
    $countsuccess += $countbegin - count(compilatio_get_non_uploaded_documents($cmid));
}

$counttotal = count($plagiarismfiles) + $countbegin;
$counterrors = count($docsfailed);

if ($counttotal === 0) {
    $SESSION->compilatio_alert = array(
        "class" => "info",
        "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
        "content" => get_string("no_document_available_for_analysis", "plagiarism_compilatio"),
    );
} else if ($counterrors === 0) {
    $SESSION->compilatio_alert = array(
        "class" => "info",
        "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
        "content" => get_string("analysis_started", "plagiarism_compilatio", $countsuccess),
    );
} else {
    $SESSION->compilatio_alert = array(
        "class" => "danger",
        "title" => get_string("not_analyzed", "plagiarism_compilatio"),
        "content" => "<ul><li>" . implode("</li><li>", $docsfailed) . "</li></ul>",
    );
}

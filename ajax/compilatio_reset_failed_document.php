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
 * Restart all the failed analyses for documents of the course module
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
global $DB, $PAGE;

$cmid = required_param('cmid', PARAM_TEXT);

$plagiarismsettings = (array) get_config('plagiarism_compilatio');

$docsmaxattempsreached = array();

$sql = "SELECT * FROM {plagiarism_compilatio_files}
    WHERE cm=? AND (statuscode LIKE '41_' OR statuscode='timeout')";
$plagiarismfiles = $DB->get_records_sql($sql, [$cmid]);

compilatio_remove_duplicates($plagiarismfiles, false);

foreach ($plagiarismfiles as $plagiarismfile) {
    if ($plagiarismfile->statuscode == 'timeout') {
        $plagiarismfile->statuscode = 'pending';
        $plagiarismfile->attempt = 0;
        $plagiarismfile->timesubmitted = time();
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    } else if ($plagiarismfile->attempt < COMPILATIO_MAX_SUBMISSION_ATTEMPTS) {
        $plagiarismfile->statuscode = 'pending';
        $plagiarismfile->attempt++;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    } else {
        $docsmaxattempsreached[] = $plagiarismfile->filename;
    }
}

compilatio_send_pending_files($plagiarismsettings);

$countmaxattemptsreached = count($docsmaxattempsreached);
$files = compilatio_get_max_attempts_files($cmid);
if ($countmaxattemptsreached !== 0) {
    $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
    $SESSION->compilatio_alert = array(
        "class" => "danger",
        "title" => get_string("max_attempts_reach_files", "plagiarism_compilatio"),
        "content" => $list,
    );
}

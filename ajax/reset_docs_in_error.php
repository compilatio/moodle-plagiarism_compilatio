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
 * Reset failed analyses and unsent documents of the course module
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();
global $DB, $PAGE;

$cmid = required_param('cmid', PARAM_TEXT);

$compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

// Restart failed analyses.
$files = $DB->get_records("plagiarism_compilatio_files", array("cm" => $cmid, "status" => "error_analysis_failed"));

$countsuccess = 0;
$docsfailed = array();
foreach ($files as $file) {
    $userid = $DB->get_field("plagiarism_compilatio_module", "userid", array("cmid" => $file->cm));
    $compilatio->set_user_id($userid);

    if ($compilatio->delete_analyse($file->externalid) && $compilatio->start_analyse($file->externalid)) {
        $countsuccess++;
    } else {
        $docsfailed[] = $file->filename;
    }

    $file->status = 'queue';
    $DB->update_record('plagiarism_compilatio_files', $file);
}

if (count($docsfailed) === 0) {
    $SESSION->compilatio_alert = [
        "class" => "info",
        "content" => get_string("analysis_started", "plagiarism_compilatio", $countsuccess),
    ];
} else {
    $SESSION->compilatio_alert = [
        "class" => "danger",
        "content" => get_string("not_analyzed", "plagiarism_compilatio") . "<ul><li>" . implode("</li><li>", $docsfailed) . "</li></ul>",
    ];
}

// Send sending failed files.
$files = $DB->get_records("plagiarism_compilatio_files", array("cm" => $cmid, "status" => "error_sending_failed"));

// TODO resend text content.

$fs = get_file_storage();

foreach ($files as $cmpfile) {
    $module = get_coursemodule_from_id(null, $cmpfile->cm);

    $modulecontext = context_module::instance($cmpfile->cm);
    $contextid = $modulecontext->id;
    $sql = "SELECT * FROM {files} f WHERE f.contenthash= ? AND contextid = ?";
    $f = $DB->get_record_sql($sql, array($cmpfile->identifier, $contextid));
    if (empty($f)) {
        continue;
    }
    $file = $fs->get_file_by_id($f->id);

    $DB->delete_records('plagiarism_compilatio_files', array('id' => $cmpfile->id));

    CompilatioSendFile::send_file($cmpfile->cm, $cmpfile->userid, $file);
}

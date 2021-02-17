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
 * Start a document analysis via Compilatio SOAP API
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['id']
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

global $DB;

$docid = required_param('docId', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);

if (!$cmid) {
    $plagiarismfile = $DB->get_record('plagiarism_compilatio_files', array('id' => $docid));
    $analyse = compilatio_startanalyse($plagiarismfile);
} else {
    $file = $DB->get_record("files", array("id" => $docid));
    if (!defined("COMPILATIO_MANUAL_SEND")) {
        define("COMPILATIO_MANUAL_SEND", true); // Hack to hide mtrace in function execution.
        compilatio_upload_files(array($file), $cmid);
    }
}


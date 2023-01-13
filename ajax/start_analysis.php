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
 * Start a document analysis via Compilatio API
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['docId']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();

global $DB;

$docid = required_param('docId', PARAM_RAW);

$plagiarismfile = $DB->get_record('plagiarism_compilatio_files', array('id' => $docid));

$status = CompilatioAnalyses::start_analysis($plagiarismfile);

$res = new StdClass();

if ($status == "queue") {
    $res->documentFrame = "<div title='" . get_string('title_' . $status, "plagiarism_compilatio") . "' class='cmp-btn-secondary'>
            <i class='cmp-icon-lg cmp-mr-10 fa fa-spinner fa-spin'></i>"
            . get_string('btn_' . $status, "plagiarism_compilatio") .
        "</div>";
    $res->bgcolor = 'primary';
} else if (strpos($status, "error") === 0) {
    if ($status == "error_too_long") {
        $value = get_config('plagiarism_compilatio', 'max_word');
    } else if ($status == "error_too_short") {
        $value = get_config('plagiarism_compilatio', 'min_word');
    }

    $res->documentFrame = "<div title='" . get_string("title_" . $status, "plagiarism_compilatio", $value ?? null) . "' class='cmp-btn cmp-btn-error'>
            <i class='cmp-mr-10 fa fa-exclamation-triangle'></i>" . get_string('btn_error', "plagiarism_compilatio") . 
        "</div>";
    $res->bgcolor = 'error';
} else {
    $res->error = get_string('failedanalysis', 'plagiarism_compilatio') . $status;
}

echo json_encode($res);



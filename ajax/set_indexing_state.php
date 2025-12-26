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
 * Set document indexing state via Compilatio API
 *
 * @package   plagiarism_compilatio
 * @copyright 2025 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['docId']
 * @param   string $_POST['indexingState']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\compilatio\api;

require_login();
global $DB;

$docid = optional_param('docId', '', PARAM_TEXT);
$file = $DB->get_record('plagiarism_compilatio_files', ['id' => $docid]);

$context = context_module::instance($file->cm);
require_capability('plagiarism/compilatio:triggeranalysis', context::instance_by_id($context->id));

// Get global Compilatio settings.
$plagiarismsettings = (array) get_config('plagiarism_compilatio');
$indexingstatepost = optional_param('indexingState', '', PARAM_TEXT);

if (isset($docid) && isset($indexingstatepost)) {
    $indexingstate = (int) ((bool) $indexingstatepost);

    $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $file->cm]);
    $compilatio = new api($userid);

    $response = new stdClass();
    if ($compilatio->set_indexing_state($file->externalid, $indexingstate) === true) {
        $file->indexed = $indexingstate;
        $DB->update_record('plagiarism_compilatio_files', $file);
        $response->status = 'ok';
        if ($indexingstate == '0') {
            $response->text = get_string('not_indexed_document', 'plagiarism_compilatio');
        } else {
            $response->text = get_string('indexed_document', 'plagiarism_compilatio');
        }
    } else {
        $response->status = 'error';
    }
    echo(json_encode($response));
}

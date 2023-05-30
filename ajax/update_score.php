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
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['docId']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/documentFrame.php');

require_login();
global $DB;

$docid = required_param('docId', PARAM_TEXT);

$file = $DB->get_record('plagiarism_compilatio_file', ['id' => $docid]);

if (!empty($file)) {
    CompilatioAnalyses::check_analysis($file);

    $file = $DB->get_record('plagiarism_compilatio_file', ['id' => $docid]);
    $cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $file->cm]);

    echo CompilatioDocumentFrame::get_score($file->similarityscore, $cmconfig);
}

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
 * Update similarity score state for a document
 *
 * @package   plagiarism_compilatio
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['docId']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\compilatio\analysis;
use plagiarism_compilatio\output\document_frame;

require_login();

global $DB, $USER;

$docid = required_param('docId', PARAM_TEXT);

$file = $DB->get_record('plagiarism_compilatio_files', ['id' => $docid]);

if (!empty($file)) {
    $file = analysis::check_analysis($file);

    $cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $file->cm]);

    echo document_frame::get_score($file, $cmconfig, true);
}

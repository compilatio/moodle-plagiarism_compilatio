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
 * @package   plagiarism_compilatio
 * @copyright 2025 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\compilatio\file;

require_login();

$cmid = required_param('cmid', PARAM_TEXT);
$context = context_module::instance($cmid);
require_capability('plagiarism/compilatio:triggeranalysis', context::instance_by_id($context->id));

global $SESSION;

// Handle not sent documents :.
$files = compilatio_get_unsent_documents($cmid);

if (count($files) != 0) {
    file::send_unsent_files($files, $cmid);
    $countsuccess = count($files) - count(compilatio_get_unsent_documents($cmid));
}

if ($countsuccess > 0) {
    $SESSION->compilatio_alerts = [
        [
            'class' => 'info',
            'content' => get_string('document_sent', 'plagiarism_compilatio', $countsuccess),
        ],
    ];
}

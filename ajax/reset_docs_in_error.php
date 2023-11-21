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
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();
global $DB;

$cmid = required_param('cmid', PARAM_TEXT);

$compilatio = new CompilatioAPI();

$SESSION->compilatio_alerts = [];

// Restart failed analyses.
$files = $DB->get_records('plagiarism_compilatio_file', ['cm' => $cmid, 'status' => 'error_analysis_failed']);

if (!empty($files)) {
    compilatio_delete_files($files);
}

// Send failed files.
$files = array_merge($files, $DB->get_records('plagiarism_compilatio_file', ['cm' => $cmid, 'status' => 'error_sending_failed']));

if (!empty($files)) {
    $countsuccess = 0;
    $docsfailed = [];

    foreach ($files as $cmpfile) {
        $success = CompilatioSendFile::retrieve_and_send_file($cmpfile);

        if ($success) {
            $countsuccess++;
        } else {
            $docsfailed[] = $cmpfile->filename;
        }
    }

    if (count($docsfailed) === 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'info',
            'content' => get_string('document_sent', 'plagiarism_compilatio', $countsuccess),
        ];
    } else {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsfailed) . '</li></ul></div>',
        ];
    }
}

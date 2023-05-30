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

$compilatio = new CompilatioAPI();

$SESSION->compilatio_alerts = [];

// Restart failed analyses.
$files = $DB->get_records('plagiarism_compilatio_file', ['cm' => $cmid, 'status' => 'error_analysis_failed']);

if (!empty($files)) {
    $countsuccess = 0;
    $docsfailed = [];
    foreach ($files as $file) {
        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $file->cm]);
        $compilatio->set_user_id($userid);

        if ($compilatio->delete_analyse($file->externalid) && $compilatio->start_analyse($file->externalid)) {
            $countsuccess++;
            $file->status = 'queue';
            $DB->update_record('plagiarism_compilatio_file', $file);
        } else {
            $docsfailed[] = $file->filename;
        }
    }

    if (count($docsfailed) === 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'info',
            'content' => get_string('analysis_started', 'plagiarism_compilatio', $countsuccess),
        ];
    } else {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsfailed) . '</li></ul></div>',
        ];
    }
}

// Send failed files.
$files = $DB->get_records('plagiarism_compilatio_file', ['cm' => $cmid, 'status' => 'error_sending_failed']);

if (!empty($files)) {
    $fs = get_file_storage();

    $countsuccess = 0;
    $docsfailed = [];

    foreach ($files as $cmpfile) {
        // Text content.
        if (preg_match('~.htm$~', $cmpfile->filename)) {
            $objectid = explode(".", explode("-", $cmpfile->filename)[1])[0];

            $sql = "SELECT m.name FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE cm.id = ?";
            $modulename = $DB->get_field_sql($sql, [$cmid]);

            switch ($modulename) {
                case 'assign':
                    $content = $DB->get_field('assignsubmission_onlinetext', 'onlinetext', ['submission' => $objectid]);
                    break;
                case 'workshop':
                    $content = $DB->get_field('workshop_submissions', 'content', ['id' => $objectid]);
                    break;
                case 'forum':
                    $content = $DB->get_field('forum_posts', 'message', ['id' => $objectid]);
                    break;
                case 'quiz':
                    $questionid = substr(explode('.', $cmpfile->filename)[0], strpos($cmpfile->filename, "Q") + 1);

                    $sql = "SELECT responsesummary
                    FROM {quiz_attempts} quiz
                    JOIN {question_attempts} qa ON quiz.uniqueid = qa.questionusageid
                    WHERE quiz.id = ? AND qa.questionid = ?";
                    $content = $DB->get_field_sql($sql, [$objectid, $questionid]);
                    break;
            }

            if (!empty($content)) {
                $DB->delete_records('plagiarism_compilatio_file', ['id' => $cmpfile->id]);

                $success = CompilatioSendFile::send_file($cmid, $cmpfile->userid, null, $cmpfile->filename, $content);
            }
        } else { // File.
            $module = get_coursemodule_from_id(null, $cmpfile->cm);

            $modulecontext = context_module::instance($cmpfile->cm);
            $contextid = $modulecontext->id;
            $sql = 'SELECT * FROM {files} f WHERE f.contenthash= ? AND contextid = ?';
            $f = $DB->get_record_sql($sql, [$cmpfile->identifier, $contextid]);
            if (empty($f)) {
                continue;
            }
            $file = $fs->get_file_by_id($f->id);

            $DB->delete_records('plagiarism_compilatio_file', ['id' => $cmpfile->id]);

            $success = CompilatioSendFile::send_file($cmpfile->cm, $cmpfile->userid, $file);
        }

        if ($success) {
            $countsuccess++;
        } else {
            $docsfailed[] = $file->filename;
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
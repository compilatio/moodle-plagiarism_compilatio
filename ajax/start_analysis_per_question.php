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
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

require_login();

global $DB, $SESSION;

$cmid = required_param('cmid', PARAM_TEXT);
$questionid = required_param('slotid', PARAM_TEXT);
$quizid = required_param('quizid', PARAM_TEXT);

$plugincm = compilatio_cm_use($cmid);
$module = get_coursemodule_from_id(null, $cmid);

$countsuccess = 0;
$plagiarism_compilatio_files = $docsfailed = $docsinextraction = $SESSION->compilatio_alerts = [];
$cmpfiles = [];
$context = context_module::instance($cmid);

if ($plugincm->analysistype == 'manual') {
    $quizattempts = $DB->get_records('quiz_attempts', ['quiz'=> $quizid]);
    
    foreach ($quizattempts as $quizattempt) {

        $attempt = $CFG->version < 2023100900 ? \quiz_attempt::create($quizattempt->id) : \mod_quiz\quiz_attempt::create($quizattempt->id);
        
        foreach ($attempt->get_slots() as $slot) {
            
            if ($attempt->get_question_attempt($slot)->get_question_id() == $questionid) {
                $answer = $attempt->get_question_attempt($slot);
                $courseid = $DB->get_field('course_modules', 'course', array('id' => $cmid));
                $filename = "quiz-" . $courseid . "-" . $cmid . "-" . $quizattempt->id . "-Q" . $answer->get_question_id() . ".htm";

                $cmpfiles[] = $DB->get_record('plagiarism_compilatio_files', ['cm' => $cmid, 'identifier' => sha1($filename), 'status' => 'sent']);
                
                $files = $answer->get_last_qt_files('attachments', $context->id);
                foreach ($files as $file) {
                    $cmpfiles[] = $DB->get_record('plagiarism_compilatio_files', ['cm' => $cmid, 'userid' => $studentid, 'identifier' => $file->get_contenthash()]);
                }
            }
        }
    }

    foreach ($cmpfiles as $file) {
        if (empty($file) || compilatio_student_analysis($plugincm->studentanalyses, $cmid, $file->userid)) {
            continue;
        }
        $status = CompilatioAnalyses::start_analysis($file);
        if ($status == 'queue') {
            $countsuccess++;
        } else if ($status == get_string('extraction_in_progress', 'plagiarism_compilatio')) {
            $docsinextraction[] = $file->filename;
        } else {
            $docsfailed[] = $file->filename;
        }
    }
}

if (count($plagiarism_compilatio_files) === 0) {
    $SESSION->compilatio_alerts[] = [
        'class' => 'info',
        'content' => get_string('no_document_available_for_analysis', 'plagiarism_compilatio'),
    ];
} else {
    if ($countsuccess > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'info',
            'content' => get_string('analysis_started', 'plagiarism_compilatio', $countsuccess),
        ];
    }

    if (count($docsfailed) > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsfailed) . '</li></ul></div>',
        ];
    }

    if (count($docsinextraction) > 0) {
        $SESSION->compilatio_alerts[] = [
            'class' => 'danger',
            'content' => '<div>' . get_string('not_analyzed_extracting', 'plagiarism_compilatio')
                . '<ul><li>' . implode('</li><li>', $docsinextraction) . '</li></ul></div>',
        ];
    }
}

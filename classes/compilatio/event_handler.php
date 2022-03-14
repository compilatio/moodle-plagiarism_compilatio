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
 * event_handler.php - Contains methods to communicate with Compilatio REST API.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2020 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');

/**
 * CompilatioEventHandler class
 * @copyright  2020 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CompilatioEventHandler {
    /**
     * Event handler
     * @param  array $eventdata  Event data
     * @param  bool  $hasfile    There is a file ?
     * @param  bool  $hascontent There is a content ?
     * @return mixed             Return null if plugin is not enabled, void otherwise
     */
    public static function handle_event($eventdata, $hasfile = true, $hascontent = true) {
        error_log(var_export($eventdata,true));

        $cmid = $eventdata["contextinstanceid"];

        if ($eventdata['objecttable'] == 'quiz_attempts' && $eventdata['action'] == 'submitted') {
            $attemptid = $eventdata['objectid'];
            self::handle_quiz_attempt($attemptid);
            return;
        }

        if ($eventdata['crud'] != 'u' && $eventdata['crud'] != 'd'
            && $eventdata['component'] != 'tool_recyclebin') {
            if (!compilatio_enabled($cmid)) {
                return;
            }
        }

        global $CFG, $DB, $SESSION;
        $duplicates = array();
        // Get user id.
        $userid = $eventdata['relateduserid'];
        if ($userid == null) {
            $userid = $eventdata['userid'];
        }

        // Get course module.
        $cm = get_coursemodule_from_id(null, $cmid);

        // Deletion events.
        if ($eventdata['crud'] == 'd') {
            // In forums.
            if ($eventdata['objecttable'] == 'forum_posts') {
                if (!isset($SESSION->compilatio_bin_created)) {
                    $filename = 'post-' . $eventdata['courseid'] . '-' . $cmid . '-' . $eventdata['objectid'] . '.htm';
                    $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE filename like ? AND recyclebinid IS NULL";
                    $posts = $DB->get_records_sql($sql, array($filename));

                    $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename like ? AND recyclebinid IS NULL";
                    $attachments = $DB->get_records_sql($sql, array($cmid, 'post-' . $eventdata['objectid'] . '-%'));

                    $duplicates = array_merge($posts, $attachments);
                }
            }

            // In workshops.
            if ($eventdata['objecttable'] == 'workshop_submissions') {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
            }

            // In quiz.
            if ($eventdata['objecttable'] == 'quiz_attempts') {
                $filename = "quiz-" . $eventdata['courseid'] . "-" . $cmid . "-" . $eventdata['objectid'] . ".htm";
                $duplicates = $DB->get_records('plagiarism_compilatio_files',
                    array('cm' => $cmid, 'userid' => $userid, 'filename' => $filename));
                compilatio_delete_files($duplicates);

                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'quiz-%'";
                $duplicates = $DB->get_records_sql($sql, array($cmid, $userid));
            }

            // Course module delete.
            if ($eventdata['objecttable'] == 'course_modules') {
                if (class_exists('\tool_recyclebin\course_bin') && \tool_recyclebin\course_bin::is_enabled()) {
                    $DB->set_field('plagiarism_compilatio_files', 'recyclebinid',
                        $SESSION->compilatio_bin_created, array('cm' => $cmid));
                    unset($SESSION->compilatio_bin_created);
                } else {
                    $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid));
                }
            }

            // Recycle_bin item deleted.
            if ($eventdata['objecttable'] == 'tool_recyclebin_course' ||
                $eventdata['objecttable'] == 'tool_recyclebin_category') {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('recyclebinid' => $eventdata['objectid']));
            }

            // User delete.
            if ($eventdata['objecttable'] == 'user') {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('userid' => $eventdata['objectid']));
            }

            // Course reset.
            if ($eventdata['target'] == 'course_reset') {
                $options = $eventdata['other']['reset_options'];

                $modules = [
                    'assign' => "reset_assign_submissions",
                    'quiz' => "reset_quiz_attempts",
                    'workshop' => "reset_workshop_submissions",
                    'forum' => "reset_forum_all",
                ];

                foreach ($modules as $modulename => $option) {
                    if (isset($options[$option]) && $options[$option] == 1) {
                        compilatio_course_delete($eventdata['courseid'], $modulename);
                    }
                }
            }
            compilatio_delete_files($duplicates);
        }

        // Update events.
        if ($eventdata['crud'] == 'u') {
            // Restored recycle_bin.
            if ($eventdata['objecttable'] == 'tool_recyclebin_course' ||
                $eventdata['objecttable'] == 'tool_recyclebin_category') {

                // Update 'filename' for restored forum posts.
                $posts = $DB->get_records_sql('SELECT * FROM {plagiarism_compilatio_files} WHERE recyclebinid = ?
                    AND filename LIKE \'post%\'', array($eventdata['objectid']));

                $cmids = array();

                foreach ($posts as $post) {
                    $restoredpost = $DB->get_record('plagiarism_compilatio_files',
                        array('filename' => $post->filename, 'recyclebinid' => null));

                    if (!isset($courseid)) {
                        $courseid = $DB->get_record('course_modules', array('id' => $restoredpost->cm), 'course')->course;
                    }

                    if (preg_match('~^post-\d+-\d+-\d+.htm$~', $restoredpost->filename)) {
                        if (!in_array($restoredpost->cm, $cmids)) {
                            array_push($cmids, $restoredpost->cm);
                        }
                    } else {
                        $moodlefile = $DB->get_record('files',
                            array('filename' => $restoredpost->filename, 'filearea' => 'attachment'));

                        $filename = substr($restoredpost->filename, strpos($restoredpost->filename, '-') + 1);
                        $filename = substr($filename, strpos($filename, '-'));
                        $filename = 'post-' . $moodlefile->itemid . $filename;

                        $DB->set_field('files', 'filename', $filename, array('filename' => $restoredpost->filename));
                        $DB->set_field('plagiarism_compilatio_files', 'filename', $filename, array('id' => $restoredpost->id));
                    }
                }

                foreach ($cmids as $cmid) {
                    $sql = '
                        SELECT forum_posts.id, forum_posts.message
                        FROM {course_modules} course_modules
                        JOIN {forum} forum ON course_modules.instance= forum.id AND course_modules.module= 9
                        JOIN {forum_discussions} forum_discussions ON forum.id= forum_discussions.forum
                        JOIN {forum_posts} forum_posts ON forum_discussions.id= forum_posts.discussion
                        WHERE course_modules.id = ?';
                    $posts = $DB->get_records_sql($sql, array($cmid));

                    foreach ($posts as $post) {
                        $filename = 'post-' . $courseid . '-' . $cmid . '-' . $post->id . '.htm';
                        $DB->set_field('plagiarism_compilatio_files', 'filename', $filename,
                            array('identifier' => sha1($post->message), 'cm' => $cmid));
                    }
                }

                $DB->delete_records('plagiarism_compilatio_files', array('recyclebinid' => $eventdata['objectid']));
            }

            // Delete in assign.
            if ($eventdata['target'] == 'submission_status' && $eventdata['other']['newstatus'] != 'draft') {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
                compilatio_delete_files($duplicates);
            }

            // Re-submit file when student submit a draft submission.
            $plugincm = compilatio_cm_use($cmid);
            if ($eventdata['target'] == 'assessable' && $plugincm->studentanalyses === '1') {

                $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
                compilatio_delete_files($plagiarismfiles, false);

                foreach ($plagiarismfiles as $pf) {
                    $pf->externalid = null;
                    $pf->reporturl = null;
                    $pf->status = 'pending';
                    $pf->similarityscore = 0;
                    $pf->attempt = 0;
                    $pf->recyclebinid = null;
                    $pf->docId = null;
                    $pf->timesubmitted = time();

                    $DB->update_record('plagiarism_compilatio_files', $pf);
                }
            }
        }

        // Creation events.
        if ($eventdata['crud'] == 'c') {
            // Course module recycle_bin created.
            if ($eventdata['objecttable'] == 'tool_recyclebin_course') {
                $SESSION->compilatio_bin_created = $eventdata['objectid'];
            }
            // Course recycle_bin created.
            if ($eventdata['objecttable'] == 'tool_recyclebin_category') {
                $sql = '
                    SELECT plagiarism_compilatio_files.id
                    FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
                    JOIN {course_modules} course_modules
                        ON plagiarism_compilatio_files.cm = course_modules.id
                    WHERE course_modules.course ='. $SESSION->compilatio_course_deleted_id;
                $files = $DB->get_records_sql($sql);
                foreach ($files as $file) {
                    $DB->set_field('plagiarism_compilatio_files', 'recyclebinid', $eventdata['objectid'], array('id' => $file->id));
                }
                unset($SESSION->compilatio_course_deleted_id);
            }
        }

        // Adding/updating a file.
        if ($hasfile) {
            $hashes = $eventdata["other"]["pathnamehashes"];

            $compifilestokeep = array();

            $fs = get_file_storage();

            if ($eventdata['objecttable'] == 'assign_submission') {
                $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                    'submission_files', $eventdata["objectid"]);

                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'assign-%'";
                $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid, $userid));
            }

            if ($eventdata['objecttable'] == 'forum_posts') {
                $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                    'attachment', $eventdata["objectid"]);

                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename NOT LIKE 'forum-%'";
                $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid));
            }

            if ($eventdata['objecttable'] == 'workshop_submissions') {
                $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                    'submission_attachment', $eventdata["objectid"]);

                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'workshop-%'";
                $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid, $userid));
            }

            foreach ($mdlsubmissionfiles as $submissionfile) {
                $file = $DB->get_record('plagiarism_compilatio_files',
                    array('cm' => $cmid, 'userid' => $userid, 'identifier' => $submissionfile->get_contenthash()));
                if ($file) {
                    array_push($compifilestokeep, $file);
                }
            }

            $duplicates = array_udiff($allcompisubmissionfiles, $compifilestokeep,
                function ($filea, $fileb) {
                    return $filea->id - $fileb->id;
                }
            );

            compilatio_delete_files($duplicates);
            self::handle_hashes($hashes, $cmid, $userid);
        }

        // Adding/updating a text content.
        if ($hascontent) {
            $objectid = $eventdata["objectid"];
            $filename = "{$cm->modname}-{$objectid}.htm";

            $content = $eventdata["other"]["content"];

            if ($eventdata['objecttable'] == 'forum_posts') {
                $identifier = sha1($DB->get_record('forum_posts', array('id' => $objectid))->message);

            } else if ($eventdata['objecttable'] == 'workshop_submissions') {
                $identifier = sha1($DB->get_record('workshop_submissions', array('id' => $eventdata["objectid"]))->content);
                    
            } else if ($eventdata['objecttable'] == 'assign_submission') {
                $params = array('submission' => $eventdata["objectid"], 'assignment' => $cmid);
                $identifier = sha1($DB->get_record('assignsubmission_onlinetext', $params))->onlinetext;
            }

            $compifile = $DB->get_record('plagiarism_compilatio_files', array('filename' => $filename, 'identifier' => $identifier));

            if (!$compifile) {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('filename' => $filename));
                compilatio_delete_files($duplicates);

                if (trim($content) != "") {
                    $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
        
                    if (str_word_count(utf8_decode(strip_tags($content))) >= $nbmotsmin) {
                        CompilatioSendFile::send_file($cmid, $userid, null, $filename, $content);
                    }
                }
            }
        }
    }

    /**
     * Function to handle Quiz attempts.
     *
     * @param int $attemptid - quiz attempt id
     */
    public static function handle_quiz_attempt($attemptid) {

        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $fs = get_file_storage();

        $attempt = \quiz_attempt::create($attemptid);
        $userid = $attempt->get_userid();
        $cmid = $attempt->get_cmid();

        foreach ($attempt->get_slots() as $slot) {
            $answer = $attempt->get_question_attempt($slot);
            if ($answer->get_question()->get_type_name() == 'essay') {
                $content = $answer->get_response_summary();

                // Check for duplicates files.
                $identifier = sha1($content);
                $duplicate = $DB->get_records('plagiarism_compilatio_files',
                    array('identifier' => $identifier, 'userid' => $userid, 'cm' => $cmid));
                compilatio_delete_files($duplicate);

                // Online text content.
                $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
                if (str_word_count(utf8_decode(strip_tags($content))) >= $nbmotsmin) {

                    $courseid = $attempt->get_courseid();
                    $question = "Q" . $answer->get_question_id();

                    $filename = "quiz-{$courseid}-{$cmid}-{$attemptid}-{$question}.htm";

                    CompilatioSendFile::send_file($cmid, $userid, null, $filename, $content);
                }

                // Files attachments.
                $context = context_module::instance($cmid);
                $files = $answer->get_last_qt_files('attachments', $context->id);
                foreach ($files as $file) {

                    // Check for duplicate files.
                    $sql = "SELECT * FROM {plagiarism_compilatio_files}
                        WHERE cm = ? AND userid = ? AND identifier = ?";
                    $duplicates = $DB->get_records_sql($sql, array($cmid, $userid, $file->get_contenthash()));
                    compilatio_delete_files($duplicates);

                    CompilatioSendFile::send_file($cmid, $userid, $file);
                }
            }
        }
    }

    /**
     * Handle hashes
     *
     * @param  array $hashes Hashes
     * @param  int   $cmid   Course module ID
     * @param  int   $userid User ID
     * @param  int   $postid Post ID
     * @return void
     */
    public static function handle_hashes($hashes, $cmid, $userid, $postid = null) {

        foreach ($hashes as $hash) {

            $fs = get_file_storage();
            $file = $fs->get_file_by_hash($hash);

            if (empty($file)) {
                mtrace("nofilefound!");
                continue;
            } else if ($file->get_filename() === '.') {
                // This 'file' is actually a directory - nothing to submit.
                continue;
            }

            CompilatioSendFile::send_file($cmid, $userid, $file);
        }
    }
}

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
 * event_handler.php - Contains methods to handle Moodle events.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

/**
 * CompilatioEventHandler class
 */
class CompilatioEventHandler {

    public static function deletion($event) {
        global $DB, $SESSION;

        $cmid = $event["contextinstanceid"];

        // Get user id.
        $userid = $event['relateduserid'];
        if ($userid == null) {
            $userid = $event['userid'];
        }

        $duplicates = [];

        // In forums.
        if ($event['objecttable'] == 'forum_posts') {
            if (!isset($SESSION->compilatio_bin_created)) {
                $sql = "SELECT  pcf.id, pcf.externalid, pcf.cm
                    FROM {plagiarism_compilatio_files} pcf
                    JOIN {plagiarism_compilatio_cm_cfg} cfg ON cfg.cmid = pcf.cm
                    WHERE pcf.cm = ? AND (filename LIKE ? OR filename LIKE ?) AND recyclebinid IS NULL";

                $filename = 'forum-' . $event['objectid'];
                $duplicates = $DB->get_records_sql($sql, [$cmid, $filename . '-%', $filename . '.htm']);
            }
        }

        // In workshops.
        if ($event['objecttable'] == 'workshop_submissions') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'userid' => $userid]);
        }

        // In quiz.
        if ($event['objecttable'] == 'quiz_attempts') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'userid' => $userid]);
            compilatio_delete_files($duplicates);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'quiz-%'";
            $duplicates = $DB->get_records_sql($sql, [$cmid, $userid]);
        }

        // User delete.
        if ($event['objecttable'] == 'user') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', ['userid' => $event['objectid']]);
        }

        compilatio_delete_files($duplicates);

        // Course module delete.
        if ($event['objecttable'] == 'course_modules') {
            if (class_exists('\tool_recyclebin\course_bin') && \tool_recyclebin\course_bin::is_enabled()) {
                $DB->set_field('plagiarism_compilatio_cm_cfg', 'recyclebinid',
                    $SESSION->compilatio_bin_created, ['cmid' => $cmid]);
                unset($SESSION->compilatio_bin_created);
            } else {
                $cmcfgs = $DB->get_records('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
                compilatio_delete_course_modules($cmcfgs);
            }
        }
    }

    public static function course_reset($event) {
        global $DB;

        $options = $event['other']['reset_options'];

        $modules = [
            'assign' => "reset_assign_submissions",
            'quiz' => "reset_quiz_attempts",
            'workshop' => "reset_workshop_submissions",
            'forum' => "reset_forum_all",
        ];

        foreach ($modules as $modulename => $option) {
            if (isset($options[$option]) && $options[$option] == 1) {

                $sql = 'SELECT pcf.id, pcf.externalid, pcf.cm
                    FROM {plagiarism_compilatio_files} pcf
                    JOIN {course_modules} course_modules ON pcf.cm = course_modules.id
                    JOIN {modules} modules ON modules.id = course_modules.module
                    WHERE course_modules.course = ? AND modules.name = ?';

                $files = $DB->get_records_sql($sql, [$event['courseid'], $modulename]);

                $keepfileindexed = boolval(get_config('plagiarism_compilatio', 'keep_docs_indexed'));
                compilatio_delete_files($files, $keepfileindexed);

                self::create_folder_if_not_set($event['courseid'], $modulename);
            }
        }
    }

    // ADTD v2 course modules management.
    private static function create_folder_if_not_set($courseid, $modulename) {
        global $DB, $USER;

        $user = $DB->get_record('plagiarism_compilatio_user', ['userid' => 0]);

        if (empty($user)) {
            return;
        }

        $compilatio = new CompilatioAPI($user->compilatioid);

        $sql = "SELECT cfg.*, {$modulename}.name FROM {plagiarism_compilatio_cm_cfg} cfg
            JOIN {course_modules} course_modules ON cfg.cmid = course_modules.id
            JOIN {modules} modules ON modules.id = course_modules.module
            JOIN {{$modulename}} {$modulename} ON course_modules.instance = {$modulename}.id AND modules.name = '{$modulename}'
            WHERE cfg.folderid IS NULL AND cfg.userid IS NULL AND course_modules.course = ? AND modules.name = ?";

        $cmconfigs = $DB->get_records_sql($sql, [$courseid, $modulename]);

        foreach ($cmconfigs as $cmconfig) {
            $cmconfig->userid = $user->compilatioid;

            $folderid = $compilatio->set_folder(
                $cmconfig->name,
                $cmconfig->defaultindexing,
                $cmconfig->analysistype,
                $cmconfig->analysistime,
                $cmconfig->warningthreshold,
                $cmconfig->criticalthreshold
            );
            if (compilatio_valid_md5($folderid)) {
                $cmconfig->folderid = $folderid;
            }

            $DB->update_record('plagiarism_compilatio_cm_cfg', $cmconfig);
        }
    }

    public static function recycle_bin($event) {
        global $DB, $SESSION;

        if ($event['crud'] == 'c') { // Recycle bin created.
            if ($event['objecttable'] == 'tool_recyclebin_course') { // Course module.
                $SESSION->compilatio_bin_created = $event['objectid'];

            } else if ($event['objecttable'] == 'tool_recyclebin_category') { // Course.
                $sql = 'SELECT module.id FROM {plagiarism_compilatio_cm_cfg} module
                        JOIN {course_modules} cm ON module.cmid = cm.id
                        WHERE cm.course ='. $SESSION->compilatio_course_deleted_id;
                $modules = $DB->get_records_sql($sql);

                foreach ($modules as $module) {
                    $DB->set_field('plagiarism_compilatio_cm_cfg', 'recyclebinid', $event['objectid'], ['id' => $module->id]);
                }

                unset($SESSION->compilatio_course_deleted_id);
            }

        } else if ($event['crud'] == 'u') { // Recycle bin restored.
            $cmcfgs = $DB->get_records('plagiarism_compilatio_cm_cfg', ['recyclebinid' => $event['objectid']]);

            foreach ($cmcfgs as $cmcfg) {
                // Update filename for restored forum posts.
                $posts = $DB->get_records_sql("SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND (filename LIKE 'forum-%.htm' OR filename LIKE 'post-%.htm')", [$cmcfg->cmid]);
                foreach ($posts as $post) {
                    $restoredpost = $DB->get_record_sql("SELECT * FROM {plagiarism_compilatio_files}
                        WHERE cm != ? AND filename = ?", [$cmcfg->cmid, $post->filename]);

                    if (preg_match('~^forum-\d+.htm$~', $post->filename)) { // Text.
                        $sql = 'SELECT id FROM {forum_posts} WHERE SHA1(message) = ?';
                        $postid = $DB->get_field_sql($sql, [$post->identifier]);

                        $restoredpost->filename = 'forum-' . $postid . '.htm';
                        $DB->update_record('plagiarism_compilatio_files', $restoredpost);
                    } else { // File.
                        $filename = explode("-", $post->filename)[2];
                        $moodlefile = $DB->get_record('files', ['filename' => $filename, 'filearea' => 'attachment']);

                        $restoredpost->filename = 'forum-' . $moodlefile->itemid . "-" . $filename;
                        $DB->update_record('plagiarism_compilatio_files', $restoredpost);
                    }
                }

                $DB->delete_records('plagiarism_compilatio_files', ['cm' => $cmcfg->cmid]);
                $DB->delete_records('plagiarism_compilatio_cm_cfg', ['id' => $cmcfg->id]);
            }

        } else if ($event['crud'] == 'd') { // Recycle bin deleted.
            $cmcfgs = $DB->get_records('plagiarism_compilatio_cm_cfg', ['recyclebinid' => $event['objectid']]);
            compilatio_delete_course_modules($cmcfgs);
        }
    }

    public static function student_analyses($event) {
        global $DB;

        $cmid = $event["contextinstanceid"];

        if (!compilatio_enabled($cmid)) {
            return;
        }

        // Get user id.
        $userid = $event['relateduserid'];
        if ($userid == null) {
            $userid = $event['userid'];
        }

        // Delete in assign.
        if ($event['target'] == 'submission_status') {
            // The event is triggered when a submission is deleted and when the submission is passed to draft.
            $fs = get_file_storage();
            $submissionfiles = $fs->get_area_files($event["contextid"], "assignsubmission_file",
                'submission_files', $event["objectid"]);

            // If the documents have been deleted in the mdl_files table, we also delete them on our side.
            if (empty($submissionfiles)) {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'userid' => $userid]);
                compilatio_delete_files($duplicates);
            }
        }

        // Re-submit file when student submit a draft submission.
        $plugincm = compilatio_cm_use($cmid);
        if ($event['target'] == 'assessable' && $plugincm->studentanalyses === '1') {

            $files = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'userid' => $userid]);
            $compilatio = new CompilatioAPI($plugincm->userid);

            foreach ($files as $file) {
                compilatio_delete_files($files);
                CompilatioSendFile::retrieve_and_send_file($file);
            }
        }
    }

    public static function submit_text($event) {
        global $DB;

        $cmid = $event["contextinstanceid"];

        if (!compilatio_enabled($cmid)) {
            return;
        }

        $userid = $event['relateduserid'];
        if ($userid == null) {
            $userid = $event['userid'];
        }

        $cm = get_coursemodule_from_id(null, $cmid);

        $objectid = $event["objectid"];
        $filename = "{$cm->modname}-{$objectid}.htm";

        $content = $event["other"]["content"];

        if ($event['objecttable'] == 'forum_posts') {
            $identifier = sha1($DB->get_record('forum_posts', ['id' => $objectid])->message);

        } else if ($event['objecttable'] == 'workshop_submissions') {
            $identifier = sha1($DB->get_record('workshop_submissions', ['id' => $objectid])->content);

        } else if ($event['objecttable'] == 'assign_submission') {
            $params = ['submission' => $objectid, 'assignment' => $cmid];
            $identifier = sha1($DB->get_record('assignsubmission_onlinetext', $params))->onlinetext;
        }

        $compifile = $DB->get_record('plagiarism_compilatio_files', ['filename' => $filename, 'identifier' => $identifier]);

        if (!$compifile) {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', ['filename' => $filename]);
            compilatio_delete_files($duplicates);

            if (trim($content) != "") {
                $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');

                if (str_word_count(mb_convert_encoding(strip_tags($content), 'ISO-8859-1', 'UTF-8')) >= $nbmotsmin) {
                    CompilatioSendFile::send_file($cmid, $userid, null, $filename, $content);
                }
            }
        }
    }

    public static function submit_file($event) {
        global $DB;

        $cmid = $event["contextinstanceid"];

        if (!compilatio_enabled($cmid)) {
            return;
        }

        $userid = $event['relateduserid'];
        if ($userid == null) {
            $userid = $event['userid'];
        }

        $cmpfilestokeep = [];

        $fs = get_file_storage();

        if ($event['objecttable'] == 'assign_submission') {
            $mdlfiles = $fs->get_area_files($event["contextid"], $event["component"], 'submission_files', $event["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'assign-%'";
            $allcmpfiles = $DB->get_records_sql($sql, [$cmid, $userid]);
        }

        if ($event['objecttable'] == 'forum_posts') {
            $mdlfiles = $fs->get_area_files($event["contextid"], $event["component"], 'attachment', $event["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename NOT LIKE 'forum-%'";
            $allcmpfiles = $DB->get_records_sql($sql, [$cmid]);

            $filename = "forum-" . $event["objectid"];
        }

        if ($event['objecttable'] == 'workshop_submissions') {
            $mdlfiles = $fs->get_area_files($event["contextid"], $event["component"], 'submission_attachment', $event["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'workshop-%'";
            $allcmpfiles = $DB->get_records_sql($sql, [$cmid, $userid]);
        }

        foreach ($mdlfiles as $file) {
            $cmpfile = $DB->get_record('plagiarism_compilatio_files',
                ['cm' => $cmid, 'userid' => $userid, 'identifier' => $file->get_contenthash()]);
            if ($cmpfile) {
                array_push($cmpfilestokeep, $cmpfile);
            }
        }

        $duplicates = array_udiff($allcmpfiles, $cmpfilestokeep,
            function ($filea, $fileb) {
                return $filea->id - $fileb->id;
            }
        );

        compilatio_delete_files($duplicates);

        foreach ($event["other"]["pathnamehashes"] as $hash) {

            $fs = get_file_storage();
            $file = $fs->get_file_by_hash($hash);

            if (empty($file)) {
                mtrace("nofilefound!");
                continue;
            } else if ($file->get_filename() === '.') {
                // This 'file' is actually a directory - nothing to submit.
                continue;
            }

            CompilatioSendFile::send_file($cmid, $userid, $file, $filename ?? null);
        }
    }

    public static function submit_quiz($event) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $fs = get_file_storage();

        $attemptid = $event['objectid'];

        $attempt = $CFG->version < 2023100900 ? \quiz_attempt::create($attemptid) : \mod_quiz\quiz_attempt::create($attemptid);
        $userid = $attempt->get_userid();
        $cmid = $attempt->get_cmid();

        if (!compilatio_enabled($cmid)) {
            return;
        }

        foreach ($attempt->get_slots() as $slot) {
            $answer = $attempt->get_question_attempt($slot);
            if ($answer->get_question()->get_type_name() == 'essay') {
                $content = $answer->get_response_summary();

                if (empty($content)) {
                    return;
                }

                // Online text content.
                $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
                if (str_word_count(mb_convert_encoding(strip_tags($content), 'ISO-8859-1', 'UTF-8')) >= $nbmotsmin) {
                    $question = "Q" . $answer->get_question_id();
                    $courseid = $DB->get_field('course_modules', 'course', array('id' => $cmid));
                    $filename = "quiz-{$courseid}-{$cmid}-{$attemptid}-{$question}.htm";

                    // Check for duplicates files.
                    $identifier = sha1($filename);
                    $duplicate = $DB->get_records('plagiarism_compilatio_files',
                        ['identifier' => $identifier, 'userid' => $userid, 'cm' => $cmid]);
                    compilatio_delete_files($duplicate);

                    CompilatioSendFile::send_file($cmid, $userid, null, $filename, $content, $identifier);
                }

                // Files attachments.
                $context = context_module::instance($cmid);
                $files = $answer->get_last_qt_files('attachments', $context->id);
                foreach ($files as $file) {

                    // Check for duplicate files.
                    $sql = "SELECT * FROM {plagiarism_compilatio_files}
                        WHERE cm = ? AND userid = ? AND identifier = ?";
                    $duplicates = $DB->get_records_sql($sql, [$cmid, $userid, $file->get_contenthash()]);
                    compilatio_delete_files($duplicates);

                    CompilatioSendFile::send_file($cmid, $userid, $file);
                }
            }
        }
    }
}

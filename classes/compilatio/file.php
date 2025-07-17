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
 * file.php - Contains methods to send files.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\compilatio\analysis;
use lib\​filestorage\stored_file;

use plagiarism_compilatio\compilatio\api;

/**
 * file class
 */
class file {

    /**
     * @var mixed $depositor
     */
    private static $depositor;

    /**
     * @var array $authors
     */
    private static $authors;

    const EMPTY_TEXT_HASH = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

    /**
     * Send file to compilatio
     *
     * @param int    $cmid       Course module identifier
     * @param int    $userid     User identifier
     * @param object $file       File to send to Compilatio
     * @param string $filename   Filename for text content
     * @param string $content    Text content
     * @param string $identifier File identifier
     */
    public static function send_file($cmid, $userid, $file = null, $filename = null, $content = null, $identifier = null) {

        global $DB, $CFG;

        $send = true;

        $cmpfile = new \stdClass();
        $identifier = new identifier($userid, $cmid);

        $cmpfile->cm = $cmid;
        $cmpfile->userid = $userid;
        $cmpfile->timesubmitted = time();

        if ($file) {
            $cmpfile->identifier = $identifier->create_from_file($file);
        } else {
            $cmpfile->identifier = $identifier->create_from_string($content);
        }

        $plugincm = compilatio_cm_use($cmid);
        $cmpfile->indexed = $plugincm->defaultindexing ?? true;

        if (compilatio_student_analysis($plugincm->studentanalyses, $cmid, $userid)) {
            $cmpfile->indexed = false;
        }

        if (!$file) { // Online text.
            $submission = self::get_submission($cmid, $userid, $content, $filename);
            if (null === $filename) {
                $cmpfile->filename = 'assign-' . $submission->id . '.htm';
            } else {
                $cmpfile->filename = $filename;
            }
        } else { // File.
            if (null === $filename) {
                $cmpfile->filename = $file->get_filename();
            } else {
                $cmpfile->filename = $filename . "-" . $file->get_filename(); // Forum.
            }

            if (!self::supported_file_type($cmpfile->filename)) {
                $cmpfile->status = "error_unsupported";
                $send = false;
            }

            if ((int) $file->get_filesize() > get_config('plagiarism_compilatio', 'max_size')) {
                $cmpfile->status = "error_too_large";
                $send = false;
            }
            $submission = self::get_submission($cmid, $userid, $file, $filename);
        }

        $groupid = null;

        if (isset($submission->groupid) && $submission->groupid !== '0') {
            $groupid = $submission->groupid;
            $cmpfile->userid = $userid = 0;
        }

        $cmpfile->groupid = $groupid;

        // Check if file has already been sent.
        $compilatiofile = new file();

         if (!empty($compilatiofile->compilatio_get_document_with_failover(
            $cmid,
            $file ?? $content,
            $userid,
            null,
            ['groupid' => $groupid])
            )
         ) {
            return false;
        }

        $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');

        if ($content && str_word_count(mb_convert_encoding(strip_tags($content), 'ISO-8859-1', 'UTF-8')) < $nbmotsmin) {
            $cmpfile->status = 'error_too_short';
            $cmpfile->id = $DB->insert_record('plagiarism_compilatio_files', $cmpfile);
            return $cmpfile;
        }

        if ($send) {
            if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
                debugging("Error when sending the file to compilatio : failed to create compilatio temp directory");
            }

            $filepath = $CFG->dataroot . "/temp/compilatio/" . __FUNCTION__ . '_' . sha1(uniqid('', true)) . ".txt";

            if ($file) {
                $file->copy_content_to($filepath);
            } else {
                $handle = fopen($filepath, "wb");
                fwrite($handle, $content);
                fclose($handle);
            }
            
            $cmconfig = $DB->get_record("plagiarism_compilatio_cm_cfg", ["cmid" => $cmid]);

            $compilatio = new api($cmconfig->userid);

            self::set_depositor_and_authors($userid, $cmid);

            $docid = $compilatio->set_document(
                $cmpfile->filename,
                $cmconfig->folderid,
                $filepath,
                $cmpfile->indexed,
                self::$depositor,
                self::$authors
            );

            unlink($filepath);

            if (compilatio_valid_md5($docid)) {
                $cmpfile->externalid = $docid;
                $cmpfile->status = 'sent';

                if ($cmconfig->analysistype == 'auto') {
                    $cmpfile->status = 'queue';

                    // Plugin v2 docs management.
                    if (null === $cmconfig->folderid) {
                        $cmpfile->status = 'to_analyze';
                    }
                }

                $cmpfile->id = $DB->insert_record('plagiarism_compilatio_files', $cmpfile);
                return $cmpfile;
            } else {
                $cmpfile->status = 'error_sending_failed';
            }
        }

        $DB->insert_record('plagiarism_compilatio_files', $cmpfile);
        return false;
    }

    /**
     * Send files and start analyse for unsent files
     *
     * @param array  $files Array of file records
     * @param string $cmid  cmid of the assignment
     */
    public static function send_unsent_files($files, $cmid) {

        global $DB;

        foreach ($files as $file) {
            $userid = $DB->get_field('assign_submission', 'userid', [
                    'id' => isset($file->onlinetext) ?
                        $file->submission :
                        $file->get_itemid()]
                    );
            if ($file instanceof \stored_file) {
                self::send_file($cmid, $userid, $file);
            } else {
                self::send_file($cmid, $userid, null, null, $file->onlinetext);
            }
        }
    }

    /**
     * Get file or text content and send it to Compilatio
     *
     * @param  mixed   $cmpfile       Compilatio file record
     * @param  boolean $startanalysis Start analysis directly after uploading
     * @return boolean Result of sending file or text content to Compilatio
     */
    public static function retrieve_and_send_file($cmpfile, $startanalysis = false) {

        global $DB;

        $newcmpfile = null;

        if (preg_match('~.htm$~', $cmpfile->filename)) { // Text content.
            $objectid = explode(".", explode("-", $cmpfile->filename)[1])[0];

            $sql = "SELECT m.name FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                WHERE cm.id = ?";
            $modulename = $DB->get_field_sql($sql, [$cmpfile->cm]);

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
                    $attemptid = explode("-", $cmpfile->filename)[3];

                    $sql = "SELECT responsesummary
                        FROM {quiz_attempts} quiz
                        JOIN {question_attempts} qa ON quiz.uniqueid = qa.questionusageid
                        WHERE quiz.id = ? AND qa.questionid = ?";
                    $content = $DB->get_field_sql($sql, [$attemptid, $questionid]);
                    break;
            }

            if (!empty($content)) {
                $DB->delete_records('plagiarism_compilatio_files', ['id' => $cmpfile->id]);

                $newcmpfile = self::send_file(
                    $cmpfile->cm,
                    $cmpfile->userid,
                    null,
                    $cmpfile->filename,
                    $content,
                );
            }
        } else { // File.
            $modulecontext = \context_module::instance($cmpfile->cm);
            $contextid = $modulecontext->id;
            $fs = get_file_storage();

            // Search by identifier.
            $allfiles = $DB->get_records_sql("SELECT * FROM {files}
                where contextid = ?
                    AND component = 'assignsubmission_file'
                    AND contenthash != '" . self::EMPTY_TEXT_HASH . "'",
                ['contextid' => $contextid]);
            $matchedfiles = [];

            foreach ($allfiles as $file) {
                $storedfile = $fs->get_file_by_id($file->id);
                if (!$storedfile) {
                    continue;
                }

                $identifier = new identifier($cmpfile->userid, $cmpfile->cm);

                $identifiers = [
                    $identifier->create_from_file($storedfile),
                    $storedfile->get_contenthash(),
                ];

                if (in_array($cmpfile->identifier, $identifiers)) {
                    $matchedfiles[] = $file;
                }
            }

            // Search by filename and userid.
            if (empty($matchedfiles)) {
                $sql = "SELECT f.* FROM {files} f
                        JOIN {assign_submission} sub ON f.itemid = sub.id
                        WHERE f.contextid = ?
                        AND f.component = 'assignsubmission_file'
                        AND f.filename = ?
                        AND (sub.userid = ? OR sub.groupid IN (
                            SELECT groupid FROM {groups_members} WHERE userid = ?
                        ))
                        AND f.contenthash != '" . self::EMPTY_TEXT_HASH . "'";

                $matchedfiles = $DB->get_records_sql($sql, [
                    $contextid,
                    $cmpfile->filename,
                    $cmpfile->userid,
                    $cmpfile->userid,
                ]);
            }

            // Search by filename.
            if (empty($matchedfiles)) {
                $sql = "SELECT * FROM {files}
                        WHERE contextid = ?
                        AND component = 'assignsubmission_file'
                        AND filename = ?
                        AND contenthash != '" . self::EMPTY_TEXT_HASH . "'";

                $matchedfiles = $DB->get_records_sql($sql, [$contextid, $cmpfile->filename]);
            }

            if (!empty($matchedfiles)) {
                $files = $matchedfiles;
            } else {
                return false;
            }

            foreach ($files as $f) {
                $file = $fs->get_file_by_id($f->id);

                $DB->delete_records('plagiarism_compilatio_files', ['id' => $cmpfile->id]);

                $newcmpfile = self::send_file($cmpfile->cm, $cmpfile->userid, $file);

                if (is_object($newcmpfile) && $startanalysis) {
                    $newcmpfile->status = 'to_analyze';
                    $DB->update_record('plagiarism_compilatio_files', $newcmpfile);
                }

                return is_object($newcmpfile);
            }
        }
    }

    /**
     * Check if file type is allowed.
     *
     * @param  string  $filename Filename of the document
     * @return boolean  Return true type if file type is supported, false otherwise
     */
    public static function supported_file_type($filename) {

        $pathinfo = pathinfo($filename);

        if (empty($pathinfo['extension'])) {
            return false;
        }
        $extension = strtolower($pathinfo['extension']);
        return in_array($extension, self::supported_extensions());
    }

    /**
     * Get supported extensions (excluding zip)
     *
     * @return array Supported extensions
     */
    public static function supported_extensions() {
        $filetypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));
        return array_keys((array) $filetypes);
    }

    /**
     * Setter for authors and depositor
     *
     * @param  int $userid  User ID
     * @param  int $cmid    Course module ID
     * @return void
     */
    private static function set_depositor_and_authors($userid, $cmid) {
        global $DB;

        $depositor = $DB->get_record("user", ["id" => $userid], 'firstname, lastname, email');

        if (empty($depositor)) {
            $depositor = (object) [
                'firstname' => 'not_found',
                'lastname' => 'not_found',
                'email' => null,
            ];
        }

        $authors = [$depositor];

        $module = get_coursemodule_from_id(null, $cmid);

        if ($module->modname == 'assign') {
            $isgroupsubmission = $DB->get_field_sql(
                'SELECT teamsubmission FROM {course_modules} course_modules
                    JOIN {assign} assign ON course_modules.instance = assign.id
                    WHERE course_modules.id = ?',
                ['id' => $cmid]
            );

            if ($isgroupsubmission === '1') {
                $groupid = $DB->get_fieldset_sql(
                    'SELECT groupid FROM {groups_members} gm
                        JOIN {groups} g ON g.id = gm.groupid
                        WHERE courseid = ? AND userid = ?',
                    [$module->course, $userid]
                );

                if (count($groupid) == 1) {
                    $authors = $DB->get_records_sql(
                        'SELECT firstname, lastname, email FROM {groups} g
                            JOIN {groups_members} gm ON g.id = gm.groupid
                            JOIN {user} u ON u.id = gm.userid
                            WHERE courseid = ? AND g.id = ?',
                        [$module->course, $groupid[0]]
                    );
                }
            }
        }

        self::$authors = $authors;
        self::$depositor = $depositor;
    }

    /**
     * Get document record(s) with identifier failover.
     * First tries with new identifier format (sha1(content+userid))
     * If not found, falls back to old format (sha1(content))
     *
     * @param int $cmid Course module ID
     * @param string $content Content to hash for identifier
     * @param int $userid User ID
     * @param string $status Document status (optional)
     * @param array $additionalparams Additional parameters for the query (optional)
     * @param bool $multiple Whether to return multiple records (true) or a single record (false)
     * @return mixed Single document object, array of document objects, or false/empty array if not found
     */
    public function compilatio_get_document_with_failover(
            $cmid,
            $content,
            $userid = null,
            $status = null,
            $additionalparams = [],
            $multiple = false
        ) {
        global $DB;
        $params = ['cm' => $cmid];

        if ($status !== null) {
            $params['status'] = $status;
        }

        if (isset($additionalparams['groupid'])) {
            $params['groupid'] = $additionalparams['groupid'];
            $params['userid'] = 0;
        } else {
            $params['userid'] = $userid;
        }

        $identifier = new identifier($userid, $cmid);

        if ($content instanceof \stored_file) {
            $params['identifier'] = $identifier->create_from_file($content);
        } else {
            $params['identifier'] = $identifier->create_from_string($content);
        }

        if (!empty($additionalparams)) {
            $filteredparams = array_diff_key($additionalparams, ['groupid' => '']);
            $params = array_merge($params, $filteredparams);
        }

        if ($multiple) {
            $documents = $DB->get_records('plagiarism_compilatio_files', $params);
            if (empty($documents)) {
                $params['identifier'] = $content instanceof \stored_file ? $content->get_contenthash() : sha1($content);
                $documents = $DB->get_records('plagiarism_compilatio_files', $params);
            }

            return $documents;
        } else {
            $document = $DB->get_record('plagiarism_compilatio_files', $params);

            if (!$document) {
                
                $params['identifier'] = $content instanceof \stored_file ? $content->get_contenthash() : sha1($content);
                $document = $DB->get_record('plagiarism_compilatio_files', $params);
            }

            return $document;
        }
    }

    /**
     * Get submission record based on various criteria
     *
     * @param int $cmid Course module ID
     * @param int $userid User ID
     * @param object $content Storedfile or onlinetext object
     * @param string $filename Filename
     * @return object|null Submission record or null if not found
     */
    private static function get_submission($cmid, $userid, $content, $filename) {
        global $DB;

        $cm = get_coursemodule_from_id('assign', $cmid);
        if (!$cm) {
            return null;
        }

        $assignment = $DB->get_record('assign', ['id' => $cm->instance]);
        if (!$assignment) {
            return null;
        }

        $submission = null;
        $onlinetext = true;

        // Search by id.
        if ($content instanceof \stored_file && !empty($content->get_id())) {
            $onlinetext = false;
            $filerecord = $DB->get_record('files', ['id' => $content->get_id()]);
            if ($filerecord) {
                $submission = $DB->get_record('assign_submission', ['id' => $filerecord->itemid]);
            }
        }

        // Search for onlinetext.
        if (!$submission && $onlinetext) {
            // Search by SHA1.

            /**
             * @var string $content
             */
            $contentidentifier = sha1($content);

            $sql = "SELECT ass.*, assot.onlinetext
                    FROM {assign_submission} ass
                    JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                    WHERE ass.assignment = ?";

            $submissions = $DB->get_records_sql($sql, [$assignment->id]);

            foreach ($submissions as $sub) {
                $subidentifier = sha1($sub->onlinetext);
                if ($subidentifier === $contentidentifier) {
                    $submission = $sub;
                    break;
                }
            }
        }

        // Search by content.
        if (!$submission) {
            // Extract submission ID from filename.
            if (!empty($filename) && preg_match('/^assign-(\d+)\.htm$/', $filename, $matches)) {
                $submissionid = $matches[1];
                $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
            }

            if (!$submission) {
                // Individual submission.
                $submission = $DB->get_record_sql(
                    "SELECT ass.*
                     FROM {assign_submission} ass
                     JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                     WHERE ass.assignment = ? AND ass.userid = ?",
                    [$assignment->id, $userid]
                );

                // Group submission where user is member.
                if (!$submission) {
                    $submission = $DB->get_record_sql(
                        "SELECT ass.*
                         FROM {assign_submission} ass
                         JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                         JOIN {groups_members} gm ON gm.groupid = ass.groupid
                         WHERE ass.assignment = ? AND gm.userid = ? AND ass.groupid != 0",
                        [$assignment->id, $userid]
                    );
                }

                // Search by content hash.
                if (!$submission) {
                    $contentidentifier = $content ? sha1($content) : $content->get_contenthash();

                    $sql = "SELECT ass.id, ass.groupid, ass.userid, assot.onlinetext
                            FROM {assign_submission} ass
                            JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                            WHERE ass.assignment = ?";

                    $submissions = $DB->get_records_sql($sql, [$assignment->id]);

                    foreach ($submissions as $sub) {
                        $subidentifier = sha1($sub->onlinetext);

                        if ($subidentifier === $contentidentifier) {
                            $submission = $sub;
                            break;
                        }
                    }
                }
            }
        }

        return $submission;
    }
}

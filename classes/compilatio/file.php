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
        $cmpfile->cm = $cmid;
        $cmpfile->userid = $userid;
        $cmpfile->timesubmitted = time();

        $plugincm = compilatio_cm_use($cmid);
        $cmpfile->indexed = $plugincm->defaultindexing ?? true;

        if (compilatio_student_analysis($plugincm->studentanalyses, $cmid, $userid)) {
            $cmpfile->indexed = false;
        }

        if (null === $file) {
            $cmpfile->filename = $filename;
            $cmpfile->identifier = $identifier ?? sha1($content . $userid . $cmid);

        } else {
            if (isset($file->onlinetext)) { // Online text.
                $content = $file->onlinetext;
                $cmpfile->filename = 'assign-' . $file->submission . '.htm';
                $cmpfile->identifier = sha1($file->onlinetext . $userid . $cmid);

            } else { // File.
                $content = $file->get_content();
                if (null === $filename) {
                    $cmpfile->filename = $file->get_filename();
                } else {
                    $cmpfile->filename = $filename . "-" . $file->get_filename(); // Forum.
                }
                $cmpfile->identifier = sha1($file->get_content() . $userid . $cmid);

                if (!self::supported_file_type($cmpfile->filename)) {
                    $cmpfile->status = "error_unsupported";
                    $send = false;
                }

                if ((int) $file->get_filesize() > get_config('plagiarism_compilatio', 'max_size')) {
                    $cmpfile->status = "error_too_large";
                    $send = false;
                }
            }
        }

        // Check if file has already been sent.
        $compilatiofile = new file();
        if (!empty($compilatiofile->compilatio_get_document_with_failover($cmid, $cmpfile->identifier, $userid))) {
            return false;
        }

        if ($send) {
            if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
                debugging("Error when sending the file to compilatio : failed to create compilatio temp directory");
            }

            $filepath = $CFG->dataroot . "/temp/compilatio/" . __FUNCTION__ . '_' . sha1(uniqid('', true)) . ".txt";
            $handle = fopen($filepath, "wb");
            fwrite($handle, $content);
            fclose($handle);

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

            self::send_file($cmid, $userid, $file);
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

                    $identifier = sha1($cmpfile->filename . $cmpfile->userid . $cmpfile->cm);

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
                    $identifier ?? null
                );
            }
        } else { // File.

            $module = get_coursemodule_from_id(null, $cmpfile->cm);

            $modulecontext = \context_module::instance($cmpfile->cm);
            $contextid = $modulecontext->id;

            $files = $DB->get_records_sql('SELECT * FROM {files} f WHERE f.contenthash = ? AND contextid = ?',
                [$cmpfile->identifier, $contextid]);

            if (empty($files)) {
                $allfiles = $DB->get_records('files', ['contextid' => $contextid]);
                $matchedfiles = [];

                foreach ($allfiles as $file) {
                    $tmpidentifier = sha1($file->contenthash . $cmpfile->userid);
                    if ($tmpidentifier === $cmpfile->identifier || $file->contenthash === $cmpfile->identifier) {
                        $matchedfiles[] = $file;
                    }
                }

                if (!empty($matchedfiles)) {
                    $files = $matchedfiles;
                }
            }

            if (empty($files)) {
                return false;
            }

            $fs = get_file_storage();

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
        $extensions = array_keys((array) $filetypes);
        return array_diff($extensions, ['zip']); ;
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

        if (!empty($additionalparams)) {
            $params = array_merge($params, $additionalparams);
        }

        $params['identifier'] = sha1($content . $userid . $cmid ?? '');

        if ($multiple) {
            $documents = $DB->get_records('plagiarism_compilatio_files', $params);

            if (empty($documents)) {
                $params['identifier'] = sha1($content);
                $documents = $DB->get_records('plagiarism_compilatio_files', $params);
            }

            return $documents;
        } else {
            $document = $DB->get_record('plagiarism_compilatio_files', $params);

            if (!$document) {
                $params['identifier'] = sha1($content);
                $document = $DB->get_record('plagiarism_compilatio_files', $params);
            }

            return $document;
        }
    }
}

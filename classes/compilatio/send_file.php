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
 * send_file.php - Contains methods to send files.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompilatioSendFile class
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

class CompilatioSendFile {
    /**
     * Send file to compilatio
     *
     * @param int    $cmid      Course module identifier
     * @param int    $userid    User identifier
     * @param object $file      File to send to Compilatio
     * @param object $filename  Filename for text content
     * @param object $content   Text content
     */
    public static function send_file($cmid, $userid, $file = null, $filename = null, $content = null) {

        global $DB, $CFG;

        $send = true;

        $cmpfile = new stdClass();
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
            $cmpfile->identifier = sha1($content);

        } else {
            $content = $file->get_content();
            if (null === $filename) {
                $cmpfile->filename = $file->get_filename();
            } else {
                $cmpfile->filename = $filename . "-" . $file->get_filename(); // Forum.
            }
            $cmpfile->identifier = $file->get_contenthash();

            if (!self::supported_file_type($cmpfile->filename)) {
                $cmpfile->status = "error_unsupported";
                $send = false;
            }

            if ((int) $file->get_filesize() > get_config('plagiarism_compilatio', 'max_size')) {
                $cmpfile->status = "error_too_large";
                $send = false;
            }
        }

        // Check if file has already been sent.
        $params = [
            "cm" => $cmid,
            "userid" => $userid,
            "identifier" => $cmpfile->identifier
        ];
        if (!empty($DB->get_record("plagiarism_compilatio_files", $params))) {
            return false;
        }

        if ($send) {
            if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
                debugging("Error when sending the file to compilatio : failed to create compilatio temp directory");
            }

            $filepath = $CFG->dataroot . "/temp/compilatio/" . date('Y-m-d H-i-s') . ".txt";
            $handle = fopen($filepath, "wb");
            fwrite($handle, $content);
            fclose($handle);

            $cmconfig = $DB->get_record("plagiarism_compilatio_module", ["cmid" => $cmid]);

            $compilatio = new CompilatioAPI(get_config('plagiarism_compilatio', 'apikey'), $cmconfig->userid);

            $depositor = $DB->get_record("user", ["id" => $userid], 'firstname, lastname, email');
            $authors = [$depositor];

            if ($module->modname == 'assign') {
                $isgroupsubmission = $DB->get_field_sql(
                    'SELECT teamsubmission FROM {course_modules} course_modules
                        JOIN {assign} assign ON course_modules.instance = assign.id
                        WHERE course_modules.id = ?',
                    ['id' => $plagiarismfile->cm]
                );

                if ($isgroupsubmission === '1') {
                    $groupid = $DB->get_fieldset_sql(
                        'SELECT groupid FROM {groups_members} gm
                            JOIN {groups} g ON g.id = gm.groupid
                            WHERE courseid = ? AND userid = ?',
                        [$module->course, $plagiarismfile->userid]
                    );
        
                    if (count($groupid) == 1) {
                        $authors = $DB->get_records_sql(
                            'SELECT firstname, lastname, email FROM {groups} g
                                JOIN {groups_members} gm ON g.id = gm.groupid
                                JOIN {user} user ON user.id = gm.userid
                                WHERE courseid = ? AND g.id = ?',
                            [$module->course, $groupid[0]]
                        );
                    }
                }
            }

            $docid = $compilatio->set_document($cmpfile->filename, $cmconfig->folderid, $filepath, $cmpfile->indexed, $depositor, $authors);

            unlink($filepath);

            if (compilatio_valid_md5($docid)) {
                $cmpfile->externalid = $docid;
                $cmpfile->status = "sent";

                if ($cmconfig->analysistype == "auto") {
                    $cmpfile->status = "queue";
                }

                $DB->insert_record('plagiarism_compilatio_files', $cmpfile);
                return $cmpfile;
            } else {
                $cmpfile->status = "error_sending_failed";
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

        // TODO delete coms
        //$compilatio = new plagiarism_plugin_compilatio();

        /*$analysistype = $DB->get_field('plagiarism_compilatio_module', 'analysistype', ['cmid' => $cmid]);
        $analysistime = $DB->get_field('plagiarism_compilatio_module', 'analysistime', ['cmid' => $cmid]);*/

        foreach ($files as $file) {
            $userid = $DB->get_field('assign_submission', 'userid', ['id' => $file->get_itemid()]);

            $file = self::send_file($cmid, $userid, $file);

            /*if ($analysistype == 'manual' || ($analysistype == 'planned' && time() >= $analysistime)) {
                // TODO doc isn't extrated yet.
                CompilatioAnalyses::start_analysis($file);
            }*/
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

        $filetypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));

        foreach ($filetypes as $type => $value) {
            if ($extension == $type) {
                return true;
            }
        }
        return false;
    }
}

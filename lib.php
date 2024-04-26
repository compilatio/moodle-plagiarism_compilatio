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
 * lib.php - Contains inherited plagiarism class and specific functions and callbacks called by Moodle.
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/frame.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/csv.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/documentFrame.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/settings.php');

/**
 * Compilatio Class
 */
class plagiarism_plugin_compilatio extends plagiarism_plugin {
    /**
     * This function should be used to initialize settings and check if plagiarism is enabled.
     *
     * @return mixed - false if not enabled, or returns an array of relevant settings.
     */
    public function get_settings() {
        static $plagiarismsettings;

        if (!empty($plagiarismsettings) || $plagiarismsettings === false) {
            return $plagiarismsettings;
        }

        $plagiarismsettings = (array) get_config('plagiarism_compilatio');
        // Check if compilatio enabled.
        if (isset($plagiarismsettings['enabled']) && $plagiarismsettings['enabled']) {
            // Now check to make sure required settings are set!.
            if (empty($plagiarismsettings['apikey'])) {
                throw new moodle_exception('Compilatio API Configuration is not set!');
            }
            return $plagiarismsettings;
        } else {
            return false;
        }
    }

    /**
     * Returns an array of all the module instance settings.
     *
     * @return array
     */
    public function config_options() {
        return [
            'activated',
            'showstudentscore',
            'showstudentreport',
            'reporttype',
            'studentanalyses',
            'analysistype',
            'analysistime',
            'warningthreshold',
            'criticalthreshold',
            'defaultindexing',
        ];
    }

    /**
     * Hook to allow plagiarism specific information to be displayed beside a submission.
     *
     * @param  array   $linkarray contains all relevant information for the plugin to generate a link.
     * @return string  HTML or blank.
     */
    public function get_links($linkarray) {
        return CompilatioDocumentFrame::get_document_frame($linkarray);
    }

    /**
     * Hook to allow a disclosure to be printed notifying users what will happen with their submission
     *
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $OUTPUT;

        $outputhtml = '';

        $compilatiouse = compilatio_cm_use($cmid);
        $plagiarismsettings = $this->get_settings();
        if (!empty($plagiarismsettings['student_disclosure']) && !empty($compilatiouse)) {
            $outputhtml .= $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
            $formatoptions = new stdClass();
            $formatoptions->noclean = true;
            $outputhtml .= format_text($plagiarismsettings['student_disclosure'], FORMAT_MOODLE, $formatoptions);
            $outputhtml .= $OUTPUT->box_end();
        }
        return $outputhtml;
    }
}

/**
 * Hook to save plagiarism specific settings on a module settings page
 *
 * @param stdClass $data
 * @param stdClass $course
 */
function plagiarism_compilatio_coursemodule_edit_post_actions($data, $course) {
    return CompilatioSettings::save_course_module_settings($data, $course);
}

/**
 * Hook to add plagiarism specific settings to a module settings page
 *
 * @param moodleform $formwrapper
 * @param MoodleQuickForm $mform
 */
function plagiarism_compilatio_coursemodule_standard_elements($formwrapper, $mform) {
    CompilatioSettings::display_course_module_settings($formwrapper, $mform);
}

/**
 * Get the plagiarism values for this course module
 *
 * @param  int          $cmid           Course module (cm) ID
 * @return array|false  $plag_values    Plagiarism values or false if the plugin is not enabled for this cm
 */
function compilatio_cm_use($cmid) {
    global $DB;

    $cm = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

    if (!empty($cm->activated)) {
        return $cm;
    } else {
        return false;
    }
}

/**
 * Get the submissions unknown from Compilatio table plagiarism_compilatio_files
 *
 * @param string $cmid cmid of the assignment
 */
function compilatio_get_unsent_documents($cmid) {
    global $DB;

    $notuploadedfiles = [];
    $fs = get_file_storage();

    $sql = 'SELECT ass.id as itemid, con.id as contextid
            FROM {course_modules} cm
                JOIN {context} con ON cm.id = con.instanceid
                JOIN {assignsubmission_file} assf ON assf.assignment = cm.instance
                JOIN {assign_submission} ass ON assf.submission = ass.id
                JOIN {user_enrolments} ue ON ass.userid = ue.userid
                JOIN {enrol} enr ON ue.enrolid = enr.id
            WHERE cm.id=? AND con.contextlevel = 70 AND assf.numfiles > 0 AND enr.courseid = cm.course';

    $filesids = $DB->get_records_sql($sql, [$cmid]);

    foreach ($filesids as $fileid) {
        $files = $fs->get_area_files($fileid->contextid, 'assignsubmission_file', 'submission_files', $fileid->itemid);

        foreach ($files as $file) {
            if ($file->get_filename() != '.') {
                $countfiles = $DB->count_records(
                    'plagiarism_compilatio_files',
                    ['identifier' => $file->get_contenthash(), 'cm' => $cmid]
                );

                if ($countfiles == 0) {
                    array_push($notuploadedfiles, $file);
                }
            }
        }
    }

    return $notuploadedfiles;
}

/**
 * Check if Compilatio is enabled in moodle in this module type in this course module
 *
 * @param  int      $cmid Course module ID
 * @return boolean  Return true if enabled, false otherwise
 */
function compilatio_enabled($cmid) {
    global $DB;
    $cm = get_coursemodule_from_id(null, $cmid);
    // Get plugin activation info.
    $pluginenabled = $DB->get_field('config_plugins', 'value', ['plugin' => 'plagiarism_compilatio', 'name' => 'enabled']);

    // Get module type activation info.
    $modtypeenabled = $DB->get_field(
        'config_plugins',
        'value',
        ['plugin' => 'plagiarism_compilatio', 'name' => 'enable_mod_' . $cm->modname]
    );

    // Get course module activation info.
    $cmenabled = $DB->get_field('plagiarism_compilatio_cm_cfg', 'activated', ['cmid' => $cmid]);

    // Check if the module associated with this event still exists.
    $cmexists = $DB->record_exists('course_modules', ['id' => $cmid]);

    if ($pluginenabled && $modtypeenabled && $cmenabled && $cmexists) {
        return true;
    } else {
        return false;
    }
}

/**
 * Hook called before deletion of a course.
 *
 * @param \stdClass $course The course record.
 */
function plagiarism_compilatio_pre_course_delete($course) {
    global $SESSION, $DB;

    if (class_exists('\tool_recyclebin\course_bin') && \tool_recyclebin\category_bin::is_enabled()) {
        $SESSION->compilatio_course_deleted_id = $course->id;
    } else {
        $sql = 'SELECT module.id, module.cmid, module.userid, module.folderid
                FROM {plagiarism_compilatio_cm_cfg} module
                JOIN {course_modules} course_modules ON module.cmid = course_modules.id
                WHERE course_modules.course = ?';

        compilatio_delete_course_modules($DB->get_records_sql($sql, [$course->id]));
    }
}

/**
 * compilatio_delete_course_modules
 *
 * Deindex and remove documents and folder in Compilatio
 * Remove files and cm config in moodle tables
 *
 * @param array    $cmconfigs
 */
function compilatio_delete_course_modules($cmconfigs) {
    if (is_array($cmconfigs)) {
        global $DB;
        $compilatio = new CompilatioAPI();

        foreach ($cmconfigs as $cmconfig) {
            $files = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmconfig->cmid]);

            $keepfileindexed = boolval(get_config('plagiarism_compilatio', 'keep_docs_indexed'));
            compilatio_delete_files($files, $keepfileindexed);

            $compilatio->set_user_id($cmconfig->userid);
            $compilatio->delete_folder($cmconfig->folderid);
            $DB->delete_records('plagiarism_compilatio_cm_cfg', ['id' => $cmconfig->id]);
        }
    }
}

/**
 * compilatio_delete_files
 *
 * Deindex and remove document(s) in Compilatio
 * Remove entry(ies) in plagiarism_compilatio_files table
 *
 * @param array    $files
 * @param bool     $keepfilesindexed
 */
function compilatio_delete_files($files, $keepfilesindexed = false) {
    if (is_array($files)) {
        global $DB;
        $compilatio = new CompilatioAPI();

        foreach ($files as $doc) {
            if (is_null($doc->externalid)) {
                $DB->delete_records('plagiarism_compilatio_files', ['id' => $doc->id]);
            } else {
                $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $doc->cm]);
                $compilatio->set_user_id($userid);

                if ($keepfilesindexed || $compilatio->set_indexing_state($doc->externalid, 0)) {
                    $compilatio->delete_document($doc->externalid);
                    $DB->delete_records('plagiarism_compilatio_files', ['id' => $doc->id]);
                } else {
                    mtrace('Error deindexing document ' . $doc->externalid);
                }
            }
        }
    }
}

/**
 * Check if a submission can be analyzed by student.
 *
 * @param  int  $studentanalysesparam Value of the parameter studentanalyses for the cm
 * @param  int  $cmid
 * @param  int  $userid
 * @return bool  Return true if it's a student analyse, false otherwise
 */
function compilatio_student_analysis($studentanalysesparam, $cmid, $userid) {
    global $DB;
    if (get_config('plagiarism_compilatio', 'enable_student_analyses') === '1' && $studentanalysesparam === '1') {
        $sql = 'SELECT sub.status
            FROM {course_modules} cm
            JOIN {assign_submission} sub ON cm.instance = sub.assignment
            WHERE cm.id = ? AND userid = ?';

        $status = $DB->get_field_sql($sql, [$cmid, $userid]);

        if ($status == 'draft' || $status == 'new') {
            return true;
        }
    }
    return false;
}

/**
 * Function to check for valid response from Compilatio
 *
 * @param  string $hash Hash
 * @return bool         Return true if succeed, false otherwise
 */
function compilatio_valid_md5($hash) {
    if (preg_match('/^[a-f0-9]{40}$/', $hash)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function to format date
 *
 * @param  string $date Date
 * @return string Return formated date
 */
function compilatio_format_date($date) {
    $lang = substr(current_language(), 0, 2);

    $fmt = new IntlDateFormatter(
        $lang,
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
    );

    return $fmt->format(strtotime($date));
}

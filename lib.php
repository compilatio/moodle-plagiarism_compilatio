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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2012 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/frame.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/csv.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/documentFrame.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');

/**
 * Compilatio Class
 * @copyright  2012 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
                throw new moodle_exception("Compilatio API Configuration is not set!");
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

        return array(
            'activated',
            'showstudentscore',
            'showstudentreport',
            'studentanalyses',
            'studentemail',
            'analysistype',
            'analysistime',
            'warningthreshold',
            'criticalthreshold',
            'defaultindexing',
        );
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
            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $outputhtml .= format_text($plagiarismsettings['student_disclosure'], FORMAT_MOODLE, $formatoptions);
            $outputhtml .= $OUTPUT->box_end();
        }
        return $outputhtml;
    }

    /**
     * Send a mail to the student
     *
     * @param  array $plagiarismfile File
     * @return mixed                 Return void if succeed, false otherwise.
     */
    public function compilatio_send_student_email($plagiarismfile) {

        global $DB, $CFG;

        if (empty($plagiarismfile->userid)) { // Sanity check.
            return false;
        }

        $user = $DB->get_record('user', array('id' => $plagiarismfile->userid));
        $site = get_site();
        $a = new stdClass();
        $cm = get_coursemodule_from_id('', $plagiarismfile->cm);
        $a->modulename = format_string($cm->name);
        $a->modulelink = $CFG->wwwroot . '/mod/' . $cm->modname . '/view.php?id=' . $cm->id;
        $a->coursename = format_string($DB->get_field('course', 'fullname', array('id' => $cm->course)));
        $emailsubject = get_string('studentemailsubject', 'plagiarism_compilatio');
        $emailcontent = get_string('studentemailcontent', 'plagiarism_compilatio', $a);
        email_to_user($user, $site->shortname, $emailsubject, $emailcontent);
    }

}

/**
 * Output callback to insert a chunk of html at the start of the html document.
 * This allow us to display the Compilatio frame with statistics, alerts,
 * author search tool and buttons to launch all analyses and update submitted files status.
 *
 * @return string
 */
function plagiarism_compilatio_before_standard_top_of_body_html() {
    return CompilatioFrame::get_frame();
}

/**
 * Hook to save plagiarism specific settings on a module settings page
 *
 * @param stdClass $data
 * @param stdClass $course
 */
function plagiarism_compilatio_coursemodule_edit_post_actions($data, $course) {

    global $DB, $USER;
    $plugin = new plagiarism_plugin_compilatio();
    if (!$plugin->get_settings()) {
        return $data;
    }

    if (isset($data->activated)) {
        // First get existing values.
        $cmconfig = $DB->get_record('plagiarism_compilatio_module', array('cmid' => $data->coursemodule));

        $newconfig = false;
        if (empty($cmconfig)) {
            $newconfig = true;
            $cmconfig = new stdClass();
            $cmconfig->cmid = $data->coursemodule;
        }

        if ($data->activated === '1') {
            // Validation on thresholds.
            if (!isset($data->warningthreshold, $data->criticalthreshold) ||
                $data->warningthreshold > $data->criticalthreshold ||
                $data->warningthreshold > 100 || $data->warningthreshold < 0 ||
                $data->criticalthreshold > 100 || $data->criticalthreshold < 0
            ) {
                $data->warningthreshold = 10;
                $data->criticalthreshold = 25;
            }

            if (get_config("plagiarism_compilatio", "enable_show_reports") !== '1') {
                $data->showstudentreport = 'never';
            }

            $user = $DB->get_record('plagiarism_compilatio_user', array("userid" => $USER->id));

            if (empty($user)) {
                $compilatio = new CompilatioAPI(get_config('plagiarism_compilatio', 'apikey'));

                // Check if user already exists in Compilatio.
                $compilatioid = $compilatio->get_user($USER->email);

                // Create the user if doesn't exists.
                if ($compilatioid == 404) {
                    $lang = substr(current_language(), 0, 2);
                    $compilatioid = $compilatio->set_user($USER->firstname, $USER->lastname, $USER->email, $lang);
                }

                $user = new stdClass();
                $user->compilatioid = $compilatioid;
                $user->userid = $USER->id;

                if (compilatio_valid_md5($compilatioid)) {
                    $user->id = $DB->insert_record('plagiarism_compilatio_user', $user);
                }
            }

            $cmconfig->userid = $user->compilatioid;

            $compilatio = new CompilatioAPI(get_config("plagiarism_compilatio", "apikey"), $user->compilatioid);

            if (isset($user->id) && ($data->termsofservice ?? false)) {
                $user->validatedtermsofservice = true;
                $DB->update_record('plagiarism_compilatio_user', $user);

                $compilatio->validate_terms_of_service();
            }

            foreach ($plugin->config_options() as $element) {
                $cmconfig->$element = $data->$element ?? null;
            }

            // Get Datetime for Compilatio folder.
            $date = new DateTime();
            $date->setTimestamp($data->analysistime);
            $data->analysistime = $date->format('Y-m-d H:i:s');

            if ($newconfig) {
                $folderid = $compilatio->set_folder($data->name, $data->defaultindexing, $data->analysistype,
                $data->analysistime, $data->warningthreshold, $data->criticalthreshold);
                if (compilatio_valid_md5($folderid)) {
                    $cmconfig->folderid = $folderid;
                }
            } else {
                $compilatio->update_folder($cmconfig->folderid, $data->name, $data->defaultindexing, $data->analysistype,
                    $data->analysistime, $data->warningthreshold, $data->criticalthreshold);
            }
        } else {
            $cmconfig->activated = 0;
        }

        if ($newconfig) {
            $DB->insert_record('plagiarism_compilatio_module', $cmconfig);
        } else {
            $DB->update_record('plagiarism_compilatio_module', $cmconfig);
        }
    }
    return $data;
}

/**
 * Hook to add plagiarism specific settings to a module settings page
 *
 * @param moodleform $formwrapper
 * @param MoodleQuickForm $mform
 */
function plagiarism_compilatio_coursemodule_standard_elements($formwrapper, $mform) {

    global $DB;

    $plugin = new plagiarism_plugin_compilatio();
    $plagiarismsettings = $plugin->get_settings();
    if (!$plagiarismsettings) {
        return;
    }
    // Hack to prevent this from showing on custom compilatioassignment type.
    if ($mform->elementExists('seuil_faible')) {
        return;
    }

    $cmid = null;
    if ($cm = $formwrapper->get_coursemodule()) {
        $cmid = $cm->id;
    }
    $matches = array();
    if (!preg_match('/^mod_([^_]+)_mod_form$/', get_class($formwrapper), $matches)) {
        return;
    }
    $modulename = "mod_" . $matches[1];
    $modname = 'enable_' . $modulename;
    if (empty($plagiarismsettings[$modname])) {
        return;
    }
    $context = context_course::instance($formwrapper->get_course()->id);

    $defaultconfig = $DB->get_record('plagiarism_compilatio_module', array('cmid' => 0));
    if (!empty($cmid)) {
        $config = $DB->get_record('plagiarism_compilatio_module', array('cmid' => $cmid));
    }

    $plagiarismelements = $plugin->config_options();

    if (has_capability('plagiarism/compilatio:enable', $context)) {
        compilatio_get_form_elements($mform, false, $modulename);

        // Disable all plagiarism elements if activated eg 0.
        foreach ($plagiarismelements as $element) {
            if ($element != 'activated') {
                $mform->disabledIf($element, 'activated', 'eq', 0);
            }
        }
    } else { // Add plagiarism settings as hidden vars.
        foreach ($plagiarismelements as $element) {
            $mform->addElement('hidden', $element);
        }
    }

    foreach ($plagiarismelements as $element) {
        $mform->setDefault($element, $config->$element ?? $defaultconfig->$element);
    }
}

/**
 * Adds the list of plagiarism settings to a form.
 *
 * @param object  $mform    Moodle form object
 * @param boolean $defaults if this is being loaded from defaults form or from inside a mod.
 * @param string  $modulename
 */
function compilatio_get_form_elements($mform, $defaults = false, $modulename = '') {

    global $PAGE, $CFG, $USER, $DB;

    $lang = substr(current_language(), 0, 2);

    $ynoptions = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );

    $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));

    if ($modulename === 'mod_quiz') {
        $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
        $mform->addElement('html', "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>");
    }

    $mform->addElement('select', 'activated', get_string("activated", "plagiarism_compilatio"), $ynoptions);
    $mform->setDefault('activated', 1);

    if (!$defaults) {
        $cmpuser = $DB->get_record('plagiarism_compilatio_user', array('userid' => $USER->id));
        $termsofservice = "https://app.compilatio.net/api/private/terms-of-service/magister/" . $lang;

        if (empty($cmpuser) || $cmpuser->validatedtermsofservice == 0) {
            $mform->addElement('checkbox', 'termsofservice', get_string("terms_of_service", "plagiarism_compilatio", $termsofservice));
            $mform->setDefault('termsofservice', 0);
            $mform->addRule('termsofservice', null, 'required', null, 'client');

            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_form', 'requiredTermsOfService');
        } else {
            $group = [];
            $group[] = $mform->createElement("html", "<p>" . get_string("terms_of_service_info", "plagiarism_compilatio", $termsofservice) . "</p>");
            $mform->addGroup($group, 'tos_info', '', ' ', false);
            $mform->hideIf('tos_info', 'activated', 'eq', '0');
        }
    }

    $analysistypes = array('manual' => get_string('analysistype_manual', 'plagiarism_compilatio'),
        'planned' => get_string('analysistype_prog', 'plagiarism_compilatio'));

    if (get_config("plagiarism_compilatio", "enable_analyses_auto") == '1') {
        $analysistypes['auto'] = get_string('analysistype_auto', 'plagiarism_compilatio');
        $help = "analysistype_auto";
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'analysistype', get_string('analysistype', 'plagiarism_compilatio'), $analysistypes);
        $mform->addHelpButton('analysistype', $help ?? 'analysistype', 'plagiarism_compilatio');
        $mform->setDefault('analysistype', 'manual');
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector', 'analysistime', get_string('analysis_date', 'plagiarism_compilatio'), array('optional' => false));
        $mform->setDefault('analysistime', time() + 7 * 24 * 3600);
        $mform->disabledif('analysistime', 'analysistype', 'noteq', 'planned');

        // TODO The image is stored in v4, must be updated to work on v5.
        if ($lang == 'fr') {
            $group = [];
            $group[] = $mform->createElement('static', 'calendar', '',
                "<img style='width: 40em;' src='https://content.compilatio.net/images/calendrier_affluence_magister.png'>");
            $mform->addGroup($group, 'calendargroup', '', ' ', false);
            $mform->hideIf('calendargroup', 'analysistype', 'noteq', 'planned');
        }
    }

    $showoptions = array(
        'never' => get_string("never"),
        'immediately' => get_string("immediately", "plagiarism_compilatio"),
        'closed' => get_string("showwhenclosed", "plagiarism_compilatio"),
    );

    $mform->addElement('select', 'showstudentscore', get_string("showstudentscore", "plagiarism_compilatio"), $showoptions);
    $mform->addHelpButton('showstudentscore', 'showstudentscore', 'plagiarism_compilatio');

    if (get_config("plagiarism_compilatio", "enable_show_reports") === '1') {
        $mform->addElement('select', 'showstudentreport', get_string("showstudentreport", "plagiarism_compilatio"), $showoptions);
        $mform->addHelpButton('showstudentreport', 'showstudentreport', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }

    if (get_config("plagiarism_compilatio", "enable_student_analyses") === '1' && !$defaults) {
        if ($mform->elementExists('submissiondrafts')) {
            $mform->addElement('select', 'studentanalyses',
                get_string("studentanalyses", "plagiarism_compilatio"), $ynoptions);
            $mform->addHelpButton('studentanalyses', 'studentanalyses', 'plagiarism_compilatio');

            $mform->disabledif('studentanalyses', 'submissiondrafts', 'eq', '0');

            $group = [];
            $group[] = $mform->createElement("html", "<p style='color: #b94a48;'>" .
                get_string('activate_submissiondraft', 'plagiarism_compilatio',
                get_string('submissiondrafts', 'assign')) .
                " <b>" . get_string('submissionsettings', 'assign') . ".</b></p>");
            $mform->addGroup($group, 'activatesubmissiondraft', '', ' ', false);
            $mform->hideIf('activatesubmissiondraft', 'submissiondrafts', 'eq', '1');
        }
    }

    $mform->addElement('select', 'studentemail',
        get_string("studentemail", "plagiarism_compilatio"), $ynoptions);
    $mform->addHelpButton('studentemail', 'studentemail', 'plagiarism_compilatio');

    // Indexing state.
    $mform->addElement('select', 'defaultindexing', get_string("defaultindexing", "plagiarism_compilatio"), $ynoptions);
    $mform->addHelpButton('defaultindexing', 'defaultindexing', 'plagiarism_compilatio');
    $mform->setDefault('defaultindexing', 1);

    // Threshold settings.
    $mform->addElement('html', '<p><strong>' . get_string("thresholds_settings", "plagiarism_compilatio") . '</strong></p>');
    $mform->addElement('html', '<p>' . get_string("thresholds_description", "plagiarism_compilatio") . '</p>');

    $mform->addElement('html', '<div>');
    $mform->addElement('text', 'warningthreshold',
        get_string("warningthreshold", "plagiarism_compilatio"),
        'size="5" id="warningthreshold"');
    $mform->addElement('html', '<noscript>' . get_string('similarity_percent', "plagiarism_compilatio") . '</noscript>');

    $mform->addElement('text', 'criticalthreshold',
        get_string("criticalthreshold", "plagiarism_compilatio"),
        'size="5" id="criticalthreshold"');
    $mform->addElement('html', '<noscript>' .
        get_string('similarity_percent', "plagiarism_compilatio") .
        ', ' . get_string("red_threshold", "plagiarism_compilatio") .
        '</noscript>');
    $mform->addElement('html', '</div>');

    // Max file size / min words / max words.
    $size = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
    $mform->addElement('html', '<p>' . get_string("max_file_size_allowed", "plagiarism_compilatio", $size) . '</p>');

    $word = new stdClass();
    $word->max = get_config('plagiarism_compilatio', 'max_word');
    $word->min = get_config('plagiarism_compilatio', 'min_word');
    $mform->addElement('html', '<p>' . get_string("min_max_word_required", "plagiarism_compilatio", $word) . '</p>');

    // File types allowed.
    $filetypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));
    $filetypesstring = '';
    foreach ($filetypes as $type => $value) {
        $filetypesstring .= $type . ", ";
    }
    $filetypesstring = substr($filetypesstring, 0, -2);
    $mform->addElement('html', '<div>' . get_string("help_compilatio_format_content", "plagiarism_compilatio") . $filetypesstring . '</div>');

    // Used to append text nicely after the inputs.
    $strsimilaritypercent = get_string("similarity_percent", "plagiarism_compilatio");
    $strredtreshold = get_string("red_threshold", "plagiarism_compilatio");
    $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_form', 'afterPercentValues',
        array($strsimilaritypercent, $strredtreshold));

    // Numeric validation for Thresholds.
    $mform->addRule('warningthreshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');
    $mform->addRule('criticalthreshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');

    $mform->setType('warningthreshold', PARAM_INT);
    $mform->setType('criticalthreshold', PARAM_INT);

    $mform->setDefault('warningthreshold', '10');
    $mform->setDefault('criticalthreshold', '25');
}

/**
 * Get the plagiarism values for this course module
 *
 * @param  int          $cmid           Course module (cm) ID
 * @return array|false  $plag_values    Plagiarism values or false if the plugin is not enabled for this cm
 */
function compilatio_cm_use($cmid) {

    global $DB;

    $cm = $DB->get_record('plagiarism_compilatio_module', array('cmid' => $cmid));

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

    $notuploadedfiles = array();
    $fs = get_file_storage();

    $sql = "SELECT ass.id as itemid, con.id as contextid
            FROM {course_modules} cm
                JOIN {context} con ON cm.id = con.instanceid
                JOIN {assignsubmission_file} assf ON assf.assignment = cm.instance
                JOIN {assign_submission} ass ON assf.submission = ass.id
                JOIN {user_enrolments} ue ON ass.userid = ue.userid
                JOIN {enrol} enr ON ue.enrolid = enr.id
            WHERE cm.id=? AND con.contextlevel = 70 AND assf.numfiles > 0 AND enr.courseid = cm.course";

    $filesids = $DB->get_records_sql($sql, array($cmid));

    foreach ($filesids as $fileid) {
        $files = $fs->get_area_files($fileid->contextid, 'assignsubmission_file', 'submission_files', $fileid->itemid);

        foreach ($files as $file) {
            if ($file->get_filename() != '.') {
                // TODO get_record sur champ pas unique identifier => logs  
                $compifile = $DB->get_record('plagiarism_compilatio_files',
                    array('identifier' => $file->get_contenthash(), 'cm' => $cmid));

                if (!$compifile) {
                    array_push($notuploadedfiles, $file);
                }
            }
        }
    }

    return $notuploadedfiles;
}

/**
 * Check if Compilatio is enabled
 *  in moodle
 *  in this module type
 *  in this course module
 *
 * @param  int      $cmid Course module ID
 * @return boolean  Return true if enabled, false otherwise
 */
function compilatio_enabled($cmid) {

    global $DB;
    $cm = get_coursemodule_from_id(null, $cmid);
    // Get plugin activation info.
    $conditions = array('plugin' => 'plagiarism_compilatio', 'name' => 'enabled');
    $pluginenabled = $DB->get_field('config_plugins', 'value', $conditions);

    // Get module type activation info.
    $conditions = array('plugin' => 'plagiarism_compilatio', 'name' => 'enable_mod_' . $cm->modname);
    $modtypeenabled = $DB->get_field('config_plugins', 'value', $conditions);

    // Get course module activation info.
    $cmenabled = $DB->get_field('plagiarism_compilatio_module', 'activated', array('cmid' => $cmid));

    // Check if the module associated with this event still exists.
    $cmexists = $DB->record_exists('course_modules', array('id' => $cmid));

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
                FROM {plagiarism_compilatio_module} module
                JOIN {course_modules} course_modules ON module.cmid = course_modules.id
                WHERE course_modules.course = ?';

        compilatio_delete_modules($DB->get_records_sql($sql, array($course->id)));
    }
}

/**
 * compilatio_delete_modules
 *
 * Deindex and remove documents and folder in Compilatio
 * Remove files and module in moodle tables
 *
 * @param array    $modules
 */
function compilatio_delete_modules($modules) {
    if (is_array($modules)) {
        global $DB;
        $compilatio = new CompilatioAPI(get_config('plagiarism_compilatio', 'apikey'));

        foreach ($modules as $module) {
            $files = $DB->get_records('plagiarism_compilatio_files', array('cm' => $module->cmid));

            $keepfileindexed = boolval(get_config('plagiarism_compilatio', 'keep_docs_indexed'));
            compilatio_delete_files($files, true, $keepfileindexed);

            $compilatio->set_user_id($module->userid);
            $compilatio->delete_folder($module->folderid);
            $DB->delete_records('plagiarism_compilatio_module', array('id' => $module->id));
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
 * @param bool     $deletefilesmoodledb
 */
function compilatio_delete_files($files, $deletefilesmoodledb = true, $keepfilesindexed = false) {
    if (is_array($files)) {
        global $DB;
        $compilatio = new CompilatioAPI(get_config('plagiarism_compilatio', 'apikey'));

        foreach ($files as $doc) {
            if (is_null($doc->externalid)) {
                if ($deletefilesmoodledb) {
                    $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                }
            } else {
                $userid = $DB->get_field("plagiarism_compilatio_module", "userid", array("cmid" => $doc->cm));
                $compilatio->set_user_id($userid);
                
                if ($keepfilesindexed || $compilatio->set_indexing_state($doc->externalid, 0)) {
                    $compilatio->delete_document($doc->externalid);
                    if ($deletefilesmoodledb) {
                        $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                    }
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
    if (get_config("plagiarism_compilatio", "enable_student_analyses") === '1' && $studentanalysesparam === '1') {
        $sql = "SELECT sub.status
            FROM {course_modules} cm
            JOIN {assign_submission} sub ON cm.instance = sub.assignment
            WHERE cm.id = ? AND userid = ?";

        $status = $DB->get_field_sql($sql, array($cmid, $userid));

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

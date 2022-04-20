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
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/output_helper.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/csv_helper.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/send_file.php');

use plagiarism_compilatio\CompilatioService;

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

        // Quiz - only essay question are supported for the moment.
        if (!empty($linkarray['component']) && $linkarray['component'] == 'qtype_essay') {

            if (empty($linkarray['cmid']) || empty($linkarray['content'])) {
                $quba = question_engine::load_questions_usage_by_activity($linkarray['area']);

                if (empty($linkarray['cmid'])) {
                    // Try to get cm using the questions owning context.
                    $context = $quba->get_owning_context();
                    if ($context->contextlevel == CONTEXT_MODULE) {
                        $cm = get_coursemodule_from_id(false, $context->instanceid);
                    }
                    $linkarray['cmid'] = $cm->id;
                }
                if (!empty($linkarray['cmid'])) {
                    if (empty($linkarray['userid']) || (empty($linkarray['content'])) && empty($linkarray['file'])) {
                        // Try to get userid from attempt step.
                        $attempt = $quba->get_question_attempt($linkarray['itemid']);
                        if (empty($linkarray['userid'])) {
                            $linkarray['userid'] = $attempt->get_step(0)->get_user_id();
                        }
                        // If content and file not submitted, try to get the content.
                        if (empty($linkarray['content']) && empty($linkarray['file'])) {
                            $linkarray['content'] = $attempt->get_response_summary();
                        }
                    }
                } else {
                    return '';
                }
            }
        }

        // Check if Compilatio is enabled in moodle->module->cm.
        if (!compilatio_enabled($linkarray['cmid'])) {
            return '';
        }

        // Get Compilatio's module configuration.
        $plugincm = compilatio_cm_use($linkarray['cmid']);

        global $DB, $CFG, $PAGE, $USER;
        $output = '';

        // DOM Compilatio index for ajax callback.
        static $domid = 0;
        $domid++;

        $cm = get_coursemodule_from_id(null, $linkarray['cmid']);

        // Get submiter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In assigns.
            $userid = $DB->get_field('assign_submission', 'userid', array('id' => $linkarray['file']->get_itemid()));
        }

        if (!empty($linkarray['content'])) {
            $identifier = sha1($linkarray['content']);
        } else if (!empty($linkarray['file'])) {
            $filename = $linkarray['file']->get_filename();
            $identifier = $linkarray['file']->get_contenthash();
        } else {
            return $output;
        }

        // Don't show Compilatio if not allowed.
        $modulecontext = context_module::instance($linkarray['cmid']);
        $teacher = $viewscore = $viewreport = has_capability('plagiarism/compilatio:viewreport', $modulecontext);
        $cantriggeranalysis = has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext);
        $studentanalyse = compilatio_student_analysis($plugincm->studentanalyses, $linkarray['cmid'], $userid);

        if ($USER->id == $userid) {
            if ($studentanalyse) {
                if ($teacher) {
                    $output .= "<div>" . get_string("student_analyze", "plagiarism_compilatio");
                } else {
                    $output .= "<div>" . get_string("student_help", "plagiarism_compilatio");
                }
                $viewreport = true;
                $viewscore = true;
            }

            $assignclosed = false;
            if ($cm->completionexpected != 0 && time() > $cm->completionexpected) {
                $assignclosed = true;
            }

            $allowed = get_config("plagiarism_compilatio", "allow_teachers_to_show_reports");
            $showreport = $plugincm->showstudentreport ?? null;
            if ($allowed === '1' && ($showreport == 'immediately' || ($showreport == 'closed' && $assignclosed))) {
                $viewreport = true;
            }

            $showscore = $plugincm->showstudentscore ?? null;
            if ($showscore == 'immediately' || ($showscore == 'closed' && $assignclosed)) {
                $viewscore = true;
            }
        }
        if (!$viewscore) {
            return '';
        }

        // Get compilatio file record.
        $cmpfile = $DB->get_record('plagiarism_compilatio_files',
            array('cm' => $linkarray['cmid'], 'userid' => $userid, 'identifier' => $identifier));

        if (empty($cmpfile)) { // Try to get record without userid in forums.
            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND identifier = ?";
            $cmpfile = $DB->get_record_sql($sql, array($linkarray['cmid'], $identifier));
        }

        $url = null;

        // No compilatio file in DB yet.
        if (empty($cmpfile)) {
            if ($cantriggeranalysis) {
                // Only works for assign.
                if (!isset($linkarray["file"]) || $cm->modname != 'assign'
                    || $linkarray['file']->get_filearea() == 'introattachment') {
                    return $output;
                }

                // Catch GET 'sendfile' param.
                $trigger = optional_param('sendfile', 0, PARAM_INT);
                $fileid = $linkarray["file"]->get_id();
                if ($trigger == $fileid && !defined("CMP_MANUAL_SEND")) {
                    CompilatioSendFile::send_unsent_files(array($linkarray['file']), $linkarray['cmid']);
                    return $output . $this->get_links($linkarray);
                }

                $cmpfile = new stdClass();
                $cmpfile->status = "sent";
                $urlparams = array("id" => $linkarray['cmid'],
                                "sendfile" => $fileid,
                                "action" => "grading",
                                'page' => optional_param('page', null, PARAM_INT));
                $moodleurl = new moodle_url("/mod/assign/view.php", $urlparams);
                $url = array("url" => $moodleurl, "target-blank" => false);

            } else {
                return '';
            }
        }

        $output .= output_helper::get_compilatio_btn($domid, $cmpfile, $teacher, $cantriggeranalysis, $studentanalyse, $viewreport, $url);

        // Now check for differing filename and display info related to it.
        if (isset($filename) && $filename !== $cmpfile->filename) {
            $output .= '<span class="compilatio-prevsubmitted">(' . get_string('previouslysubmitted', 'plagiarism_compilatio') . ': ' . $cmpfile->filename . ')</span>';
        }

        if ($studentanalyse) {
            $output .= "</div>";
        }

        return $output;
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

    global $CFG, $PAGE, $OUTPUT, $DB, $SESSION, $USER;

    if (!$PAGE->context instanceof \context_module) {
        return;
    }

    if (!has_capability('plagiarism/compilatio:viewreport', $PAGE->context)) {
        return;
    }

    if ($PAGE->url->compare(new moodle_url('/mod/assign/view.php'), URL_MATCH_BASE)) {
        if (optional_param('action', null, PARAM_RAW) != 'grading') {
            return;
        }
        $module = 'assign';
    } else if ($PAGE->url->compare(new moodle_url('/mod/forum/view.php'), URL_MATCH_BASE)) {
        $module = 'forum';
    } else if ($PAGE->url->compare(new moodle_url('/mod/workshop/view.php'), URL_MATCH_BASE)) {
        $module = 'workshop';
    } else if ($PAGE->url->compare(new moodle_url('/mod/quiz/report.php'), URL_MATCH_BASE)) {
        $module = 'quiz';
    } else {
        return;
    }

    $cmid = $PAGE->context->instanceid;
    $plagiarismsettings = (array) get_config('plagiarism_compilatio');

    $compilatioenabled = $plagiarismsettings["enabled"] && $plagiarismsettings["enable_mod_" . $module];

    $compilatioactivated = $DB->get_field("plagiarism_compilatio_module", "activated", array("cmid" => $cmid));

    if ($compilatioactivated != 1 || !$compilatioenabled) {
        return;
    }

    $export = optional_param('compilatio_export', '', PARAM_BOOL);
    if ($export) {
        csv_helper::generate_cm_csv($cmid, $module);
    }

    // Store plagiarismfiles in $SESSION.
    $sql = "cm = ? AND externalid IS NOT null";
    $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, array($cmid));
    $filesids = array_keys($SESSION->compilatio_plagiarismfiles);

    $alerts = array();

    // TODO ?
    $mod = $DB->get_record('plagiarism_compilatio_module', array("cmid" => $cmid));
    if (null === $mod->folderid) {
        $alerts[] = array(
            "class" => "danger",
            "title" => "Erreur",
            "content" => "Une erreur s'est produite lors de la création de l'activité.
            Les analyses automatiques et programmées ne fonctionnent pas. Vous pouvez lancer les analyses manuellement."
        );
    }
    // TODO ?

    if (isset($SESSION->compilatio_alert)) {
        $alerts[] = $SESSION->compilatio_alert;
        unset($SESSION->compilatio_alert);
    }
    if (isset($SESSION->compilatio_alert_max_attempts)) {
        $alerts[] = $SESSION->compilatio_alert_max_attempts;
        unset($SESSION->compilatio_alert_max_attempts);
    }

    $startallanalysis = $restartfailedanalysis = false;

    $analysistype = $DB->get_field('plagiarism_compilatio_module', "analysistype", array("cmid" => $cmid));

    if ($analysistype == 'manual') {
        $startallanalysis = true;

    } else if ($analysistype == 'planned') { // Display the date of analysis if its type is set on 'Planned'.
        $analysistime = $DB->get_field('plagiarism_compilatio_module', "analysistime", array("cmid" => $cmid));
        $date = userdate($analysistime);
        if ($analysistime > time()) {
            $analysisdate = get_string("programmed_analysis_future", "plagiarism_compilatio", $date);
        } else {
            $analysisdate = get_string("programmed_analysis_past", "plagiarism_compilatio", $date);
        }
    }

    // Get webservice status :.
    $webservicestatus = get_config('plagiarism_compilatio', 'connection_webservice');
    // If the record exists and if the webservice is marked as unreachable in Cron function :.
    if ($webservicestatus != null && $webservicestatus === '0') {
        $alerts[] = array(
            "class" => "danger",
            "title" => get_string("webservice_unreachable_title", "plagiarism_compilatio"),
            "content" => get_string("webservice_unreachable_content", "plagiarism_compilatio"));
    }

    // Display restart analysis button if necesseary.
    $sql = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_files} pcf 
        WHERE pcf.cm=? AND (status = 'error_analysis_failed' OR status = 'error_sending_failed')";
    if ($DB->count_records_sql($sql, array($cmid)) !== 0) {
        $restartfailedanalysis = true;
    }

    // Check for unsend documents
    if ($module == 'assign') {
        $countunsend = count(compilatio_get_unsent_documents($cmid));

        if ($countunsend !== 0) {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsent_documents", "plagiarism_compilatio"),
                "content" => get_string("unsent_documents_content", "plagiarism_compilatio"),
            );
            $startallanalysis = $restartfailedanalysis = true;
        }
    } else {
        $countunsend = 0;
    }

    $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

    foreach ($compilatio->get_alerts() as $alert) {
        $language = substr(current_language(), 0, 2);
        $translation = $compilatio->get_translation($language, $alert->text);

        if (empty($translation)) {
            $text = $alert->text;
        } else {
            $text = $translation;
        }

        if (time() > strtotime($alert->activation_period->start) && time() < strtotime($alert->activation_period->end)) {
            $alerts[] = array(
                "class" => "info",
                "title" => "<i class='fa-lg fa fa-info-circle'></i>",
                "content" => $text,
            );
        }
    }

    return output_helper::get_compilatio_frame(
        $cmid,
        $alerts,
        $plagiarismsettings,
        $startallanalysis,
        $restartfailedanalysis,
        $filesids,
        $module,
        $countunsend ?? null,
        $analysisdate ?? null
    );
}

function get_compilatio_user($validatedtermsofservice) {
    global $USER, $DB;

    
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

            if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") !== '1') {
                $data->showstudentreport = 'never';
            }

            $user = $DB->get_record('plagiarism_compilatio_user', array("userid" => $USER->id));

            if (empty($user)) {
                $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

                // Check if user already exists in Compilatio.
                $compilatioid = $compilatio->get_user($USER->email);

                // Create the user if doesn't exists.
                if ($compilatioid == 404) {
                    $compilatioid = $compilatio->set_user($USER->firstname, $USER->lastname, $USER->email);
                }

                $user = new stdClass();
                $user->compilatioid = $compilatioid;
                $user->userid = $USER->id;

                if (compilatio_valid_md5($compilatioid)) {
                    $id = $DB->insert_record('plagiarism_compilatio_user', $user);
                    //TODO
                    $user = $DB->get_record('plagiarism_compilatio_user', array("id" => $id));
                }
            }

            $cmconfig->userid = $user->compilatioid;

            $compilatio = new CompilatioService(get_config("plagiarism_compilatio", "apikey"), $user->compilatioid);

            if ($data->termsofservice ?? false) {
                $user->validatedtermsofservice = true;
                $DB->update_record('plagiarism_compilatio_user', $user);

                $compilatio->validate_terms_of_service();
            }

            foreach ($plugin->config_options() as $element) {
                $cmconfig->$element = $data->$element ?? null;
            }

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

    // TODO subtring ?
    $lang = current_language();

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
    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'analysistype', get_string('analysis', 'plagiarism_compilatio'), $analysistypes);
        $mform->addHelpButton('analysistype', 'analysis', 'plagiarism_compilatio');
        $mform->setDefault('analysistype', 'manual');
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector', 'analysistime', get_string('analysis_date', 'plagiarism_compilatio'), array('optional' => false));
        $mform->setDefault('analysistime', time() + 7 * 24 * 3600);
        $mform->disabledif('analysistime', 'analysistype', 'noteq', 'planned');

        // TODO img in v4 !
        /*if ($lang == 'fr') {
            $group = [];
            $group[] = $mform->createElement('static', 'calendar', '',
                "<img style='width: 45em;' src='https://content.compilatio.net/images/calendrier_affluence_magister.png'>");
            $mform->addGroup($group, 'calendargroup', '', ' ', false);
            $mform->hideIf('calendargroup', 'analysistype', 'noteq', 'planned');
        }*/
    }

    $showoptions = array(
        'never' => get_string("never"),
        'immediately' => get_string("immediately", "plagiarism_compilatio"),
        'closed' => get_string("showwhenclosed", "plagiarism_compilatio"),
    );

    $mform->addElement('select', 'showstudentscore', get_string("showstudentscore", "plagiarism_compilatio"), $showoptions);
    $mform->addHelpButton('showstudentscore', 'showstudentscore', 'plagiarism_compilatio');

    if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") === '1') {
        $mform->addElement('select', 'showstudentreport', get_string("showstudentreport", "plagiarism_compilatio"), $showoptions);
        $mform->addHelpButton('showstudentreport', 'showstudentreport', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }

    if (get_config("plagiarism_compilatio", "allow_student_analyses") === '1' && !$defaults) {
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

    $sql = "SELECT assf.submission as itemid, con.id as contextid
            FROM {course_modules} cm
                JOIN {assignsubmission_file} assf ON assf.assignment = cm.instance
                JOIN {context} con ON cm.id = con.instanceid
            WHERE cm.id=? AND con.contextlevel = 70 AND assf.numfiles > 0";

    $filesids = $DB->get_records_sql($sql, array($cmid));

    foreach ($filesids as $fileid) {
        $files = $fs->get_area_files($fileid->contextid, 'assignsubmission_file', 'submission_files', $fileid->itemid);

        foreach ($files as $file) {
            if ($file->get_filename() != '.') {
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
        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

        foreach ($modules as $module) {
            $files = $DB->get_records('plagiarism_compilatio_files', array('cm' => $module->cmid));
            compilatio_delete_files($files);
    
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
function compilatio_delete_files($files, $deletefilesmoodledb = true) {
    if (is_array($files)) {
        global $DB;
        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

        foreach ($files as $doc) {
            if (is_null($doc->externalid)) {
                if ($deletefilesmoodledb) {
                    $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                }
            } else {
                $userid = $DB->get_field("plagiarism_compilatio_module", "userid", array("cmid" => $doc->cm));
                $compilatio->set_user_id($userid);
                
                if ($compilatio->set_indexing_state($doc->externalid, 0)) {
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
    if (get_config("plagiarism_compilatio", "allow_student_analyses") === '1' && $studentanalysesparam === '1') {
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

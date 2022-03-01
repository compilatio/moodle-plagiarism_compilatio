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
            'use_compilatio',
            'show_student_score',
            'show_student_report',
            'student_analyses',
            'student_email',
            'time_analyse',
            'analysis_type',
            'green_threshold',
            'orange_threshold',
            'indexing_state',
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
        $studentanalyse = compilatio_student_analysis($plugincm['student_analyses'], $linkarray['cmid'], $userid);

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
            $showreport = $plugincm['show_student_report'] ?? null;
            if ($allowed === '1' && ($showreport == 'immediately' || ($showreport == 'closed' && $assignclosed))) {
                $viewreport = true;
            }

            $showscore = $plugincm['show_student_score'] ?? null;
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

        $config = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $linkarray['cmid']), '', 'name, value');
        $output .= output_helper::get_compilatio_btn($domid, $cmpfile, $config, $teacher, $cantriggeranalysis, $studentanalyse, $viewreport, $url);

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
        if (!empty($plagiarismsettings['student_disclosure']) &&
            !empty($compilatiouse)) {
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

    global $CFG, $PAGE, $OUTPUT, $DB, $SESSION;

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
    $sql = "SELECT value FROM {plagiarism_compilatio_config} WHERE cm=? AND name='use_compilatio'";
    $activecompilatio = $DB->get_record_sql($sql, array($cmid));
    // Compilatio not enabled, return.

    if ($activecompilatio === false) {
        // Plagiarism settings have not been saved :.
        $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => 0), '', 'name, value');

        $plugin = new plagiarism_plugin_compilatio();
        $plagiarismelements = $plugin->config_options();
        foreach ($plagiarismelements as $element) {
            if (isset($plagiarismdefaults[$element])) {
                $newelement = new Stdclass();
                $newelement->cm = $cmid;
                $newelement->name = $element;
                $newelement->value = $plagiarismdefaults[$element];

                $DB->insert_record('plagiarism_compilatio_config', $newelement);
            }
        }
        // Get the new status.
        $activecompilatio = $DB->get_record_sql($sql, array($cmid));
    }

    if ($activecompilatio == null || $activecompilatio->value != 1 || !$compilatioenabled) {
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

    if (isset($SESSION->compilatio_alert)) {
        $alerts[] = $SESSION->compilatio_alert;
        unset($SESSION->compilatio_alert);
    }
    if (isset($SESSION->compilatio_alert_max_attempts)) {
        $alerts[] = $SESSION->compilatio_alert_max_attempts;
        unset($SESSION->compilatio_alert_max_attempts);
    }

    $startallanalysis = $restartfailedanalysis = false;

    // Get compilatio analysis type.
    $record = $DB->get_record_select('plagiarism_compilatio_config', "cm = ? AND name='analysis_type'", array($cmid));
    $value = $record->value;

    if ($value == 'manual') {
        $startallanalysis = true;

    } else if ($value == 'planned') { // Display the date of analysis if its type is set on 'Planned'.
        $plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_config', "cm = ? AND name='time_analyse'", array($cmid));
        $record = reset($plagiarismfiles); // Get the first value of the array.
        $date = userdate($record->value);
        if ($record->value > time()) {
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

    // Add the Compilatio news to the alerts displayed :.
    // TODO $alerts = array_merge($alerts, compilatio_display_news());

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

/**
 * Hook to save plagiarism specific settings on a module settings page
 *
 * @param stdClass $data
 * @param stdClass $course
 */
function plagiarism_compilatio_coursemodule_edit_post_actions($data, $course) {

    global $DB;
    $plugin = new plagiarism_plugin_compilatio();
    if (!$plugin->get_settings()) {
        return $data;
    }

    if (isset($data->use_compilatio)) {
        // Array of possible plagiarism config options.
        $plagiarismelements = $plugin->config_options();

        // Validation on thresholds :
        // Set thresholds to default if the green one is greater than the orange.
        if (!isset($data->green_threshold, $data->orange_threshold) ||
            $data->green_threshold > $data->orange_threshold ||
            $data->green_threshold > 100 ||
            $data->green_threshold < 0 ||
            $data->orange_threshold > 100 ||
            $data->orange_threshold < 0
        ) {
            $data->green_threshold = 10;
            $data->orange_threshold = 25;
        }

        if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") !== '1') {
            $data->show_student_report = 'never';
        }

        // First get existing values.
        $existingelements = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $data->coursemodule), '', 'name, id');

        foreach ($plagiarismelements as $element) {
            $newelement = new stdClass();
            $newelement->cm = $data->coursemodule;
            $newelement->name = $element;
            $newelement->value = (isset($data->$element) ? $data->$element : 0);
            if (isset($existingelements[$element])) { // Update.
                $newelement->id = $existingelements[$element];
                $DB->update_record('plagiarism_compilatio_config', $newelement);
            } else { // Insert.
                $DB->insert_record('plagiarism_compilatio_config', $newelement);
            }
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
    if (!empty($cmid)) {
        $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    }
    // The cmid(0) is the default list.
    $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => 0), '', 'name, value');
    $plagiarismelements = $plugin->config_options();
    if (has_capability('plagiarism/compilatio:enable', $context)) {
        compilatio_get_form_elements($mform, false, $modulename);

        // Disable all plagiarism elements if use_plagiarism eg 0.
        foreach ($plagiarismelements as $element) {
            if ($element != 'use_compilatio') { // Ignore this var.
                $mform->disabledIf($element, 'use_compilatio', 'eq', 0);
            }
        }
    } else { // Add plagiarism settings as hidden vars.
        foreach ($plagiarismelements as $element) {
            $mform->addElement('hidden', $element);
        }
    }
    // Now set defaults.
    foreach ($plagiarismelements as $element) {
        if (isset($plagiarismvalues[$element])) {
            $mform->setDefault($element, $plagiarismvalues[$element]);
        } else if (isset($plagiarismdefaults[$element])) {
            $mform->setDefault($element, $plagiarismdefaults[$element]);
        }
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

    global $PAGE, $CFG;

    $ynoptions = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );

    $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));

    if ($modulename === 'mod_quiz') {
        $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
        $mform->addElement('html', "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>");
    }

    $mform->addElement('select', 'use_compilatio', get_string("use_compilatio", "plagiarism_compilatio"), $ynoptions);
    $mform->setDefault('use_compilatio', 1);

    $analysistypes = array('manual' => get_string('analysistype_manual', 'plagiarism_compilatio'),
        'planned' => get_string('analysistype_prog', 'plagiarism_compilatio'));
    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'analysis_type',
            get_string('analysis', 'plagiarism_compilatio'),
            $analysistypes);
        $mform->addHelpButton('analysis_type', 'analysis', 'plagiarism_compilatio');
        $mform->setDefault('analysis_type', 'manual');
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector',
            'time_analyse',
            get_string('analysis_date', 'plagiarism_compilatio'),
            array('optional' => false));
        $mform->setDefault('time_analyse', time() + 7 * 24 * 3600);
        $mform->disabledif('time_analyse', 'analysis_type', 'noteq', 'planned');

        $lang = current_language();
        if ($lang == 'fr' && $CFG->version >= 2017111300) { // Method hideIf is available since moodle 3.4.
            $group = [];
            $group[] = $mform->createElement('static', 'calendar', '',
                "<img style='width: 45em;' src='https://content.compilatio.net/images/calendrier_affluence_magister.png'>");
            $mform->addGroup($group, 'calendargroup', '', ' ', false);
            $mform->hideIf('calendargroup', 'analysis_type', 'noteq', 'planned');
        }
    }

    $tiioptions = array(
        'never' => get_string("never"),
        'immediately' => get_string("immediately", "plagiarism_compilatio"),
        'closed' => get_string("showwhenclosed", "plagiarism_compilatio"),
    );

    $mform->addElement('select', 'show_student_score', get_string("compilatio_display_student_score", "plagiarism_compilatio"), $tiioptions);
    $mform->addHelpButton('show_student_score', 'compilatio_display_student_score', 'plagiarism_compilatio');
    if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") === '1') {
        $mform->addElement('select', 'show_student_report',
            get_string("compilatio_display_student_report", "plagiarism_compilatio"),
            $tiioptions);
        $mform->addHelpButton('show_student_report', 'compilatio_display_student_report', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }

    if (get_config("plagiarism_compilatio", "allow_student_analyses") === '1' && !$defaults) {
        if ($mform->elementExists('submissiondrafts')) {
            $mform->addElement('select', 'student_analyses',
                get_string("student_analyses", "plagiarism_compilatio"), $ynoptions);
            $mform->addHelpButton('student_analyses', 'student_analyses', 'plagiarism_compilatio');

            $plugincm = compilatio_cm_use($PAGE->context->instanceid);
            if ($plugincm["student_analyses"] === '0') {
                $mform->disabledif('student_analyses', 'submissiondrafts', 'eq', '0');
            }

            if ($CFG->version >= 2017111300) { // Method hideIf is available since moodle 3.4.
                $group = [];
                $group[] = $mform->createElement("html", "<p style='color: #b94a48;'>" .
                    get_string('activate_submissiondraft', 'plagiarism_compilatio',
                    get_string('submissiondrafts', 'assign')) .
                    " <b>" . get_string('submissionsettings', 'assign') . ".</b></p>");
                $mform->addGroup($group, 'activatesubmissiondraft', '', ' ', false);
                $mform->hideIf('activatesubmissiondraft', 'submissiondrafts', 'eq', '1');
            }
        }
    }

    $mform->addElement('select', 'student_email',
        get_string("student_email", "plagiarism_compilatio"), $ynoptions);
    $mform->addHelpButton('student_email', 'student_email', 'plagiarism_compilatio');

    // Indexing state.
    $mform->addElement('select',
        'indexing_state',
        get_string("indexing_state", "plagiarism_compilatio"),
        $ynoptions);
    $mform->addHelpButton('indexing_state',
        'indexing_state',
        'plagiarism_compilatio');
    $mform->setDefault('indexing_state', 1);

    // Threshold settings.
    $mform->addElement('html', '<p><strong>' . get_string("thresholds_settings", "plagiarism_compilatio") . '</strong></p>');
    $mform->addElement('html', '<p>' . get_string("thresholds_description", "plagiarism_compilatio") . '</p>');

    $mform->addElement('html', '<div>');
    $mform->addElement('text', 'green_threshold',
        get_string("green_threshold", "plagiarism_compilatio"),
        'size="5" id="green_threshold"');
    $mform->addElement('html', '<noscript>' . get_string('similarity_percent', "plagiarism_compilatio") . '</noscript>');

    $mform->addElement('text', 'orange_threshold',
        get_string("orange_threshold", "plagiarism_compilatio"),
        'size="5" id="orange_threshold"');
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
    $mform->addRule('green_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');
    $mform->addRule('orange_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');

    $mform->setType('green_threshold', PARAM_INT);
    $mform->setType('orange_threshold', PARAM_INT);

    $mform->setDefault('green_threshold', '10');
    $mform->setDefault('orange_threshold', '25');
}

/**
 * Get the plagiarism values for this course module
 *
 * @param  int          $cmid           Course module (cm) ID
 * @return array|false  $plag_values    Plagiarism values or false if the plugin is not enabled for this cm
 */
function compilatio_cm_use($cmid) {

    global $DB;
    static $plagvalues = array();
    if (!isset($plagvalues[$cmid])) {
        $r = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name,value');
        if (!empty($r['use_compilatio'])) {
            $plagvalues[$cmid] = $r;
        } else {
            $plagvalues[$cmid] = false;
        }
    }
    return $plagvalues[$cmid];
}

/**
 * Display the news of Compilatio
 *
 * @return array containing almerts according to the news in the DB.
 */
function compilatio_display_news() {

    global $DB;
    // Get the moodle language -> function used by "get_string" to define language.
    $language = current_language();

    $news = $DB->get_records_select('plagiarism_compilatio_news', 'end_display_on>? AND begin_display_on<?', array(time(), time()));

    $alerts = array();

    foreach ($news as $new) {
        $message = "";
        // Get the field matching the language, english by default.
        switch ($language) {
            case "fr":
                if (!$new->message_fr) {
                    $message = $new->message_en;
                } else {
                    $message = $new->message_fr;
                }
                break;
            default:
                $message = $new->message_en;
                break;
        }

        // Get the title of the notification according to the type of news:.
        $title = "";
        $class = "warning";
        switch ($new->type) {
            case CMP_NEWS_UPDATE:
                $title = get_string("news_update", "plagiarism_compilatio"); // Info.
                $class = "info";
                break;
            case CMP_NEWS_INCIDENT:
                $title = get_string("news_incident", "plagiarism_compilatio"); // Danger.
                $class = "danger";
                break;
            case CMP_NEWS_MAINTENANCE:
                $title = get_string("news_maintenance", "plagiarism_compilatio"); // Warning.
                $class = "warning";
                break;
            case CMP_NEWS_ANALYSIS_PERTURBATED:
                $title = get_string("news_analysis_perturbated", "plagiarism_compilatio"); // Danger.
                $class = "danger";
                break;
        }

        $alerts[] = array(
            "class" => $class,
            "title" => $title,
            "content" => $message,
        );
    }

    return $alerts;

    define('CMP_NEWS_UPDATE', 1);
    define('CMP_NEWS_INCIDENT', 2);
    define('CMP_NEWS_MAINTENANCE', 3);
    define('CMP_NEWS_ANALYSIS_PERTURBATED', 4);
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
    $conditions = array('cm' => $cmid, 'name' => 'use_compilatio');
    $cmenabled = $DB->get_field('plagiarism_compilatio_config', 'value', $conditions);

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

    global $SESSION;

    if (class_exists('\tool_recyclebin\course_bin') && \tool_recyclebin\category_bin::is_enabled()) {
        $SESSION->compilatio_course_deleted_id = $course->id;
    } else {
        compilatio_course_delete($course->id);
    }
}

/**
 * Delete files of a course.
 * @param int    $courseid   Course identifier.
 * @param string $modulename Module name (e.g : assign).
 */
function compilatio_course_delete($courseid, $modulename = null) {

    global $DB;

    $files = array();

    $sql = '
        SELECT cm
        FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
        JOIN {course_modules} course_modules
            ON plagiarism_compilatio_files.cm = course_modules.id';

    $conditions = array();
    $conditions['courseid'] = $courseid;

    if (null !== $modulename) {
        $sql .= '
            JOIN {modules} modules
                ON modules.id = course_modules.module
            WHERE course_modules.course = :courseid
            AND modules.name = :modulename';
        $conditions['modulename'] = $modulename;
    } else {
        $sql .= '
            WHERE course_modules.course = :courseid';
    }

    $coursemodules = $DB->get_records_sql($sql, $conditions);

    foreach ($coursemodules as $coursemodule) {
        $files = $DB->get_records('plagiarism_compilatio_files', array('cm' => $coursemodule->cm));
        compilatio_delete_files($files);
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
 * @return boolean true if all documents have been processed, false otherwise
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
 * @param  int  $studentanalysesparam Value of the parameter student_analyses for the cm
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
        if ($status == 'draft') {
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

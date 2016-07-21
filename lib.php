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
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

// Get global class.
global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');

define('COMPILATIO_MAX_SUBMISSION_ATTEMPTS', 6); // Max num to try and send a submission to Compilatio.
define('COMPILATIO_MAX_SUBMISSION_DELAY', 60); // Max time to wait between submissions (defined in minutes).
define('COMPILATIO_SUBMISSION_DELAY', 15); // Initial wait time, doubled each time until max_submission_delay is met.
define('COMPILATIO_MAX_STATUS_ATTEMPTS', 10); // Maximum number of times to try and obtain the status of a submission.
define('COMPILATIO_MAX_STATUS_DELAY', 1440); // Maximum time to wait between checks (defined in minutes).
define('COMPILATIO_STATUS_DELAY', 10); // Initial wait time, doubled each time a until the max_status_delay is met.

define('COMPILATIO_STATUSCODE_ACCEPTED', '202');
define('COMPILATIO_STATUSCODE_ANALYSING', '203');
define('COMPILATIO_STATUSCODE_BAD_REQUEST', '400');
define('COMPILATIO_STATUSCODE_NOT_FOUND', '404');
define('COMPILATIO_STATUSCODE_UNSUPPORTED', '415');
define('COMPILATIO_STATUSCODE_UNEXTRACTABLE', '416');
define('COMPILATIO_STATUSCODE_TOO_LARGE', '413');
define('COMPILATIO_STATUSCODE_COMPLETE', 'Analyzed');
define('COMPILATIO_STATUSCODE_IN_QUEUE', 'In queue');

define('COMPILATIO_ANALYSISTYPE_AUTO', 0); // File shoud be processed as soon as the file is sent.
define('COMPILATIO_ANALYSISTYPE_MANUAL', 1); // File processed when teacher manually decides to.
define('COMPILATIO_ANALYSISTYPE_PROG', 2); // File processed on set time/date.

define('PLAGIARISM_COMPILATIO_SHOW_NEVER', 0);
define('PLAGIARISM_COMPILATIO_SHOW_ALWAYS', 1);
define('PLAGIARISM_COMPILATIO_SHOW_CLOSED', 2);

define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE', 0);
define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL', 1);

define('PLAGIARISM_COMPILATIO_NEWS_UPDATE', 1);
define('PLAGIARISM_COMPILATIO_NEWS_INCIDENT', 2);
define('PLAGIARISM_COMPILATIO_NEWS_MAINTENANCE', 3);
define('PLAGIARISM_COMPILATIO_NEWS_ANALYSIS_PERTURBATED', 4);

// Compilatio Class.
class plagiarism_plugin_compilatio extends plagiarism_plugin {

    // Used to store cache data about Compilatio module thresholds.
    private $_green_threshold_cache = null;
    private $_orange_threshold_cache = null;

    /**
     * This function should be used to initialise settings and check if plagiarism is enabled.
     * *
     * @return mixed - false if not enabled, or returns an array of relevant settings.
     */
    public function get_settings() {
        static $plagiarismsettings;
        if (!empty($plagiarism_settings) || $plagiarismsettings === false) {
            return $plagiarismsettings;
        }
        $plagiarismsettings = (array) get_config('plagiarism');
        // Check if compilatio enabled.
        if (isset($plagiarismsettings['compilatio_use']) && $plagiarismsettings['compilatio_use']) {
            // Now check to make sure required settings are set!
            if (empty($plagiarismsettings['compilatio_api'])) {
                error("Compilatio API URL not set!");
            }
            return $plagiarismsettings;
        } else {
            return false;
        }
    }

    /**
     * function which returns an array of all the module instance settings.
     *
     * @return array
     *
     */
    public function config_options() {
        return array('use_compilatio', 'compilatio_show_student_score',
            'compilatio_show_student_report',
            'compilatio_draft_submit',
            'compilatio_studentemail',
            'compilatio_timeanalyse',
            'compilatio_analysistype',
            'green_threshold',
            'orange_threshold');
    }

    public function cron() {
        /* Deprecated for TaskAPI - https://docs.moodle.org/dev/Plagiarism_plugins#cron
          "This function was deprecated in 3.1, Moodle 2.7 added the new Task_API
          which should now be used instead.
          in versions prior to 3.1 this function must exist within your local
          class but can be empty if you have migrated to using the newer Task_API"
         */
        global $CFG;
        if ($CFG->version < 2014051200) { // Legacy cron for Moodle<2.7
            $plagiarismsettings = $this->get_settings();
            compilatio_send_pending_files($plagiarismsettings);
            compilatio_get_scores($plagiarismsettings);
            compilatio_trigger_timed_analyses();
            compilatio_update_meta();
        }
    }

    /**
     * hook to allow plagiarism specific information to be displayed beside a submission.
     * @param array $linkarray contains all relevant information for the plugin to generate a link.
     * @return string
     *
     */
    public function get_links($linkarray) {
        global $DB, $COURSE, $CFG, $PAGE;
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];

        if (!empty($linkarray['content'])) {
            $filename = "content-" . $COURSE->id . "-" . $cmid . "-" . $userid . ".htm";
            $filepath = $CFG->dataroot . "/temp/compilatio/" . $filename;
            $file = new stdclass();
            $file->type = "tempcompilatio";
            $file->filename = $filename;
            $file->timestamp = time();
            $file->identifier = null;
            $file->filepath = $filepath;
        } else if (!empty($linkarray['file'])) {
            $file = new stdclass();
            $file->filename = $linkarray['file']->get_filename();
            $file->timestamp = time();
            $file->identifier = $linkarray['file']->get_contenthash();
            $file->filepath = $linkarray['file']->get_filepath();
        }
        $results = $this->get_file_results($cmid, $userid, $file);
        $output = '';
        $modulecontext = context_module::instance($cmid);
        if (empty($results)) {

            // Check if Compilatio is enabled in this assignment.
            $sql = "select value from {plagiarism_compilatio_config} where cm=? and name='use_compilatio'";
            $active_compilatio = $DB->get_record_sql($sql, array($cmid));

            if ($active_compilatio === false || $active_compilatio->value === '0') {
                return "";
            }
            // If the user has permission to see result of all items in this course module.

            $teacher = has_capability('plagiarism/compilatio:viewreport', $modulecontext);

            if (!$teacher) {
                return '';
            } else {
                // Only works for assign.
                $cm = get_coursemodule_from_id('assign', $cmid);
                if (!isset($linkarray["file"]) || $cm === false) {
                    return $output;
                }

                $trigger = optional_param('sendfile', 0, PARAM_INT);

                $fileInstance = $linkarray["file"];
                $fileID = $fileInstance->get_id();

                if ($trigger == $fileID) {
                    $res = $DB->get_record("files", array("id" => $fileID));

                    if (!defined("COMPILATIO_MANUAL_SEND")) {
                        define("COMPILATIO_MANUAL_SEND", true); // hack to hide mtrace in function execution.
                        compilatio_upload_files(array($res), $cmid);
                        return $output . $this->get_links($linkarray);
                    } else {
                        return $output;
                    }
                }

                $moodle_url = new moodle_url("/mod/assign/view.php", array("id" => $cmid, "sendfile" => $fileID, "action" => "grading"));
                $url = array();
                $url["url"] = "$moodle_url";
                $url["target-blank"] = false;


                $spanContent = get_string("analyze", "plagiarism_compilatio");
                $image = "play";
                $title = get_string('startanalysis', 'plagiarism_compilatio');

                $output .= compilatio_get_plagiarism_area($spanContent, $image, $title, "", $url);

                return $output;
            }
            // Info about this file is not available to this user.
            return $output;
        }

        $trigger = optional_param('compilatioprocess', 0, PARAM_INT);

        if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED && $trigger == $results['pid']) {
            if (has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext)) {
                // Trigger manual analysis call.
                $plagiarism_file = compilatio_get_plagiarism_file($cmid, $userid, $file);

                $analyse = compilatio_startanalyse($plagiarism_file);
                if ($analyse === true) {
                    // Update plagiarism record.
                    $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
                    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);

                    $spanContent = get_string("queue", "plagiarism_compilatio");
                    $image = "queue";
                    $title = get_string('queued', 'plagiarism_compilatio');
                    $output.= compilatio_get_plagiarism_area($spanContent, $image, $title, "");
                } else {
                    $output .= '<span class="plagiarismreport">' .
                            '</span>';
                }
                return $output;
            }
        }
        if ($results['statuscode'] == 'pending') {

            $spanContent = get_string("pending_status", "plagiarism_compilatio");
            $image = "hourglass";
            $title = get_string('pending', 'plagiarism_compilatio');
            $output.= compilatio_get_plagiarism_area($spanContent, $image, $title, "");

            return $output;
        }
        if ($results['statuscode'] == 'Analyzed') {
            // Normal situation - Compilatio has successfully analyzed the file.
            // Cache Thresholds values.
            if ($this->_green_threshold_cache == null || $this->_orange_threshold_cache == null) {
                $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');

                if (isset($plagiarismvalues["green_threshold"], $plagiarismvalues["orange_threshold"])) {
                    $this->_green_threshold_cache = $plagiarismvalues["green_threshold"];
                    $this->_orange_threshold_cache = $plagiarismvalues["orange_threshold"];
                } else {
                    $this->_green_threshold_cache = 10;
                    $this->_orange_threshold_cache = 25;
                }
            }

            $url = "";
            $append = "";
            if (!empty($results['reporturl'])) {
                // User is allowed to view the report
                // Score is contained in report, so they can see the score too.

                $url = $results['reporturl'];

                $append = compilatio_get_image_similarity($results['score'], $this->_green_threshold_cache, $this->_orange_threshold_cache);
            } else if ($results['score'] !== '') {
                // User is allowed to view only the score.


                $append = compilatio_get_image_similarity($results['score'], $this->_green_threshold_cache, $this->_orange_threshold_cache);
            }
            $title = get_string("analysis_completed", 'plagiarism_compilatio', $results['score']);
            $url = array("target-blank" => true, "url" => $url);
            $output.= compilatio_get_plagiarism_area("", "", $title, $append, $url);

            if (!empty($results['renamed'])) {
                $output .= $results['renamed'];
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_IN_QUEUE) {


            $spanContent = get_string("queue", "plagiarism_compilatio");
            $image = "queue";
            $title = get_string('queued', 'plagiarism_compilatio');
            $output.= compilatio_get_plagiarism_area($spanContent, $image, $title, "");
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
            $title = "";
            $span = "";
            $url = "";
            $image = "";

            // Check settings to see if we need to tell compilatio to process this file now.
            // Check if this is a timed release and add hourglass image.
            if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_PROG) {

                $image = "prog";
                $span = get_string('planned', 'plagiarism_compilatio');

                $title = get_string('waitingforanalysis', 'plagiarism_compilatio', userdate($plagiarismvalues['compilatio_timeanalyse']));
            } else if (has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext)) {
                $url = new moodle_url($PAGE->url, array('compilatioprocess' => $results['pid']));
                $action = optional_param('action', '', PARAM_TEXT); // Hack to add action to params for mod/assign.
                if (!empty($action)) {
                    $url->param('action', $action);
                }
                $url = "$url";
                $span = get_string("analyze", "plagiarism_compilatio");
                $image = "play";
                $title = get_string('startanalysis', 'plagiarism_compilatio');
            } else if ($results['score'] !== '') {//If score === "" => Student, not allowed to see.
                $image = "inprogress";
                $title = get_string('processing_doc', 'plagiarism_compilatio');
            }
            if ($title !== "") {
                $url = array("target-blank" => false, "url" => $url);
                $output .= compilatio_get_plagiarism_area($span, $image, $title, "", $url);
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ANALYSING) {
            $span = get_string("analyzing", "plagiarism_compilatio");
            $image = "inprogress";
            $title = get_string('processing_doc', 'plagiarism_compilatio');
            $output .= compilatio_get_plagiarism_area($span, $image, $title);
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNSUPPORTED) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unsupportedfiletype', 'plagiarism_compilatio');
            $output .= compilatio_get_plagiarism_area($span, $image, $title, "", "", true);
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_LARGE) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('toolarge', 'plagiarism_compilatio');
            $output .= compilatio_get_plagiarism_area($span, $image, $title, "", "", true);
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNEXTRACTABLE) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unextractablefile', 'plagiarism_compilatio');
            $output .= compilatio_get_plagiarism_area($span, $image, $title, "", "", true);
        } else {
            $title = get_string('unknownwarning', 'plagiarism_compilatio');
            $reset = '';
            $url = "";
            if (has_capability('plagiarism/compilatio:resetfile', $modulecontext) &&
                    !empty($results['error'])) { // This is a teacher viewing the responses.
                // Strip out some possible known text to tidy it up.
                $erroresponse = format_text($results['error'], FORMAT_PLAIN);
                $erroresponse = str_replace('{&quot;LocalisedMessage&quot;:&quot;', '', $erroresponse);
                $erroresponse = str_replace('&quot;,&quot;Message&quot;:null}', '', $erroresponse);
                $title .= ': ' . $erroresponse;
                $url = new moodle_url('/plagiarism/compilatio/reset.php', array('cmid' => $cmid, 'pf' => $results['pid'], 'sesskey' => sesskey()));
                $reset = "<a class='reinit' href='$url'>" . get_string('reset') . "</a>";
            }
            $span = get_string('reset', "plagiarism_compilatio");
            $url = array("target-blank" => false, "url" => $url);
            $image = "exclamation";
            $output .= compilatio_get_plagiarism_area($span, $image, $title, "", $url, true);
        }
        return $output;
    }

    public function get_file_results($cmid, $userid, $file) {
        global $DB, $USER, $CFG;
        $plagiarismsettings = $this->get_settings();
        if (empty($plagiarismsettings)) {
            // Compilatio is not enabled.
            return false;
        }
        $plagiarismvalues = compilatio_cm_use($cmid);
        if (empty($plagiarismvalues)) {
            // Compilatio not enabled for this cm.
            return false;
        }

        // Collect detail about the specified coursemodule.
        $filehash = $file->identifier;
        $modulesql = 'SELECT m.id, m.name, cm.instance' .
                ' FROM {course_modules} cm' .
                ' INNER JOIN {modules} m on cm.module = m.id ' .
                'WHERE cm.id = ?';
        $moduledetail = $DB->get_record_sql($modulesql, array($cmid));

        if (!empty($moduledetail)) {
            $sql = "SELECT * FROM " . $CFG->prefix . $moduledetail->name . " WHERE id= ?";
            $module = $DB->get_record_sql($sql, array($moduledetail->instance));
        }

        if (empty($module)) {
            // No such cmid.
            return false;
        }

        $modulecontext = context_module::instance($cmid);
        // If the user has permission to see result of all items in this course module.
        $viewscore = $viewreport = has_capability('plagiarism/compilatio:viewreport', $modulecontext);

        // Determine if the activity is closed.
        // If report is closed, this can make the report available to more users.
        $assignclosed = false;
        $time = time();
        if (!empty($module->preventlate) && !empty($module->timedue)) {
            $assignclosed = ($module->timeavailable <= $time && $time <= $module->timedue);
        } else if (!empty($module->timeavailable)) {
            $assignclosed = ($module->timeavailable <= $time);
        }

        // Under certain circumstances, users are allowed to see plagiarism info
        // even if they don't have view report capability.
        if ($USER->id == $userid) {
            $selfreport = true;
            if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") === '1' &&
                    isset($plagiarismvalues['compilatio_show_student_report']) &&
                    ($plagiarismvalues['compilatio_show_student_report'] == PLAGIARISM_COMPILATIO_SHOW_ALWAYS ||
                    $plagiarismvalues['compilatio_show_student_report'] == PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
                $viewreport = true;
            }
            if (isset($plagiarismvalues['compilatio_show_student_score']) &&
                    ($plagiarismvalues['compilatio_show_student_score'] == PLAGIARISM_COMPILATIO_SHOW_ALWAYS) ||
                    ($plagiarismvalues['compilatio_show_student_score'] == PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
                $viewscore = true;
            }
        } else {
            $selfreport = false;
        }
        // End of rights checking.

        if (!$viewscore && !$viewreport && $selfreport) {
            // User is not permitted to see any details.
            return false;
        }
        if ($filehash != null) {
            $plagiarismfile = $DB->get_record_sql(
                    "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND " .
                    "identifier = ?", array($cmid, $userid, $filehash));
        } else {
            // We don't have the hash of the content-submission, get it by name
            $plagiarismfile = $DB->get_record_sql(
                    "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND " .
                    "filename = ?", array($cmid, $userid, $file->filename));
        }

        if (empty($plagiarismfile)) {
            // No record of that submitted file.
            return false;
        }

        // Returns after this point will include a result set describing information about
        // interactions with compilatio servers.
        $results = array('statuscode' => '', 'error' => '', 'reporturl' => '',
            'score' => '', 'pid' => '', 'renamed' => '',
            'analyzed' => 0,
        );

        if ($plagiarismfile->statuscode == 'pending') {
            $results['statuscode'] = 'pending';
            return $results;
        }

        // Now check for differing filename and display info related to it.
        $previouslysubmitted = '';
        if ($file->filename !== $plagiarismfile->filename) {
            $previouslysubmitted = '<span class="prevsubmitted">(' . get_string('previouslysubmitted', 'plagiarism_compilatio') .
                    ': ' . $plagiarismfile->filename . ')</span>';
        }

        $results['statuscode'] = $plagiarismfile->statuscode;
        $results['pid'] = $plagiarismfile->id;
        $results['error'] = $plagiarismfile->errorresponse;
        if ($plagiarismfile->statuscode == 'Analyzed') {
            $results['analyzed'] = 1;
            // File has been successfully analyzed - return all appropriate details.
            if ($viewscore || $viewreport) {
                // If user can see the report, they can see the score on the report
                // so make it directly available.
                $results['score'] = $plagiarismfile->similarityscore;
            }
            if ($viewreport) {
                $results['reporturl'] = $plagiarismfile->reporturl;
            }
            $results['renamed'] = $previouslysubmitted;
        }
        return $results;
    }

    /* hook to save plagiarism specific settings on a module settings page.
     * @param object $data - data from an mform submission.
     */

    public function save_form_elements($data) {
        global $DB;
        if (!$this->get_settings()) {
            return;
        }
        if (isset($data->use_compilatio)) {
            // Array of possible plagiarism config options.
            $plagiarismelements = $this->config_options();

            //Validation on thresholds : 
            //Set thresholds to default if the green one is greater than the orange.
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

            if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") !== '1') {
                $data->compilatio_show_student_report = PLAGIARISM_COMPILATIO_SHOW_NEVER;
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
            //check if we are changing from timed or manual to instant
            //if changing to instant, make all existing files to get a report.
            if (isset($existingelements['compilatio_analysistype']) && $existingelements['compilatio_analysistype'] !== $data->compilatio_analysistype &&
                    $data->compilatio_analysistype == COMPILATIO_ANALYSISTYPE_AUTO) {
                //get all existing files in this assignment set to manual status
                $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files', array('cm' => $data->coursemodule, 'statuscode' => COMPILATIO_STATUSCODE_ACCEPTED));
                compilatio_analyse_files($plagiarismfiles);
            }
        }
    }

    /**
     * hook to add plagiarism specific settings to a module settings page.
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context, $modulename = "") {
        global $DB;
        $plagiarismsettings = $this->get_settings();
        if (!$plagiarismsettings) {
            return;
        }
        // Hack to prevent this from showing on custom compilatioassignment type.
        if ($mform->elementExists('seuil_faible')) {
            return;
        }
        $cmid = optional_param('update', 0, PARAM_INT); // We can't access $this->_cm here.
        if (!empty($modulename)) {
            $modname = 'compilatio_enable_' . $modulename;
            if (empty($plagiarismsettings[$modname])) {
                return; // Return if compilatio is not enabled for the module
            }
        }
        if (!empty($cmid)) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
        }
        // The cmid(0) is the default list.
        $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => 0), '', 'name, value');
        $plagiarismelements = $this->config_options();
        if (has_capability('plagiarism/compilatio:enable', $context)) {
            compilatio_get_form_elements($mform);
            if ($mform->elementExists('compilatio_draft_submit')) {
                if ($mform->elementExists('var4')) {
                    $mform->disabledIf('compilatio_draft_submit', 'var4', 'eq', 0);
                } else if ($mform->elementExists('submissiondrafts')) {
                    $mform->disabledIf('compilatio_draft_submit', 'submissiondrafts', 'eq', 0);
                }
            }
            // Disable all plagiarism elements if use_plagiarism eg 0.
            foreach ($plagiarismelements as $element) {
                if ($element <> 'use_compilatio') { // Ignore this var.
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
     * hook to allow a disclosure to be printed notifying users what will happen with their submission.
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $OUTPUT;

        $outputhtml = '';

        $compilatiouse = compilatio_cm_use($cmid);
        $plagiarismsettings = $this->get_settings();
        if (!empty($plagiarismsettings['compilatio_student_disclosure']) &&
                !empty($compilatiouse)) {
            $outputhtml .= $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $outputhtml .= format_text($plagiarismsettings['compilatio_student_disclosure'], FORMAT_MOODLE, $formatoptions);
            $outputhtml .= $OUTPUT->box_end();
        }
        return $outputhtml;
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     * @return string
     */
    public function update_status($course, $cm) {
        global $PAGE, $OUTPUT, $DB;

        $alerts = array();

        $output = '';
        //Handle the action of the button.
        $update = optional_param('compilatioupdate', '', PARAM_BOOL);
        if ($update) {
            $sql = "cm = ? AND externalid IS NOT NULL";
            $params = array($cm->id);
            $plagiarism_files = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);
            foreach ($plagiarism_files as $pf) {
                compilatio_check_analysis($pf, true);
            }

            $alerts[] = array(
                "class" => "success",
                "title" => get_string('updated_analysis', 'plagiarism_compilatio'),
                "content" => "");
        }

        $export = optional_param('compilatio_export', '', PARAM_BOOL);
        if ($export) {
            compilatio_csv_export($cm->id);
        }

        //Handle the action of the button when course is set on manual analysis
        $startAllAnalysis = optional_param('compilatiostartanalysis', '', PARAM_BOOL);
        if ($startAllAnalysis) {
            $sql = "cm = ? AND name='compilatio_analysistype'";
            $params = array($cm->id);
            $record = $DB->get_record_select('plagiarism_compilatio_config', $sql, $params);

            //Counter incremented on success
            $countSuccess = 0;
            $plagiarism_files = array();
            $docsFailed = array();
            if ($record != null && $record->value == COMPILATIO_ANALYSISTYPE_MANUAL) {
                $sql = "cm = ? AND statuscode = ?";
                $params = array($cm->id, COMPILATIO_STATUSCODE_ACCEPTED);
                $plagiarism_files = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);


                foreach ($plagiarism_files as $file) {
                    if (compilatio_startanalyse($file)) {
                        $countSuccess++;
                    } else {
                        $docsFailed[] = $file["filename"];
                    }
                }
            }


            //Handle not sent documents :
            $files = compilatio_get_non_uploaded_documents($cm->id);
            $countBegin = count($files);
            if ($countBegin != 0) {
                define("COMPILATIO_MANUAL_SEND", true);
                compilatio_upload_files($files, $cm->id);
                $countSuccess += $countBegin - count(compilatio_get_non_uploaded_documents($cm->id));
            }

            $countTotal = count($plagiarism_files) + $countBegin;
            $countErrors = count($docsFailed);
            if ($countTotal === 0) {
                $alerts[] = array(
                    "class" => "info",
                    "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
                    "content" => get_string("no_document_available_for_analysis", "plagiarism_compilatio"));
            } elseif ($countErrors === 0) {
                $alerts[] = array(
                    "class" => "info",
                    "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
                    "content" => get_string("analysis_started", "plagiarism_compilatio", $countSuccess));
            } else {

                $alerts[] = array(
                    "class" => "danger",
                    "title" => get_string("not_analyzed", "plagiarism_compilatio"),
                    "content" => "<ul><li>" . implode("</li><li>", $docsFailed) . "</li></ul>"
                );
            }
            //$output .= $OUTPUT->notification(get_string('manual_global_analysis', 'plagiarism_compilatio'), 'notifysuccess');
        }

        $plagiarismsettings = (array) get_config('plagiarism');
        $compilatio_enabled = $plagiarismsettings["compilatio_use"] && $plagiarismsettings["compilatio_enable_mod_assign"];


        $sql = "select value from {plagiarism_compilatio_config} where cm=? and name='use_compilatio'";
        $active_compilatio = $DB->get_record_sql($sql, array($cm->id));
        //Compilatio not enabled, return.

        if ($active_compilatio === false) {
            //Plagiarism settings have not been saved :
            $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => 0), '', 'name, value');

            $plagiarismelements = $this->config_options();
            foreach ($plagiarismelements as $element) {
                if (isset($plagiarismdefaults[$element])) {
                    $newelement = new Stdclass();
                    $newelement->cm = $cm->id;
                    $newelement->name = $element;
                    $newelement->value = $plagiarismdefaults[$element];

                    $DB->insert_record('plagiarism_compilatio_config', $newelement);
                }
            }
            //Get the new status
            $active_compilatio = $DB->get_record_sql($sql, array($cm->id));
        }


        if ($active_compilatio == null || $active_compilatio->value != 1 || !$compilatio_enabled) {
            return "";
        }

        //Get compilatio analysis type
        $sql = "cm = ? AND name='compilatio_analysistype'";
        $params = array($cm->id);
        $record = $DB->get_record_select('plagiarism_compilatio_config', $sql, $params);
        $value = $record->value;

        if ($value == COMPILATIO_ANALYSISTYPE_MANUAL) {//Display a button that start all the analysis of the activity
            $url = $PAGE->url;
            $url->param('compilatiostartanalysis', true);
            $StartAllAnalysisButton = "
			<a href='$url' class='compilatio-button button' >
				<i class='fa fa-play-circle'>
				</i>
				" . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
			</a>";
        } else if ($value == COMPILATIO_ANALYSISTYPE_PROG) {//Display the date of analysis if its type is set on 'Timed'.
            //Get analysis date :
            $sql = "cm = ? AND name='compilatio_timeanalyse'";
            $params = array($cm->id);
            $plagiarism_files = $DB->get_records_select('plagiarism_compilatio_config', $sql, $params);
            $record = reset($plagiarism_files); //Get the first value of the array
            $date = userdate($record->value);
            if ($record->value > time()) {
                $programmedAnalysisDate = get_string("programmed_analysis_future", "plagiarism_compilatio", $date);
            } else {
                $programmedAnalysisDate = get_string("programmed_analysis_past", "plagiarism_compilatio", $date);
            }
        }


        //Get the DB record containing the webservice status :
        $oldConnectionStatus = $DB->get_record('plagiarism_compilatio_data', array('name' => 'connection_webservice'));
        //If the record exists and if the webservice is marked as unreachable in Cron function :
        if ($oldConnectionStatus != null && $oldConnectionStatus->value === '0') {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("webservice_unreachable_title", "plagiarism_compilatio"),
                "content" => get_string("webservice_unreachable_content", "plagiarism_compilatio"));
        }
        //Display a notification of the unsupported files
        $files = compilatio_get_unsupported_files($cm->id);
        if (count($files) !== 0) {
            $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsupported_files", "plagiarism_compilatio"),
                "content" => $list
            );
        }
        //Display a notification form the unextractable files
        $files = compilatio_get_unextractable_files($cm->id);
        if (count($files) !== 0) {
            $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unextractable_files", "plagiarism_compilatio"),
                "content" => $list
            );
        }
        //If the account expires within the month, display an alert :
        if (compilatio_check_account_expiration_date()) {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("account_expire_soon_title", "plagiarism_compilatio"),
                "content" => get_string("account_expire_soon_content", "plagiarism_compilatio")
            );
        }

        $documentsNotUploaded = compilatio_get_non_uploaded_documents($cm->id);
        if (count($documentsNotUploaded) !== 0) {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsent_documents", "plagiarism_compilatio"),
                "content" => get_string("unsent_documents_content", "plagiarism_compilatio"));

            $url = $PAGE->url;
            $url->param('compilatiostartanalysis', true);
            $StartAllAnalysisButton = "
			<a href='$url' class='compilatio-button button' >
				<i class='fa fa-play-circle'>
				</i>
				" . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
			</a>";
        }

        //Add the Compilatio news to the alerts displayed :
        $alerts = array_merge($alerts, compilatio_display_news());


        $jquery_url = new moodle_url("/plagiarism/compilatio/jquery.min.js");
        $fontawesome_url = new moodle_url("/plagiarism/compilatio/fonts/font-awesome.min.css");

        //Include JQuery & FontAwesome
        $output .= "<script src='$jquery_url'></script>";
        $output .= "<link rel='stylesheet' href='$fontawesome_url'>";

        $output .= "<div id='compilatio-container'>";


        $output .= compilatio_get_logo();

        //Display the tabs: Notification tab will be hidden if there is 0 alerts.
        $output .= "<div id='compilatio-tabs' style='display:none'>";
        $output .= "<div title=\"" . get_string("compilatio_help_assign", "plagiarism_compilatio") . "\" id='show-help' class='compilatio-icon'><i class='fa fa-question-circle fa-2x'></i></div>";

        $output .= "<div id='show-stats' class='compilatio-icon'  title='" . get_string("display_stats", "plagiarism_compilatio") . "'><i class='fa fa-bar-chart fa-2x'></i></div>";

        if (count($alerts) !== 0) {
            $output .= "<div id='show-notifications' title='" . get_string("display_notifications", "plagiarism_compilatio") . "'
					class='compilatio-icon active' ><i class='fa fa-bell fa-2x'></i>";
            $output .= "<span>" . count($alerts) . "</span>";
            $output .= "</div>";
        }


        $output .= "<div id='compilatio-hide-area' class='compilatio-icon'  title='" . get_string("hide_area", "plagiarism_compilatio") . "'><i class='fa fa-chevron-up fa-2x'></i></div>";


        $output .= "</div>";


        $output .= "<script>";
        //Focus on notifications if there is any.
        $output .= "var selectedElement = ";
        if (count($alerts) !== 0) {
            $output .= "'#compilatio-notifications';";
        } else {
            $output .= "'#compilatio-home';";
        }

        //JQuery Script to handle click on the tabs
        $output .= "$(document).ready(function(){
                            $('#compilatio-tabs').show();

                            var tabs = $('#show-notifications, #show-stats, #show-help');
                            var elements = $('#compilatio-notifications, #compilatio-stats, #compilatio-help, #compilatio-home');

                            elements.not($(selectedElement)).hide();

                            $('#show-notifications').on('click',function(){
                                    tabClick($(this), $('#compilatio-notifications'));
                            });
                            $('#show-stats').on('click',function(){
                                    tabClick($(this), $('#compilatio-stats'));
                            });
                            $('#show-help').on('click',function(){
                                    tabClick($(this), $('#compilatio-help'));
                            });

                            function tabClick(tabClicked, contentToShow)
                            {
                                    if(!contentToShow.is(':visible'))
                                    {
                                            contentToShow.show();

                                            elements.not(contentToShow).hide();

                                            tabs.not(tabClicked).removeClass('active');

                                            tabClicked.toggleClass('active');
                                            $('#compilatio-hide-area').fadeIn();
                                    }
                            }


                            $('#compilatio-logo').on('click',function(){
                                    elementClicked = $('#compilatio-home');
                                    elementClicked.show();
                                    elements.not(elementClicked).hide();
                                    tabs.removeClass('active');
                                    $('#compilatio-hide-area').fadeIn();
                            });
                            $('#compilatio-hide-area').on('click',function(event){
                                    elements.hide();
                                    $(this).fadeOut();
                                    tabs.removeClass('active');
                            });

                    });
            </script>";




        $output .= "<div class='clear'></div>";

        //Home tab
        $output .= "
			<div id='compilatio-home'>
				<p style='margin-top: 15px;'>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>
			</div>";

        //Alerts tab
        if (count($alerts) !== 0) {
            $output .= "<div id='compilatio-notifications'>";
            $output .= "<h5>" . get_string("tabs_title_notifications", "plagiarism_compilatio") . " : </h5>";
            foreach ($alerts as $alert) {
                $output .= "<div class='alert alert-" . $alert["class"] . "'><strong>" . $alert["title"] . "</strong><br/>" . $alert["content"] . "</div>";
            }
            $output .= "</div>";
        }

        //Stats tab
        $output .= "<div id='compilatio-stats'>
						<h5>" . get_string("tabs_title_stats", "plagiarism_compilatio") . " : </h5>
						" . compilatio_get_statistics($cm->id) . "
						</div>";
        //Help tab
        $output .= "<div id='compilatio-help'>
						<h5>" . get_string("tabs_title_help", "plagiarism_compilatio") . " : </h5>
						" . compilatio_display_help() . "
						</div>";

        //Display timed analysis date :
        if (isset($programmedAnalysisDate)) {
            $output .= "<p id='programmed-analysis'>$programmedAnalysisDate</p>";
        }



        $output .= "</div>";

        //Display buttons : 
        $output .= "<div id='button-container'>";

        //Update button
        $url = $PAGE->url;
        $url->param('compilatioupdate', true);
        $output .= "
		<a class='compilatio-button button' href='$url'>
				<i class='fa fa-refresh'></i>
				" . get_string('updatecompilatioresults', 'plagiarism_compilatio') . "
		</a>";
        //Start all analysis button.
        if (isset($StartAllAnalysisButton)) {
            $output .= $StartAllAnalysisButton;
        }

        $output .= "</div>";

        return $output;
    }

    public function compilatio_send_student_email($plagiarism_file) {
        global $DB, $CFG;
        if (empty($plagiarism_file->userid)) { // Sanity check.
            return false;
        }
        $user = $DB->get_record('user', array('id' => $plagiarism_file->userid));
        $site = get_site();
        $a = new stdClass();
        $cm = get_coursemodule_from_id('', $plagiarism_file->cm);
        $a->modulename = format_string($cm->name);
        $a->modulelink = $CFG->wwwroot . '/mod/' . $cm->modname . '/view.php?id=' . $cm->id;
        $a->coursename = format_string($DB->get_field('course', 'fullname', array('id' => $cm->course)));
        $emailsubject = get_string('studentemailsubject', 'plagiarism_compilatio');
        $emailcontent = get_string('studentemailcontent', 'plagiarism_compilatio', $a);
        email_to_user($user, $site->shortname, $emailsubject, $emailcontent);
    }

}

function compilatio_trigger_timed_analyses() {
    global $DB;

    // Now check for any assignments with a scheduled processing time that is after now.
    $sql = "SELECT cf.* FROM {plagiarism_compilatio_files} cf
                LEFT JOIN {plagiarism_compilatio_config} cc1 ON cc1.cm = cf.cm
                LEFT JOIN {plagiarism_compilatio_config} cc2 ON cc2.cm = cf.cm
                LEFT JOIN {plagiarism_compilatio_config} cc3 ON cc3.cm = cf.cm
                WHERE cf.statuscode = '" . COMPILATIO_STATUSCODE_ACCEPTED . "'
                AND cc1.name = 'use_compilatio' AND cc1.value='1'
                AND cc2.name = 'compilatio_analysistype' AND cc2.value = '" . COMPILATIO_ANALYSISTYPE_PROG . "'
                AND cc3.name = 'compilatio_timeanalyse'
                AND " . $DB->sql_cast_char2int('cc3.value') . " < ?";
    $plagiarismfiles = $DB->get_records_sql($sql, array(time()));
    compilatio_analyse_files($plagiarismfiles);
}

function compilatio_update_meta() {
    global $DB;

    //Send data about plugin version to Compilatio
    compilatio_send_statistics();

    //Update the expiration date in the DB
    compilatio_update_account_expiration_date();

    //Get most recent news from Compilatio :
    compilatio_update_news();

    //Update the "Compilatio unavailable" marker in the database
    compilatio_update_connection_status();


    // Do any scheduled task stuff.
    compilatio_update_allowed_filetypes();
}

function compilatio_send_pending_files($plagiarismsettings) {
    $fs = get_file_storage();
    global $DB;

    $lastCron = compilatio_update_cron_frequency();
    // Keep track of the last datetime of execution
    compilatio_update_last_cron_date($lastCron);

    if (!empty($plagiarismsettings)) {
        // Get all files in a pending state.
        $plagiarismfiles = $DB->get_records("plagiarism_compilatio_files", array("statuscode" => "pending"));
        foreach ($plagiarismfiles as $plagiarism_file) {
            $tmpfile = compilatio_get_temp_file($plagiarism_file->filename);
            if ($tmpfile !== false) {
                compilatio_send_file_to_compilatio($plagiarism_file, $plagiarismsettings, $tmpfile);
                compilatio_start_if_direct_analysis($plagiarism_file, $plagiarism_file->cm, $plagiarismsettings);
                unlink($tmpfile->filepath);
            } else {
                // Not a temporary file

                $modulecontext = context_module::instance($plagiarism_file->cm);
                $contextid = $modulecontext->id;
                $sql = "SELECT * FROM {files} f WHERE f.contenthash= ? AND contextid = ?";
                $f = $DB->get_record_sql($sql, array($plagiarism_file->identifier, $contextid));
                if (empty($f)) {
                    continue;
                }
                $file = $fs->get_file_by_id($f->id);
                $compid = compilatio_send_file_to_compilatio($plagiarism_file, $plagiarismsettings, $file);
                compilatio_start_if_direct_analysis($plagiarism_file, $plagiarism_file->cm, $plagiarismsettings);
            }
        }
    }
}

function compilatio_start_if_direct_analysis($plagiarism_file, $cmid, $plagiarismsettings) {
    global $DB;
    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    // Check settings to see if we need to tell compilatio to process this file now.
    if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_AUTO) {
        compilatio_startanalyse($plagiarism_file, $plagiarismsettings);
    }
}

function compilatio_get_temp_file($filename) {
    global $CFG;
    $filepath = $CFG->dataroot . "/temp/compilatio/" . $filename;
    if (!file_exists($filepath))
        return false;
    $file = new stdclass();
    $file->type = "tempcompilatio";
    $file->filename = $filename;
    $file->identifier = sha1_file($filepath);
    $file->filepath = $filepath;
    return $file;
}

function compilatio_create_temp_file($cmid, $eventdata) {
    global $CFG;
    if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
        mkdir($CFG->dataroot . "/temp/compilatio", 0700);
    }
    $filename = "content-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->userid . ".htm";
    $filepath = $CFG->dataroot . "/temp/compilatio/" . $filename;
    $fd = fopen($filepath, 'wb');   // Create if not exist, write binary
    fwrite($fd, $eventdata->content);
    fclose($fd);
    $file = new stdclass();
    $file->type = "tempcompilatio";
    $file->filename = $filename;
    $file->timestamp = time();
    $file->identifier = sha1_file($filepath);
    $file->filepath = $filepath;
    return $file;
}

/**
 * adds the list of plagiarism settings to a form.
 *
 * @param object $mform - Moodle form object
 * @oaram boolean $defaults - if this is being loaded from defaults form or from inside a mod.
 */
function compilatio_get_form_elements($mform, $defaults = false) {
    $ynoptions = array(0 => get_string('no'),
        1 => get_string('yes'));
    $tiioptions = array(0 => get_string("never"),
        1 => get_string("immediately", "plagiarism_compilatio"),
        2 => get_string("showwhenclosed", "plagiarism_compilatio"));
    $compilatiodraftoptions = array(
        PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE => get_string("submitondraft", "plagiarism_compilatio"),
        PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL => get_string("submitonfinal", "plagiarism_compilatio")
    );

    $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));
    $mform->addElement('select', 'use_compilatio', get_string("use_compilatio", "plagiarism_compilatio"), $ynoptions);
    $mform->setDefault('use_compilatio', 1);




    $analysistypes = array(COMPILATIO_ANALYSISTYPE_AUTO => get_string('analysistype_direct', 'plagiarism_compilatio'),
        COMPILATIO_ANALYSISTYPE_MANUAL => get_string('analysistype_manual', 'plagiarism_compilatio'),
        COMPILATIO_ANALYSISTYPE_PROG => get_string('analysistype_prog', 'plagiarism_compilatio'));
    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'compilatio_analysistype', get_string('analysis_type', 'plagiarism_compilatio'), $analysistypes);
        $mform->addHelpButton('compilatio_analysistype', 'analysis_type', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_analysistype', COMPILATIO_ANALYSISTYPE_MANUAL);
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector', 'compilatio_timeanalyse', get_string('analysis_date', 'plagiarism_compilatio'), array('optional' => false));
        $mform->setDefault('compilatio_timeanalyse', time() + 7 * 24 * 3600);
        $mform->disabledif('compilatio_timeanalyse', 'compilatio_analysistype', 'noteq', COMPILATIO_ANALYSISTYPE_PROG);
    }

    $mform->addElement('select', 'compilatio_show_student_score', get_string("compilatio_display_student_score", "plagiarism_compilatio"), $tiioptions);
    $mform->addHelpButton('compilatio_show_student_score', 'compilatio_display_student_score', 'plagiarism_compilatio');
    if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") === '1') {
        $mform->addElement('select', 'compilatio_show_student_report', get_string("compilatio_display_student_report", "plagiarism_compilatio"), $tiioptions);
        $mform->addHelpButton('compilatio_show_student_report', 'compilatio_display_student_report', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }
    if ($mform->elementExists('var4') ||
            $mform->elementExists('submissiondrafts')) {
        $mform->addElement('html', '<div style="display:none">');
        $mform->addElement('select', 'compilatio_draft_submit', get_string("compilatio_draft_submit", "plagiarism_compilatio"), $compilatiodraftoptions);
        $mform->addElement('html', '</div>');
    }
    $mform->addElement('select', 'compilatio_studentemail', get_string("compilatio_studentemail", "plagiarism_compilatio"), $ynoptions);
    $mform->addHelpButton('compilatio_studentemail', 'compilatio_studentemail', 'plagiarism_compilatio');




    $mform->addElement('html', '<p><strong>' . get_string("thresholds_settings", "plagiarism_compilatio") . '</strong></p>');
    $mform->addElement('html', '<p>' . get_string("thresholds_description", "plagiarism_compilatio") . '</p>');

    $mform->addElement('html', '<div>');
    $mform->addElement('text', 'green_threshold', get_string("green_threshold", "plagiarism_compilatio"), 'size="5" id="green_threshold"');
    $mform->addElement('html', '<noscript>' . get_string('similarity_percent', "plagiarism_compilatio") . '</noscript>');
    $mform->addElement('text', 'orange_threshold', get_string("orange_threshold", "plagiarism_compilatio"), 'size="5" id="orange_threshold"');
    $mform->addElement('html', '<noscript>' . get_string('similarity_percent', "plagiarism_compilatio") . ', ' . get_string("red_threshold", "plagiarism_compilatio") . '</noscript>');
    $mform->addElement('html', '</div>');

    $jquery_url = new moodle_url("/plagiarism/compilatio/jquery.min.js");

    //Used to append text nicely after the inputs. If Javascript is disabled, it will be displayed on the line below the input.
    $mform->addElement('html', '<script src="' . $jquery_url . '"></script>');
    $mform->addElement('html', '<script>
	$(document).ready(function(){
		var txtGreen = $("<span>", {class:"after-input"}).text("' . get_string('similarity_percent', "plagiarism_compilatio") . '");
		$("#green_threshold").after(txtGreen);
		var txtOrange = $("<span>", {class:"after-input"}).text("' . get_string('similarity_percent', "plagiarism_compilatio") . ', ' . get_string("red_threshold", "plagiarism_compilatio") . '.");
		$("#orange_threshold").after(txtOrange);
	});
	</script>');



    //Numeric validation for Thresholds
    $mform->addRule('green_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');
    $mform->addRule('orange_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');

    $mform->setType('green_threshold', PARAM_INT);
    $mform->setType('orange_threshold', PARAM_INT);

    $mform->setDefault('green_threshold', '10');
    $mform->setDefault('orange_threshold', '25');
}

/**
 * updates a compilatio_files record
 *
 * @param int $cmid - course module id
 * @param int $userid - user id
 * @param varied $identifier - identifier for this plagiarism record - hash of file, id of quiz question etc.
 * @return int - id of compilatio_files record
 */
function compilatio_get_plagiarism_file($cmid, $userid, $file) {
    global $DB;

    if (is_a($file, '\stdClass')) {
        $filehash = $file->identifier;
    } else {
        $filehash = $file->get_contenthash();
    }

    // Now update or insert record into compilatio_files.

    if ($filehash != null) {
        $plagiarism_file = $DB->get_record_sql(
                "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND " .
                "identifier = ?", array($cmid, $userid, $filehash));
    } else {
        // We don't have the hash of the content-submission, get it by name
        $plagiarism_file = $DB->get_record_sql(
                "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND " .
                "filename = ?", array($cmid, $userid, $file->filename));
    }
    if (!empty($plagiarism_file)) {
        return $plagiarism_file;
    } else {
        $plagiarism_file = new stdClass();
        $plagiarism_file->cm = $cmid;
        $plagiarism_file->userid = $userid;
        $plagiarism_file->identifier = $filehash;
        $plagiarism_file->filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
        $plagiarism_file->statuscode = 'pending';
        $plagiarism_file->attempt = 0;
        $plagiarism_file->timesubmitted = time();
        if (!$pid = $DB->insert_record('plagiarism_compilatio_files', $plagiarism_file)) {
            debugging("insert into compilatio_files failed");
        }
        $plagiarism_file->id = $pid;
        return $plagiarism_file;
    }
}

function compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings, $sendfile = false) {
    global $DB;
    $plagiarism_file = compilatio_get_plagiarism_file($cmid, $userid, $file);
    // Check if $plagiarism_file actually needs to be submitted.
    if ($plagiarism_file->statuscode <> 'pending') {
        return true;
    }
    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
    if ($plagiarism_file->filename !== $filename) {
        // This is a file that was previously submitted and not sent to compilatio but the filename has changed so fix it.
        $plagiarism_file->filename = $filename;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
    }
    // Check to see if this is a valid file.
    $mimetype = compilatio_check_file_type($filename);
    if (empty($mimetype)) {
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        return true;
    }

    // Optionally send the file to Compilatio
    if ($sendfile !== false) {
        // Check if we need to delay this submission.
        $attemptallowed = compilatio_check_attempt_timeout($plagiarism_file);
        if (!$attemptallowed) {
            return false;
        }
        // Increment attempt number.
        $plagiarism_file->attempt = $plagiarism_file->attempt + 1;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        $compid = compilatio_send_file_to_compilatio($plagiarism_file, $plagiarismsettings, $file);
        compilatio_start_if_direct_analysis($plagiarism_file, $cmid, $plagiarismsettings);
        return false;
    }
    return true;
}

// Function to check timesubmitted and attempt to see if we need to delay an API check.
// Also checks max attempts to see if it has exceeded.
function compilatio_check_attempt_timeout($plagiarism_file) {
    global $DB;
    // The first time a file is submitted we don't need to wait at all.
    if (empty($plagiarism_file->attempt) && $plagiarism_file->statuscode == 'pending') {
        return true;
    }
    $now = time();
    // Set some initial defaults.
    $submissiondelay = 15;
    $maxsubmissiondelay = 60;
    $maxattempts = 4;
    if ($plagiarism_file->statuscode == 'pending') {
        // Initial wait time - doubled each time a check is made until the max delay is met.
        $submissiondelay = COMPILATIO_SUBMISSION_DELAY;
        // Maximum time to wait between submissions.
        $maxsubmissiondelay = COMPILATIO_MAX_SUBMISSION_DELAY;
        // Maximum number of times to try and send a submission.
        $maxattempts = COMPILATIO_MAX_SUBMISSION_ATTEMPTS;
    } else if ($plagiarism_file->statuscode == COMPILATIO_STATUSCODE_ANALYSING || $plagiarism_file->statuscode == COMPILATIO_STATUSCODE_IN_QUEUE) {
        // Initial wait time - this is doubled each time a check is made until the max delay is met.
        $submissiondelay = COMPILATIO_STATUS_DELAY;
        // Maximum time to wait between checks.
        $maxsubmissiondelay = COMPILATIO_MAX_STATUS_DELAY;
        // Maximum number of times to try and send a submission.
        $maxattempts = COMPILATIO_MAX_STATUS_ATTEMPTS;
    }
    $wait = $submissiondelay;
    // Check if we have exceeded the max attempts.
    if ($plagiarism_file->attempt > $maxattempts) {
        $plagiarism_file->statuscode = 'timeout';
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        return true; // Return true to cancel the event.
    }
    // Now calculate wait time.
    $i = 0;
    while ($i < $plagiarism_file->attempt) {
        if ($wait > $maxsubmissiondelay) {
            $wait = $maxsubmissiondelay;
        }
        $wait = $wait * $plagiarism_file->attempt;
        $i++;
    }
    $wait = (int) $wait * 60;
    $timetocheck = (int) ($plagiarism_file->timesubmitted + $wait);
    // Calculate when this should be checked next.
    if ($timetocheck < $now) {
        return true;
    } else {
        return false;
    }
}

function compilatio_send_file_to_compilatio(&$plagiarism_file, $plagiarismsettings, $file) {
    global $DB, $CFG;

    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
    $mimetype = compilatio_check_file_type($filename);
    if (empty($mimetype)) { // Sanity check on filetype - this should already have been checked.
        debugging("no mime type for this file found.");
        return false;
    }
    // Display this log only on CRON exec.
    if (!defined("COMPILATIO_MANUAL_SEND")) {
        mtrace("sending file #" . $plagiarism_file->id);
    }

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    // Get name from module.
    $modulesql = 'SELECT m.id, m.name, cm.instance' .
            ' FROM {course_modules} cm' .
            ' INNER JOIN {modules} m on cm.module = m.id ' .
            'WHERE cm.id = ?';
    $moduledetail = $DB->get_record_sql($modulesql, array($plagiarism_file->cm));
    if (!empty($moduledetail)) {
        $sql = "SELECT * FROM " . $CFG->prefix . $moduledetail->name . " WHERE id= ?";
        $module = $DB->get_record_sql($sql, array($moduledetail->instance));
    }
    if (empty($module)) {
        debugging("could not find this module - it may have been deleted?");
        return false;
    }
    $name = format_string($module->name) . "_" . $plagiarism_file->cm;
    $filecontents = (!empty($file->filepath)) ? file_get_contents($file->filepath) : $file->get_content();
    $id_compi = $compilatio->SendDoc($name, // Title.
            $name, // Description.
            $filename, // File_name.
            $mimetype, // Mime data.
            $filecontents); // Doc content.

    if (compilatio_valid_md5($id_compi)) {
        $plagiarism_file->externalid = $id_compi;
        $plagiarism_file->attempt = 0; // Reset attempts for status checks.
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        return $id_compi;
    }
    //correctif blocage moodle doc non extractable
    $plagiarism_file->attempt = 0; // Reset attempts for status checks.
    $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_UNEXTRACTABLE;
    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
    debugging("invalid compilatio response received - will try again later." . $id_compi);
    // Invalid response returned - increment attempt value and return false to allow this to be called again.
    return false;
}

// Function to check for the allowed file types, returns the mimetype that COMPILATIO expects.
function compilatio_check_file_type($filename) {
    $pathinfo = pathinfo($filename);

    if (empty($pathinfo['extension'])) {
        return '';
    }
    $ext = strtolower($pathinfo['extension']);
    $filetypes = array('doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'rtf' => 'application/rtf',
        'html' => 'text/html',
        'htm' => 'text/html',
        'odt' => 'application/vnd.oasis.opendocument.text');
    if (!empty($filetypes[$ext])) {
        return $filetypes[$ext];
    }
}

/**
 * used to obtain similarity scores from Compilatio for submitted files.
 *
 * @param object $plagiarismsettings - from a call to plagiarism_get_settings.
 *
 */
function compilatio_get_scores($plagiarismsettings) {
    global $DB;
    mtrace("getting Compilatio similarity scores");
    // Get all files set that have been submitted.

    $sql = "statuscode = ? OR statuscode = ? OR statuscode = ?";
    $params = array(COMPILATIO_STATUSCODE_ANALYSING, COMPILATIO_STATUSCODE_IN_QUEUE, "pending");
    $files = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);



    if (!empty($files)) {
        foreach ($files as $plagiarism_file) {
            // Check if we need to delay this submission.
            $attemptallowed = compilatio_check_attempt_timeout($plagiarism_file);
            if (!$attemptallowed) {
                continue;
            }
            mtrace("getting score for file " . $plagiarism_file->id);
            compilatio_check_analysis($plagiarism_file); // Get status and set reporturl/status if required.
        }
    }
    // Now check for files that need to be set to analyse.
}

// Helper function to save multiple db calls.
function compilatio_cm_use($cmid) {
    global $DB;
    static $usecompilatio = array();
    if (!isset($usecompilatio[$cmid])) {
        $pvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name,value');
        if (!empty($pvalues['use_compilatio'])) {
            $usecompilatio[$cmid] = $pvalues;
        } else {
            $usecompilatio[$cmid] = false;
        }
    }
    return $usecompilatio[$cmid];
}

/**
 * Function that checks Compilatio to see if there are any newly supported filetypes.
 *
 */
function compilatio_update_allowed_filetypes() {
    // Not implemented.
    return false;
}

/**
 * Fonction to return current compilatio quota.
 * @return $quotas
 */
function compilatio_getquotas() {
    global $CFG;
    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    return $compilatio->GetQuotas();
}

function compilatio_startanalyse($plagiarism_file, $plagiarismsettings = '') {
    global $CFG, $DB, $OUTPUT;
    if (empty($plagiarismsettings)) {
        $plagiarismsettings = (array) get_config('plagiarism');
    }
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    $analyse = $compilatio->StartAnalyse($plagiarism_file->externalid);
    if ($analyse === true) {
        // Update plagiarism record.
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
    } else {
        echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio') . $analyse->string);
        return $analyse;
    }
    return true;
}

// Function to check for valid response from Compilatio.
function compilatio_valid_md5($hash) {
    if (preg_match('`^[a-f0-9]{32}$`', $hash)) {
        return true;
    } else {
        return false;
    }
}

function compilatio_check_analysis($plagiarism_file, $manually_triggered = false) {
    global $CFG, $DB;
    $plagiarismsettings = (array) get_config('plagiarism');
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    $docstatus = $compilatio->GetDoc($plagiarism_file->externalid);
    if (isset($docstatus->documentStatus->status)) {
        if ($docstatus->documentStatus->status == "ANALYSE_COMPLETE") {
            if (isset($docstatus->documentProperties) && $docstatus->documentProperties->wordCount < 10) {
                //Set the code to UNEXTRACTABLE if the documents contains less than 10 words:
                $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_UNEXTRACTABLE;
            } else {
                $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_COMPLETE;
            }
            $plagiarism_file->similarityscore = round($docstatus->documentStatus->indice);
            // Now get report url.
            $plagiarism_file->reporturl = $compilatio->GetReportUrl($plagiarism_file->externalid);
            $emailstudents = $DB->get_field('plagiarism_compilatio_config', 'value', array('cm' => $plagiarism_file->cm, 'name' => 'compilatio_studentemail'));
            if (!empty($emailstudents)) {
                $compilatio = new plagiarism_plugin_compilatio();
                $compilatio->compilatio_send_student_email($plagiarism_file);
            }
        }
        //Added for queue support
        else if ($docstatus->documentStatus->status == "ANALYSE_IN_QUEUE") {
            $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
        } else if ($docstatus->documentStatus->status == "ANALYSE_PROCESSING") {
            $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ANALYSING;
        } else if ($docstatus->documentStatus->status == "ANALYSE_NOT_STARTED") {
            $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        }
    }
    if (!$manually_triggered) {
        $plagiarism_file->attempt = $plagiarism_file->attempt + 1;
    }
    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
}

function compilatio_analyse_files($plagiarismfiles) {
    $plagiarismsettings = (array) get_config('plagiarism');
    foreach ($plagiarismfiles as $plagiarism_file) {
        compilatio_startanalyse($plagiarism_file, $plagiarismsettings);
    }
}

/*
 * Get the Compilatio subscription expiration date (Year and month)
 * @return string, formatted as "YYYY-MM".
 */

function compilatio_get_account_expiration_date() {
    global $CFG;
    $plagiarismsettings = (array) get_config('plagiarism');
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    return $compilatio->GetAccountExpirationDate();
}

/*
 * Send informations about this configuration to Compilatio
 * @return bool : False if any error occurs, true otherwise
 */

function compilatio_send_statistics() {
    //Get data about installation
    global $CFG;
    global $DB;

    $language = $CFG->lang;
    $releasePHP = phpversion();
    $releaseMoodle = $CFG->release;
    $releasePlugin = get_config('plagiarism_compilatio', 'version');
    $cronFrequencyObject = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));
    if ($cronFrequencyObject != null) {
        $cronFrequency = $cronFrequencyObject->value;
    } else {
        $cronFrequency = 0;
    }

    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    return $compilatio->PostConfiguration($releasePHP, $releaseMoodle, $releasePlugin, $language, $cronFrequency);
}

/*
 * Get all Compilatio news from Compilatio Webservice
 * @returns array of news objects
 */

function compilatio_get_technical_news() {
    global $CFG;
    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    return $compilatio->GetTechnicalNews();
}

/*
 * Test connection to Compilatio webservice.
 * @eturns boolean: false if API key is invalid or if we cannot reach the webservice.
 */

function compilatio_test_connection($password, $compilatio_api) {
    global $CFG;
    $compi = new compilatioservice($password, $compilatio_api, $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    $quotasArray = $compi->GetQuotas();
    return $quotasArray['quotas'] != null;
}

/*
 * Display an image and similarity percentage according to the thresholds.
 * @param $score float Similarity score to be displayed
 * @param $greenThreshold int Green for $score lower than that threshold
 * @param $redThreshold int Red for $score higher than that threshold
 * @return the HTML string displaying colored score and an image
 */

function compilatio_get_image_similarity($score, $greenThreshold, $redThreshold) {
    global $OUTPUT;
    $content = "<span>";
    if ($score <= $greenThreshold) {
        $content.= '
		<img class="similarity-image" src="' . $OUTPUT->pix_url('green', 'plagiarism_compilatio') . '"/>
		<span class="similarity similarity-green">' . $score . '<span class="percentage">%</span></span>';
    } else if ($score <= $redThreshold) {
        $content.= '<img class="similarity-image" src="' . $OUTPUT->pix_url('orange', 'plagiarism_compilatio') . '"/>
		<span class="similarity similarity-orange">' . $score . '<span class="percentage">%</span></span>';
    } else {
        $content.= '<img class="similarity-image" src="' . $OUTPUT->pix_url('red', 'plagiarism_compilatio') . '">
		<span class="similarity similarity-red">' . $score . '<span class="percentage">%</span></span>';
    }
    $content.= "</span>";
    return $content;
}

/*
 * Get statistics for the assignment $cmid
 * @return string - HTML containing the statistics
 */

function compilatio_get_statistics($cmid) {
    global $DB, $PAGE;

    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    //Create the thresholds if they don't exist : Case of upgrade from an older plugin version.
    if (!isset($plagiarismvalues["green_threshold"])) {
        $green = new StdClass();
        $green->name = "green_threshold";
        $green->value = "10";
        $green->cm = $cmid;
        $DB->insert_record('plagiarism_compilatio_config', $green, false);
        $greenThreshold = 10;
    } else {
        $greenThreshold = $plagiarismvalues["green_threshold"];
    }
    if (!isset($plagiarismvalues["orange_threshold"])) {
        $orange = new StdClass();
        $orange->name = "orange_threshold";
        $orange->value = "25";
        $orange->cm = $cmid;
        $DB->insert_record('plagiarism_compilatio_config', $orange, false);
        $redThreshold = 25;
    } else {
        $redThreshold = $plagiarismvalues["orange_threshold"];
    }

    $sql = "SELECT COUNT(DISTINCT plagiarism_compilatio_files.id)
            FROM {course_modules} course_modules
            JOIN {assign_submission} assign_submission ON assign_submission.assignment = course_modules.instance
            JOIN {files} files ON files.itemid = assign_submission.id
            JOIN {plagiarism_compilatio_files} plagiarism_compilatio_files ON plagiarism_compilatio_files.identifier = files.contenthash
            WHERE course_modules.id=? AND plagiarism_compilatio_files.cm=? AND files.filearea='submission_files' ";

    $countAllSQL = $sql;
    $documentsCount = $DB->count_records_sql($countAllSQL, array($cmid, $cmid));

    $countAnalyzedSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "'";
    $countAnalyzed = $DB->count_records_sql($countAnalyzedSQL, array($cmid, $cmid));

    $countHigherThanRedSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore>$redThreshold";
    $countHigherThanRed = $DB->count_records_sql($countHigherThanRedSQL, array($cmid, $cmid));

    $countLowerThanGreenSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore<=$greenThreshold";
    $countLowerThanGreen = $DB->count_records_sql($countLowerThanGreenSQL, array($cmid, $cmid));

    $countUnsupportedSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNSUPPORTED . "'";
    $countUnsupported = $DB->count_records_sql($countUnsupportedSQL, array($cmid, $cmid));

    $countUnextractableSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNEXTRACTABLE . "'";
    $countUnextractable = $DB->count_records_sql($countUnextractableSQL, array($cmid, $cmid));

    $countInQueueSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_IN_QUEUE . "'";
    $countInQueue = $DB->count_records_sql($countInQueueSQL, array($cmid, $cmid));

    $countAnalysingSQL = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_ANALYSING . "'";
    $countAnalysing = $DB->count_records_sql($countAnalysingSQL, array($cmid, $cmid));

    $averageSQL = "
	SELECT AVG(similarityscore) avg
	FROM {plagiarism_compilatio_files}
	WHERE id IN
	(
	SELECT DISTINCT plagiarism_compilatio_files.id
	FROM {course_modules} course_modules
	JOIN {assign_submission} assign_submission ON assign_submission.assignment = course_modules.instance
	JOIN {files} files ON files.itemid = assign_submission.id
	JOIN {plagiarism_compilatio_files} plagiarism_compilatio_files ON plagiarism_compilatio_files.identifier = files.contenthash
	JOIN {user} usr ON plagiarism_compilatio_files.userid= usr.id
	WHERE course_modules.id=? AND plagiarism_compilatio_files.cm=? AND files.filearea='submission_files' AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "'
	)
	";

    $avgResult = $DB->get_record_sql($averageSQL, array($cmid, $cmid));
    $avg = $avgResult->avg;


    $analysisStats = new StdClass();
    $analysisStats->countAnalyzed = $countAnalyzed;
    $analysisStats->documentsCount = $documentsCount;

    $analysisStatsThresholds = new StdClass();
    //Total
    $analysisStatsThresholds->documentsCount = $countAnalyzed;
    //Thresholds
    $analysisStatsThresholds->greenThreshold = $greenThreshold;
    $analysisStatsThresholds->redThreshold = $redThreshold;
    //Number of documents
    $analysisStatsThresholds->documentsUnderGreenThreshold = $countLowerThanGreen;
    $analysisStatsThresholds->documentsAboveRedThreshold = $countHigherThanRed;
    $analysisStatsThresholds->documentsBetweenThresholds = $countAnalyzed - $countHigherThanRed - $countLowerThanGreen;

    //Display an array as a list, using moodle translations and parameters. index 0 for translation index and index 1 for parameter.
    function compilatio_display_list_stats($listItems) {
        $string = "<ul>";
        foreach ($listItems as $listItem) {
            $string .= "<li>" . get_string($listItem[0], "plagiarism_compilatio", $listItem[1]) . "</li>";
        }
        $string .= "</ul>";
        return $string;
    }

    if ($documentsCount === 0) {
        $result = "<span>" . get_string("no_documents_available", "plagiarism_compilatio") . "</span>";
    } else {
        $items = array();
        $items[] = array("documents_analyzed", $analysisStats);
        if ($countAnalysing !== 0) {
            $items[] = array("documents_analyzing", $countAnalysing);
        }
        if ($countInQueue !== 0) {
            $items[] = array("documents_in_queue", $countInQueue);
        }

        $result = "<span>" . get_string("progress", "plagiarism_compilatio") . "</span>";
        $result.= compilatio_display_list_stats($items);
    }

    $items = array();

    if ($countAnalyzed != 0) {
        $items[] = array("average_similarities", round($avg));
        $items[] = array("documents_analyzed_lower_green", $analysisStatsThresholds);
        $items[] = array("documents_analyzed_between_thresholds", $analysisStatsThresholds);
        $items[] = array("documents_analyzed_higher_red", $analysisStatsThresholds);
    }

    $errors = array();
    if ($countUnsupported !== 0) {
        $errors[] = array("not_analyzed_unsupported", $countUnsupported);
    }
    if ($countUnextractable !== 0) {
        $errors[] = array("not_analyzed_unextractable", $countUnextractable);
    }

    if (count($items) !== 0) {
        $result .= "<span>" . get_string("results", "plagiarism_compilatio") . "</span>";
        $result.= compilatio_display_list_stats($items);

        if (count($errors) !== 0) {
            $result .= "<span>" . get_string("errors", "plagiarism_compilatio") . "</span>";
            $result.= compilatio_display_list_stats($errors);
        }

        $url = $PAGE->url;
        $url->param('compilatio_export', true);
        $result.="<a title='" . get_string("export_csv", "plagiarism_compilatio") . "' class='compilatio-icon' href='$url'><i class='fa fa-download fa-2x'></i></a>";
    }
    return $result;
}

/*
 * Lists unsupported documents in the assignment
 * @return array of string: containing the student & the file
 */

function compilatio_get_unsupported_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_UNSUPPORTED);
}

/*
 * Lists unextractable documents in the assignment
 * @return array of string: containing the student & the file
 */

function compilatio_get_unextractable_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_UNEXTRACTABLE);
}

/*
 * Lists files of an assignment according to the status code.
 * @return array of string: containing the student & the file
 */

function compilatio_get_files_by_status_code($cmid, $statuscode) {
    global $DB;
    $modulesql = "SELECT DISTINCT plagiarism_compilatio_files.id,plagiarism_compilatio_files.filename, usr.firstname, usr.lastname
                    FROM {course_modules} course_modules
                    JOIN {assign_submission} assign_submission ON assign_submission.assignment = course_modules.instance
                    JOIN {files} files ON files.itemid = assign_submission.id
                    JOIN {plagiarism_compilatio_files} plagiarism_compilatio_files ON plagiarism_compilatio_files.identifier = files.contenthash
                    JOIN {user} usr ON plagiarism_compilatio_files.userid= usr.id
                    WHERE course_modules.id=? AND plagiarism_compilatio_files.cm=? AND files.filearea='submission_files' AND statuscode = '" . $statuscode . "'";

    $files = $DB->get_records_sql($modulesql, array($cmid, $cmid));


    return array_map(
            function($file) {
        return $file->lastname . " " . $file->firstname . " : " . $file->filename;
    }, $files);
}

/*
 * Get the expiration date from the webservice
 * Insert or update a field in the DB containing that date
 */

function compilatio_update_account_expiration_date() {
    global $DB;
    $expirationDate = compilatio_get_account_expiration_date();
    if ($expirationDate === false) {
        return;
    }
    //Insert / update in db
    $date = $DB->get_record('plagiarism_compilatio_data', array('name' => 'account_expire_on'));

    if ($date == null) {
        $item = new stdClass();
        $item->name = "account_expire_on";
        $item->value = $expirationDate;
        $DB->insert_record('plagiarism_compilatio_data', $item);
    } else if ($date->value !== $expirationDate) {
        $date->value = $expirationDate;
        $DB->update_record('plagiarism_compilatio_data', $date);
    }
}

/*
 * Check expiration date of the account in the DB :
 * @return boolean : false if it's not expiring and true if it's expiring at the end of the month.
 */

function compilatio_check_account_expiration_date() {
    global $DB;
    $expirationDate = $DB->get_record('plagiarism_compilatio_data', array('name' => 'account_expire_on'));

    if ($expirationDate != null && date("Y-m") == $expirationDate->value) {
        return true;
    }
    return false;
}

/*
 * Update the news of Compilatio
 * Remove old entries and insert new ones.
 */

function compilatio_update_news() {
    $news = compilatio_get_technical_news();
    if ($news === false) {
        return;
    }

    global $DB;
    $DB->delete_records_select('plagiarism_compilatio_news', '1=1');
    foreach ($news as $new) {

        $new->id_compilatio = $new->id;
        $new->message_en = compilatio_decode($new->message_en);
        $new->message_fr = compilatio_decode($new->message_fr);
        unset($new->id);
        $DB->insert_record("plagiarism_compilatio_news", $new);
    }
}

/*
 * Used to solve encoding problems from Compilatio API.
 * Some string are UTF8 encoded twice.
 * @param $message string : UTF-8 String, encoded once or twice
 * @return string encoded correctly
 */

function compilatio_decode($message) {
    $decodeOnce = utf8_decode($message);
    $decodeTwice = utf8_decode($decodeOnce);
    if (preg_match('!!u', $decodeTwice)) {
        return $message;
    } else {
        return $decodeOnce;
    }
}

/*
 * Display the news of Compilatio
 * @return array containing almerts according to the news in the DB.
 */

function compilatio_display_news() {
    global $DB;
    //Get the moodle language -> function used by "get_string" to define language
    $language = current_language();

    $news = $DB->get_records_select('plagiarism_compilatio_news', 'end_display_on>? AND begin_display_on<?', array(time(), time()));

    $alerts = array();

    foreach ($news as $new) {
        $message = "";
        //Get the field matching the language, english by default
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
        //Get the title of the notification according to the type of news: 
        $title = "";
        $class = "warning";
        switch ($new->type) {
            case PLAGIARISM_COMPILATIO_NEWS_UPDATE:
                $title = get_string("news_update", "plagiarism_compilatio"); //info
                $class = "info";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_INCIDENT:
                $title = get_string("news_incident", "plagiarism_compilatio"); //danger
                $class = "danger";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_MAINTENANCE:
                $title = get_string("news_maintenance", "plagiarism_compilatio"); //warning
                $class = "warning";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_ANALYSIS_PERTURBATED:
                $title = get_string("news_analysis_perturbated", "plagiarism_compilatio"); //danger
                $class = "danger";
                break;
        }
        $alerts[] = array(
            "class" => $class,
            "title" => $title,
            "content" => $message
        );
    }
    return $alerts;
}

/* Display plagiarism document area 
 * @param $span : One word about the status to be displayed in the area
 * @param $image : Identifier of an image from Compilatio plugin, rendered using $OUTPUT->pix_url($image, 'plagiarism_compilatio')
 * @param $content : Content to be appended in the plagiarism area, such as similarity rate.
 * @param $url : array : index ["target-blank"] contains a boolean : True to open in a new window.
 * 				 index ["url"] contains the URL.
 * @param $error : Span will be stylized as an error if $error is true.
 */

function compilatio_get_plagiarism_area($span = "", $image = "", $title = "", $content = "", $url = array(), $error = false) {
    global $OUTPUT;
    $html = "<br/>";
    $html.="<div class='clear'></div>";
    $html.= "<div class='plagiarismreport' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";

    if (!empty($url) && !empty($url["url"])) {
        if ($url["target-blank"] === true) {
            $target = "target='_blank'";
        } else {
            $target = "";
        }
        $html.="<a $target class='plagiarismreport-link' href='" . $url["url"] . "'>";
    }

    $html.='<div class="small-logo-compi" style="background-image: url(\'' . new moodle_url("/plagiarism/compilatio/pix/logo_compilatio_carre.png") . '\')" title="Compilatio.net"></div>';

    if ($image !== "") {
        $html.= '<img src="' . $OUTPUT->pix_url($image, 'plagiarism_compilatio') . '" class="float-right" />';
    }
    if ($span !== "") {
        if ($error) {
            $class = "compilatio-error";
        } else {
            $class = "";
        }
        if (!empty($url) && !empty($url["url"])) {
            $class.=" link"; //Used to underline span on hover.
        }
        $html.= "<span class='$class'>$span</span>";
    }
    if ($content !== "") {
        $html.= $content;
    }
    if (!empty($url) && !empty($url["url"])) {
        $html.="</a>";
    }
    $html.="</div>";
    $html.="<div class='clear'></div>";
    return $html;
}

/*
 * Generates CSV file for an assignment
 * @param $cmid course module id of the assignment to export.
 */

function compilatio_csv_export($cmid) {
    global $DB;

    $sql = "SELECT DISTINCT plagiarism_compilatio_files.id,files.filename, usr.firstname, usr.lastname, plagiarism_compilatio_files.statuscode, plagiarism_compilatio_files.similarityscore, plagiarism_compilatio_files.timesubmitted
            FROM {course_modules} course_modules
            JOIN {assign_submission} assign_submission ON assign_submission.assignment = course_modules.instance
            JOIN {files} files ON files.itemid = assign_submission.id
            JOIN {plagiarism_compilatio_files} plagiarism_compilatio_files ON plagiarism_compilatio_files.identifier = files.contenthash
            JOIN {user} usr ON plagiarism_compilatio_files.userid= usr.id
            WHERE course_modules.id=? AND plagiarism_compilatio_files.cm=? AND files.filearea='submission_files'";

    $files = $DB->get_records_sql($sql, array($cmid, $cmid));


    $moduleConfig = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    $analysisType = $moduleConfig["compilatio_analysistype"];


    //Get the name of the activity in order to generate header line and the filename
    $name = "";
    $sql = "SELECT assign.name
            FROM {course_modules} course_modules
            JOIN {assign} assign ON course_modules.course = assign.course
            AND course_modules.instance = assign.id
            WHERE course_modules.id =?";
    $record = $DB->get_record_sql($sql, array($cmid));
    if ($record != null) {
        $name = $record->name;
    }
    $date = userdate(time());
    //Sanitize date for CSV
    $date = str_replace(",", "", $date);
    //Create CSV first line
    $head = '"' . $name . " - " . $date . "\",\n";
    //Sanitize filename
    $name = preg_replace("/[^a-z0-9\.]/", "", strtolower($name));

    $filename = "compilatio_moodle_" . $name . "_" . date("Y_m_d") . ".csv";
    //Add the first line to the content : "{Name of the module} - {date}"
    $csv = $head;

    foreach ($files as $file) {
        $line = array();
        $line["lastname"] = $file->lastname;
        $line["firstname"] = $file->firstname;
        $line["filename"] = $file->filename;
        $line["timesubmitted"] = date("d/m/y H:i:s", $file->timesubmitted);

        switch ($file->statuscode) {
            case COMPILATIO_STATUSCODE_COMPLETE:
                $line["similarities"] = $file->similarityscore;
                break;
            case COMPILATIO_STATUSCODE_UNEXTRACTABLE:
                $line["similarities"] = get_string("unextractable", "plagiarism_compilatio");
                break;
            case COMPILATIO_STATUSCODE_UNSUPPORTED:
                $line["similarities"] = get_string("unsupported", "plagiarism_compilatio");
                break;
            case COMPILATIO_STATUSCODE_ANALYSING:
                $line["similarities"] = get_string("analysing", "plagiarism_compilatio");
                break;
            case COMPILATIO_STATUSCODE_IN_QUEUE:
                $line["similarities"] = get_string("queued", "plagiarism_compilatio");
                break;
            default:
                if ($analysisType == COMPILATIO_ANALYSISTYPE_MANUAL) {
                    $line["similarities"] = get_string("manual_analysis", "plagiarism_compilatio");
                } else if ($analysisType == COMPILATIO_ANALYSISTYPE_PROG) {
                    $line["similarities"] = get_string("waitingforanalysis", "plagiarism_compilatio", userdate($moduleConfig['compilatio_timeanalyse']));
                } else {
                    $line["similarities"] = "";
                }
                break;
        }
        if ($csv === $head) {
            //Translate headers, using the key of the array as the key for translation : 
            $headers = array_keys($line);
            $headersTranslated = array_map(function($item) {
                return get_string($item, "plagiarism_compilatio");
            }, $headers);
            $csv .= '"' . implode('","', $headersTranslated) . "\"\n";
        }

        $csv .= '"' . implode('","', $line) . "\"\n";
    }

    header('HTTP/1.1 200 OK');
    header('Date: ' . date('D M j G:i:s T Y'));
    header('Last-Modified: ' . date('D M j G:i:s T Y'));
    header('Content-Disposition: attachment;filename=' . $filename);
    if (is_callable("mb_convert_encoding")) {
        header('Content-Type: application/vnd.ms-excel');
        //Display with the right encoding for Excel PC & Excel Mac
        print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $csv, 'UTF-16LE', 'UTF-8');
    } else {
        header('Content-Type: text/csv; charset=utf-8');
        echo "\xEF\xBB\xBF";
        //echo "sep=,\n";
        echo $csv;
    }

    exit(0);
}

/*
 * Display the help according to the language (English by default) from the files help/FAQ-en.php and help/FAQ-fr.php
 * @return string - Contains the help content & script to toggle display on click
 */

function compilatio_display_help() {

    $items = array("settings",
        "thresholds",
        "format",
        "languages");
    $string = "<ul id='compilatio-help-items'>";
    foreach ($items as $item) {
        $string.= "<li>";
        $string.= "<strong>" . get_string("help_compilatio_" . $item . "_title", "plagiarism_compilatio") . "</strong>";
        $string.= "<div>" . get_string("help_compilatio_" . $item . "_content", "plagiarism_compilatio") . "</div>";

        $string.= "</li>";
    }
    $string.= "</ul>";
    $string.= "<p>" . get_string("compilatio_faq", "plagiarism_compilatio") . "</p>";
    //Expand the response on click on a question :
    $string.= "<script>
		$(document).ready(function(){
			$('#compilatio-help-items li strong').click(function(){
				var delay = 500;
				var target = $(this).parent().children().not($(this));
				target.toggle(delay);
				$('#compilatio-help-items li div').not($(this)).not(target).hide(delay)
			})
		});
	</script>";
    //Hide the help if javascript is disabled, and give advice to the user on how to enable it.
    $string.= "<noscript>
		<style>
			#compilatio-help ul{
					display:none;
			}
		</style>
			<p>" . get_string("enable_javascript", "plagiarism_compilatio") . "</p>
	</noscript>";
    return $string;
}

/*
 * Format the date from "2015-05" to may 2015 or mai 2015, according to the moodle language.
 * @param $date formatted like "YYYY-MM"
 * @return string : Litteral month in local language and year.
 */

function compilatio_format_date($date) {
    $year = (int) substr($date, 0, 4);
    $month = (int) substr($date, 5);

    if ($month > 12 || $month < 1) {
        return $date;
    }
    $d = mktime(0, 0, 0, $month, 28, $year);
    return userdate($d, "%B %Y");
}

/*
 * Get tbe submissions unknown from Compilatio table plagiarism_compilatio_files
 * @param $cmid : cmid of the assignment
 */

function compilatio_get_non_uploaded_documents($cmid) {
    global $DB;

    return $DB->get_records_sql(
                    "SELECT files.*
        FROM {course_modules} course_modules
        JOIN {assignsubmission_file} assignsubmission_file ON assignsubmission_file.assignment = course_modules.instance
        JOIN {files} files ON files.itemid = assignsubmission_file.submission AND component='assignsubmission_file' AND filearea='submission_files' AND filename<>'.'
        WHERE course_modules.id=? AND contenthash NOT IN (
            SELECT DISTINCT identifier
            FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
            WHERE cm=course_modules.id AND files.userid=plagiarism_compilatio_files.userid
    )", array($cmid));
}

/*
 * Uploads files to compilatio
 * @param $files : Array of file records
 * @param $cmid : cmid of the assignment
 */

function compilatio_upload_files($files, $cmid) {
    global $DB;
    $fs = get_file_storage();

    $compilatio = new plagiarism_plugin_compilatio();
    $plagiarismsettings = $compilatio->get_settings();

    $analysis_type = $DB->get_field('plagiarism_compilatio_config', 'value', array('cm' => $cmid, "name" => "compilatio_analysistype"));
    $time_analysis = $DB->get_field('plagiarism_compilatio_config', 'value', array('cm' => $cmid, "name" => "compilatio_timeanalyse"));

    foreach ($files as $file) {
        $f = $fs->get_file_by_id($file->id);
        compilatio_queue_file($cmid, $file->userid, $f, $plagiarismsettings, true); // send the file to Compilatio
        /* Start analysis if the settings are on "manual" or "timed" and the planned time is greater than the current time
          Starting "auto" analysis is handled in "compilatio_send_file" */
        if ($analysis_type == COMPILATIO_ANALYSISTYPE_MANUAL ||
                ($analysis_type == COMPILATIO_ANALYSISTYPE_PROG &&
                time() >= $time_analysis)) {
            $plagiarism_file = compilatio_get_plagiarism_file($cmid, $file->userid, $f);
            compilatio_startanalyse($plagiarism_file, $plagiarismsettings);
        }
    }
}

/*
 * Display Compilatio's logo according to the user's language (English by default)
 * FR, IT, ES & EN are currently available.
 * @return string  - HTML tag displaying the right image
 */

function compilatio_get_logo() {
    global $OUTPUT;
    $ln = current_language();
    if (!in_array($ln, array("fr", "en", "it", "es"))) {
        $language = "en";
    } else {
        $language = $ln;
    }
    return '<img title="Compilatio" id="compilatio-logo" src="' . $OUTPUT->pix_url('compilatio-logo-' . $language, 'plagiarism_compilatio') . '">';
}

/*
 * Get the cron frequency and store it in the database
 * Cron frequency is the difference between the current time and
 * the time of the last cron execution
 */

function compilatio_update_cron_frequency() {
    global $DB;
    //Create or Update last execution date of CRON task :
    //Get last cron exec :
    $lastCron = $DB->get_record('plagiarism_compilatio_data', array('name' => 'last_cron'));

    //get & store cron frequency :
    if ($lastCron != null) {
        //Convert in minutes
        $frequency = round((time() - $lastCron->value) / 60);

        $lastFrequency = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));

        if ($lastFrequency == null) {//Create if not exists
            $item = new stdClass();
            $item->name = "cron_frequency";
            $item->value = $frequency;
            $DB->insert_record('plagiarism_compilatio_data', $item);
        } else {
            $lastFrequency->value = $frequency;
            $DB->update_record('plagiarism_compilatio_data', $lastFrequency);
        }
    }
    return $lastCron;
}

/*
 * Updates the last CRON date in the database
 * @param $lastcron : stdClass from a get_record call.
 */

function compilatio_update_last_cron_date($lastCron) {
    global $DB;
    //Insert or update the last cron date
    if ($lastCron == null) {//Create if not exists
        $item = new stdClass();
        $item->name = "last_cron";
        $item->value = strtotime("now");
        $DB->insert_record('plagiarism_compilatio_data', $item);
    } else {
        $lastCron->value = strtotime("now");
        $DB->update_record('plagiarism_compilatio_data', $lastCron);
    }
}

/*
 * Updates a marker in the database, according to Compilatio's webservice status
 */

function compilatio_update_connection_status() {
    global $DB;
    //Test connection to the Compilatio web service.
    $plagiarismsettings = (array) get_config('plagiarism');
    $connectionStatus = compilatio_test_connection($plagiarismsettings["compilatio_password"], $plagiarismsettings["compilatio_api"]);

    //Insert connection status into DB
    $oldConnectionStatus = $DB->get_record('plagiarism_compilatio_data', array('name' => 'connection_webservice'));
    if ($oldConnectionStatus == null) {
        //Create if not exists
        $item = new stdClass();
        $item->name = "connection_webservice";
        $item->value = (int) $connectionStatus;
        $DB->insert_record('plagiarism_compilatio_data', $item);
    } elseif ($oldConnectionStatus->value != $connectionStatus) {
        $oldConnectionStatus->value = (int) $connectionStatus;
        $DB->update_record('plagiarism_compilatio_data', $oldConnectionStatus);
    }
}

/*
 * Get glboal plagiarism statistics :
 * @param $html boolean display HTML if true, text otherwise 
 * @return array containing associative arrays for the statistics
 */

function compilatio_get_global_statistics($html = true) {

    $sql = 'SELECT cm,
        course.id,
        course.fullname "course", 
        assign.name "assign",
        AVG(similarityscore) "avg",
        MIN(similarityscore) "min",
        MAX(similarityscore) "max",
        COUNT(similarityscore) "count",
        usr.firstname "firstname",
        usr.lastname "lastname",
        usr.id "userid"
FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
JOIN {course_modules} course_modules
    ON plagiarism_compilatio_files.cm = course_modules.id
JOIN {assign} assign ON course_modules.instance= assign.id
JOIN {course} course ON course_modules.course= course.id
JOIN {event} event ON assign.id=event.instance
JOIN {user} usr ON event.userid=usr.id
WHERE statuscode=\'Analyzed\'
GROUP BY cm,
        course.id,
        course.fullname,
        assign.name,     
        usr.firstname,
        usr.lastname,
        usr.id
ORDER BY course.fullname, usr.lastname, assign.name';

    global $DB;
    $rows = $DB->get_records_sql($sql);

    $results = array();
    foreach ($rows as $row) {
        $courseURL = new moodle_url('/course/view.php', array('id' => $row->id));
        $assignURL = new moodle_url('/mod/assign/view.php', array('id' => $row->cm, 'action' => "grading"));
        $userURL = new moodle_url('/user/view.php', array('id' => $row->userid));

        $result = array();
        if ($html) {
            $result["teacher"] = "<a href='$userURL'>$row->lastname $row->firstname</a>";
            $result["course"] = "<a href='$courseURL'>$row->course</a>";
            $result["assign"] = "<a href='$assignURL'>$row->assign</a>";
        } else {
            $result["courseid"] = $row->id;
            $result["course"] = $row->course;
            $result["teacherid"] = $row->userid;
            $result["teacher"] = $row->lastname . " " . $row->firstname;
            $result["assignid"] = $row->cm;
            $result["assign"] = $row->assign;
        }
        $result["analyzed_documents_count"] = $row->count;
        $result["minimum_rate"] = $row->min;
        $result["maximum_rate"] = $row->max;
        $result["average_rate"] = round($row->avg, 2);
        $results[] = $result;
    }
    return $results;
}

function handleContent($content, $userid, $courseid, $cmid) {
    if (trim($content) == "") {
        return;
    }
    $data = new stdClass();
    $data->courseid = $courseid;
    $data->content = $content;
    $data->userid = $userid;

    $plagiarismsettings = (array) get_config('plagiarism');

    $file = compilatio_create_temp_file($cmid, $data);
    compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings);
}

function handleHashes($hashes, $cmid, $userid) {
    $plagiarismsettings = (array) get_config('plagiarism');
    foreach ($hashes as $hash) {
        $fs = get_file_storage();
        $efile = $fs->get_file_by_hash($hash);
        if (empty($efile)) {
            mtrace("nofilefound!");
            continue;
        } else if ($efile->get_filename() === '.') {
            // This 'file' is actually a directory - nothing to submit.
            continue;
        }
        compilatio_queue_file($cmid, $userid, $efile, $plagiarismsettings);
    }
}

function compilatio_enabled($cmid) {
    global $DB;
    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    if (empty($plagiarismvalues['use_compilatio'])) {
        return false;
    }
    // Check if the module associated with this event still exists.
    if (!$DB->record_exists('course_modules', array('id' => $cmid))) {
        return false;
    }
    return true;
}

function event_handler($eventdata, $has_file = true, $has_content = true) {
    $cmid = $eventdata["contextinstanceid"];
    if (!compilatio_enabled($cmid)) {
        return;
    }
    $userid = $eventdata["userid"];

    if ($has_file) {
        $hashes = $eventdata["other"]["pathnamehashes"];
        handleHashes($hashes, $cmid, $userid);
    }

    if ($has_content) {
        $content = $eventdata["other"]["content"];
        $courseid = $eventdata["courseid"];
        handleContent($content, $userid, $courseid, $cmid);
    }
}

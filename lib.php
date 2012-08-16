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
require_once($CFG->dirroot.'/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');

define('COMPILATIO_MAX_SUBMISSION_ATTEMPTS', 6); // Max num to try and send a submission to Compilatio.
define('COMPILATIO_MAX_SUBMISSION_DELAY', 60); // Max time to wait between submissions (defined in minutes).
define('COMPILATIO_SUBMISSION_DELAY', 15); // Initial wait time, doubled each time until max_submission_delay is met.
define('COMPILATIO_MAX_STATUS_ATTEMPTS', 10); // Maximum number of times to try and obtain the status of a submission.
define('COMPILATIO_MAX_STATUS_DELAY', 1440); // Maximum time to wait between checks (defined in minutes).
define('COMPILATIO_STATUS_DELAY', 30); // Initial wait time, doubled each time a until the max_status_delay is met.

define('COMPILATIO_STATUSCODE_ACCEPTED', '202');
define('COMPILATIO_STATUSCODE_ANALYSING', '203');
define('COMPILATIO_STATUSCODE_BAD_REQUEST', '400');
define('COMPILATIO_STATUSCODE_NOT_FOUND', '404');
define('COMPILATIO_STATUSCODE_UNSUPPORTED', '415');
define('COMPILATIO_STATUSCODE_TOO_LARGE', '413');
define('COMPILATIO_STATUSCODE_COMPLETE', 'Analyzed');

define('COMPILATIO_ANALYSISTYPE_AUTO', 0); // File shoud be processed as soon as the file is sent.
define('COMPILATIO_ANALYSISTYPE_MANUAL', 1); // File processed when teacher manually decides to.
define('COMPILATIO_ANALYSISTYPE_PROG', 2); // File processed on set time/date.

define('PLAGIARISM_COMPILATIO_SHOW_NEVER', 0);
define('PLAGIARISM_COMPILATIO_SHOW_ALWAYS', 1);
define('PLAGIARISM_COMPILATIO_SHOW_CLOSED', 2);

define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE', 0);
define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL', 1);
// Compilatio Class.
class plagiarism_plugin_compilatio extends plagiarism_plugin {
    /**
     * This function should be used to initialise settings and check if plagiarism is enabled.
     * *
     * @return mixed - false if not enabled, or returns an array of relevant settings.
     */
    public function get_settings() {
        global $DB;
        static $plagiarismsettings;
        if (!empty($plagiarism_settings) || $plagiarismsettings === false) {
            return $plagiarismsettings;
        }
        $plagiarismsettings = (array)get_config('plagiarism');
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
        return array('use_compilatio', 'compilatio_show_student_score', 'compilatio_show_student_report',
                     'compilatio_draft_submit', 'compilatio_studentemail', 'compilatio_timeanalyse', 'compilatio_analysistype');
    }
    /**
     * hook to allow plagiarism specific information to be displayed beside a submission.
     * @param array  $linkarraycontains all relevant information for the plugin to generate a link.
     * @return string
     *
     */
    public function get_links($linkarray) {
        global $DB, $USER, $COURSE, $OUTPUT, $CFG, $PAGE;
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];
        $file = $linkarray['file'];
        $results = $this->get_file_results($cmid, $userid, $file);
        if (empty($results)) {
            // Info about this file is not available to this user.
            return '';
        }
        $modulecontext = get_context_instance(CONTEXT_MODULE, $cmid);

        $output = '';
        $trigger = optional_param('compilatioprocess', 0, PARAM_INT);
        if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED && $trigger == $results['pid']) {
            if (has_capability('moodle/plagiarism_compilatio:triggeranalysis', $modulecontext)) {
                // Trigger manual analysis call.
                $plagiarism_file = compilatio_get_plagiarism_file($cmid, $userid, $file);
                $analyse = compilatio_startanalyse($plagiarism_file);
                if ($analyse === true) {
                    // Update plagiarism record.
                    $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ANALYSING;
                    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
                    $output .= '<span class="plagiarismreport">'.
                        '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_compilatio') .
                        '" alt="'.get_string('processing', 'plagiarism_compilatio').'" '.
                        '" title="'.get_string('processing', 'plagiarism_compilatio').'" />'.
                        '</span>';
                } else {
                    $output .= '<span class="plagiarismreport">'.
                    '</span>';
                }
                return $output;
            }
        }
        if ($results['statuscode'] == 'pending') {
            // TODO: check to make sure there is a pending event entry for this file - if not add one.
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_compilatio') .
                        '" alt="'.get_string('pending', 'plagiarism_compilatio').'" '.
                        '" title="'.get_string('pending', 'plagiarism_compilatio').'" />'.
                        '</span>';
            return $output;
        }
        if ($results['statuscode'] == 'Analyzed') {
            // Normal situation - Compilatio has successfully analyzed the file.
            $rank = compilatio_get_css_rank($results['score']);
            $output .= '<span class="plagiarismreport">';
            if (!empty($results['reporturl'])) {
                // User is allowed to view the report
                // Score is contained in report, so they can see the score too.
                $output .= '<a href="'.$results['reporturl'].'" target="_blank">';
                $output .= get_string('similarity', 'plagiarism_compilatio') . ':';
                $output .= '<span class="'.$rank.'">'.$results['score'].'%</span>';
                $output .= '</a>';
            } else if ($results['score'] !== '') {
                // User is allowed to view only the score.
                $output .= get_string('similarity', 'plagiarism_compilatio') . ':';
                $output .= '<span class="' . $rank . '">' . $results['score'] . '%</span>';
            }
            if (!empty($results['optoutlink'])) {
                // Display opt-out link.
                $output .= '&nbsp;<span class"plagiarismoptout">' .
                        '<a href="' . $results['optoutlink'] . '" target="_blank">' .
                        get_string('optout', 'plagiarism_compilatio') .
                        '</a></span>';
            }
            if (!empty($results['renamed'])) {
                $output .= $results['renamed'];
            }
            $output .= '</span>';
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED) {
            if (has_capability('moodle/plagiarism_compilatio:triggeranalysis', $modulecontext)) {
                $url = new moodle_url($PAGE->url, array('compilatioprocess' => $results['pid']));
                $action = optional_param('action', '', PARAM_TEXT); // Hack to add action to params for mod/assign.
                if (!empty($action)) {
                    $url->param('action', $action);
                }
                $output .= '<span class="plagiarismreport">'.
                    "<a href='$url'>".get_string('startanalysis', 'plagiarism_compilatio')."</a>" .
                    '</span>';
            } else {
                $output .= '<span class="plagiarismreport">'.
                           '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_compilatio') .
                            '" alt="'.get_string('processing', 'plagiarism_compilatio').'" '.
                           '" title="'.get_string('processing', 'plagiarism_compilatio').'" />'.
                            '</span>';
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ANALYSING) {
            $output .= '<span class="plagiarismreport">'.
                '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_compilatio') .
                '" alt="'.get_string('processing', 'plagiarism_compilatio').'" '.
                '" title="'.get_string('processing', 'plagiarism_compilatio').'" />'.
                '</span>';
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNSUPPORTED) {
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_compilatio') .
                        '" alt="'.get_string('unsupportedfiletype', 'plagiarism_compilatio').'" '.
                        '" title="'.get_string('unsupportedfiletype', 'plagiarism_compilatio').'" />'.
                        '</span>';
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_LARGE) {
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_compilatio') .
                        '" alt="'.get_string('toolarge', 'plagiarism_compilatio').'" '.
                        '" title="'.get_string('toolarge', 'plagiarism_compilatio').'" />'.
                        '</span>';
        } else {
            $title = get_string('unknownwarning', 'plagiarism_compilatio');
            $reset = '';
            if (has_capability('moodle/plagiarism_compilatio:resetfile', $modulecontext) &&
                !empty($results['error'])) { // This is a teacher viewing the responses.
                // Strip out some possible known text to tidy it up.
                $erroresponse = format_text($results['error'], FORMAT_PLAIN);
                $erroresponse = str_replace('{&quot;LocalisedMessage&quot;:&quot;', '', $erroresponse);
                $erroresponse = str_replace('&quot;,&quot;Message&quot;:null}', '', $erroresponse);
                $title .= ': ' . $erroresponse;
                $url = new moodle_url('/plagiarism/compilatio/reset.php',
                    array('cmid'=>$cmid, 'pf'=>$results['pid'], 'sesskey'=>sesskey()));
                $reset = "<a href='$url'>".get_string('reset')."</a>";
            }
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_compilatio') .
                        '" alt="'.get_string('unknownwarning', 'plagiarism_compilatio').'" '.
                        '" title="'.$title.'" />'.$reset.'</span>';
        }
        return $output;
    }

    public function get_file_results($cmid, $userid, stored_file $file) {
        global $DB, $USER, $COURSE, $CFG;
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
        $filehash = $file->get_contenthash();
        $modulesql = 'SELECT m.id, m.name, cm.instance'.
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

        $modulecontext = get_context_instance(CONTEXT_MODULE, $cmid);
        // If the user has permission to see result of all items in this course module.
        $viewscore = $viewreport = has_capability('moodle/plagiarism_compilatio:viewreport', $modulecontext);

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
            if (isset($plagiarismvalues['compilatio_show_student_report']) &&
                    ($plagiarismvalues['compilatio_show_student_report']== PLAGIARISM_COMPILATIO_SHOW_ALWAYS ||
                     $plagiarismvalues['compilatio_show_student_report']== PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
                $viewreport = true;
            }
            if (isset($plagiarismvalues['compilatio_show_student_score']) &&
                    ($plagiarismvalues['compilatio_show_student_score']== PLAGIARISM_COMPILATIO_SHOW_ALWAYS) ||
                    ($plagiarismvalues['compilatio_show_student_score']== PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
                $viewscore = true;
            }
        } else {
            $selfreport = false;
        }
        // End of rights checking.

        if (!$viewscore && !$viewreport && !$selfreport) {
            // User is not permitted to see any details.
            return false;
        }
        $plagiarismfile = $DB->get_record_sql(
                    "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND " .
                    "identifier = ?",
                    array($cmid, $userid, $filehash));
        if (empty($plagiarismfile)) {
            // No record of that submitted file.
            return false;
        }

        // Returns after this point will include a result set describing information about
        // interactions with compilatio servers.
        $results = array('statuscode' => '', 'error' => '', 'reporturl' => '',
                'score' => '', 'pid' => '', 'optoutlink' => '', 'renamed' => '',
                'analyzed' => 0,
                );
        if ($plagiarismfile->statuscode == 'pending') {
            $results['statuscode'] = 'pending';
            return $results;
        }

        // Now check for differing filename and display info related to it.
        $previouslysubmitted = '';
        if ($file->get_filename() !== $plagiarismfile->filename) {
            $previouslysubmitted = '('.get_string('previouslysubmitted', 'plagiarism_compilatio').
                ': '.$plagiarismfile->filename.')';
        }

        $results['statuscode'] = $plagiarismfile->statuscode;
        $results['pid'] = $plagiarismfile->id;
        $results['error'] = $plagiarismfile->errorresponse;
        if ($plagiarismfile->statuscode=='Analyzed') {
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
            if (!empty($plagiarismfile->optout) && $selfreport) {
                $results['optoutlink'] = $plagiarismfile->optout;
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
            // First get existing values.
            $existingelements = $DB->get_records_menu('plagiarism_compilatio_config',
                array('cm'=>$data->coursemodule), '', 'name, id');
            foreach ($plagiarismelements as $element) {
                $newelement = new object();
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
    }

    /**
     * hook to add plagiarism specific settings to a module settings page.
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context) {
        global $CFG, $DB;
        if (!$this->get_settings()) {
            return;
        }
        // Hack to prevent this from showing on custom compilatioassignment type.
        if ($mform->elementExists('seuil_faible')) {
            return;
        }
        $cmid = optional_param('update', 0, PARAM_INT); // We can't access $this->_cm here.
        if (!empty($cmid)) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>$cmid), '', 'name, value');
        }
        // The cmid(0) is the default list.
        $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>0), '', 'name, value');
        $plagiarismelements = $this->config_options();
        if (has_capability('moodle/plagiarism_compilatio:enable', $context)) {
            compilatio_get_form_elements($mform);
            if ($mform->elementExists('compilatio_draft_submit')) {
                $mform->disabledIf('compilatio_draft_submit', 'var4', 'eq', 0);
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
        // Called at top of submissions/grading pages - allows printing of admin style links or updating status.
        return '';
    }

    /**
     * called by admin/cron.php 
     *
     */
    public function cron() {
        global $CFG, $DB;
        // Do any scheduled task stuff.
        compilatio_update_allowed_filetypes();
        if ($plagiarismsettings = $this->get_settings()) {
            compilatio_get_scores($plagiarismsettings);
        }
        // Now check for any assignments with a scheduled processing time that is after now.
        $sql = "SELECT cf.* FROM mdl_plagiarism_compilatio_files cf
                LEFT JOIN mdl_plagiarism_compilatio_config cc1 ON cc1.cm = cf.cm
                LEFT JOIN mdl_plagiarism_compilatio_config cc2 ON cc2.cm = cf.cm
                LEFT JOIN mdl_plagiarism_compilatio_config cc3 ON cc3.cm = cf.cm
                WHERE cf.statuscode = '".COMPILATIO_STATUSCODE_ACCEPTED."'
                AND cc1.name = 'use_compilatio' AND cc1.value='1'
                AND cc2.name = 'compilatio_analysistype' AND cc2.value = '".COMPILATIO_ANALYSISTYPE_PROG."'
                AND cc3.name = 'compilatio_timeanalyse'
                AND " . $DB->sql_cast_char2int('cc3.value'). " < ?";
        $plagiarismfiles = $DB->get_records_sql($sql, array(time()));
        foreach ($plagiarismfiles as $plagiarism_file) {
            $analyse = compilatio_startanalyse($plagiarism_file->externalid);
            if ($analyse->code == 'NOT_ENOUGH_CREDITS') {
                // Don't process any more in this run.
                return true;
            }
        }
    }
    /**
     * generic handler function for all events - triggers sending of files.
     * @return boolean
     */
    public function event_handler($eventdata) {
        global $DB, $CFG;

        if ($eventdata->eventtype != "file_uploaded") {
            return true; // Don't need to handle this event.
        }

        $plagiarismsettings = $this->get_settings();
        if (!$plagiarismsettings) {
            return true;
        }
        $cmid = (!empty($eventdata->cm->id)) ? $eventdata->cm->id : $eventdata->cmid;
        $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>$cmid), '', 'name, value');
        if (empty($plagiarismvalues['use_compilatio'])) {
            // Compilatio not in use for this cm - return.
            return true;
        }

        // Check if the module associated with this event still exists.
        if (!$DB->record_exists('course_modules', array('id' => $eventdata->cmid))) {
            return true;
        }

        if (empty($eventdata->pathnamehashes)) {
            // Assignment-specific functionality:
            // This is a 'finalize' event. No files from this event itself,
            // but need to check if files from previous events need to be submitted for processing.
            mtrace("finalise");
            $result = true;
            if (isset($plagiarismvalues['plagiarism_draft_submit']) &&
                $plagiarismvalues['plagiarism_draft_submit'] == PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL) {
                // Any files attached to previous events were not submitted.
                // These files are now finalized, and should be submitted for processing.

                // Hack to include filelib so that file_storage class is available.
                require_once("$CFG->dirroot/mod/assignment/lib.php");
                // We need to get a list of files attached to this assignment and put them in an array, so that
                // we can submit each of them for processing.
                $assignmentbase = new assignment_base($cmid);
                $submission = $assignmentbase->get_submission($eventdata->userid);
                $modulecontext = get_context_instance(CONTEXT_MODULE, $eventdata->cmid);
                $fs = get_file_storage();
                if ($files = $fs->get_area_files($modulecontext->id, 'mod_assignment', 'submission', $submission->id,
                    "timemodified", false)) {
                    foreach ($files as $file) {
                        $sendresult = compilatio_send_file($cmid, $eventdata->userid, $file, $plagiarismsettings);
                        $result = $result && $sendresult;
                    }
                }
            }
            return $result;
        }

        if (isset($plagiarismvalues['plagiarism_draft_submit']) &&
            $plagiarismvalues['plagiarism_draft_submit'] == PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL) {
            // Assignment-specific functionality:
            // Files should only be sent for checking once "finalized".
            return true;
        }

        // Normal situation: 1 or more assessable files attached to event, ready to be checked.
        $result = true;
        foreach ($eventdata->pathnamehashes as $hash) {
            $fs = get_file_storage();
            $efile = $fs->get_file_by_hash($hash);

            if (empty($efile)) {
                mtrace("nofilefound!");
                continue;
            } else if ($efile->get_filename() ==='.') {
                // This 'file' is actually a directory - nothing to submit.
                continue;
            }

            $sendresult = compilatio_send_file($cmid, $eventdata->userid, $efile, $plagiarismsettings);
            $result = $result && $sendresult;
        }
        return $result;
    }

    public function compilatio_send_student_email($plagiarism_file) {
        global $DB, $CFG;
        if (empty($plagiarism_file->userid)) { // Sanity check.
            return false;
        }
        $user = $DB->get_record('user', array('id'=>$plagiarism_file->userid));
        $site = get_site();
        $a = new stdClass();
        $cm = get_coursemodule_from_id('', $plagiarism_file->cm);
        $a->modulename = format_string($cm->name);
        $a->modulelink = $CFG->wwwroot.'/mod/'.$cm->modname.'/view.php?id='.$cm->id;
        $a->coursename = format_string($DB->get_field('course', 'fullname', array('id'=>$cm->course)));
        $emailsubject = get_string('studentemailsubject', 'plagiarism_compilatio');
        $emailcontent = get_string('studentemailcontent', 'plagiarism_compilatio', $a);
        email_to_user($user, $site->shortname, $emailsubject, $emailcontent);
    }
}

function event_file_uploaded($eventdata) {
    $eventdata->eventtype = 'file_uploaded';
    $compilatio = new plagiarism_plugin_compilatio();
    return $compilatio->event_handler($eventdata);
}
function event_files_done($eventdata) {
    $eventdata->eventtype = 'file_uploaded';
    $compilatio = new plagiarism_plugin_compilatio();
    return $compilatio->event_handler($eventdata);
}

function event_mod_created($eventdata) {
    $result = true;
        // A new module has been created - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

function event_mod_updated($eventdata) {
    $result = true;
        // A module has been updated - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

function event_mod_deleted($eventdata) {
    $result = true;
        // A module has been deleted - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

/**
 * adds the list of plagiarism settings to a form.
 *
 * @param object $mform - Moodle form object
 * @oaram boolean $defaults - if this is being loaded from defaults form or from inside a mod.
 */
function compilatio_get_form_elements($mform, $defaults=false) {
    $ynoptions = array(0 => get_string('no'),
                       1 => get_string('yes'));
    $tiioptions = array(0 => get_string("never"),
                        1 => get_string("always"),
                        2 => get_string("showwhenclosed", "plagiarism_compilatio"));
    $compilatiodraftoptions = array(
            PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE => get_string("submitondraft", "plagiarism_compilatio"),
            PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL => get_string("submitonfinal", "plagiarism_compilatio")
            );

    $mform->addElement('header', 'plagiarismdesc');
    $mform->addElement('select', 'use_compilatio', get_string("usecompilatio", "plagiarism_compilatio"), $ynoptions);

    $analysistypes = array(COMPILATIO_ANALYSISTYPE_AUTO   => get_string('analysistypeauto', 'plagiarism_compilatio'),
                           COMPILATIO_ANALYSISTYPE_MANUAL => get_string('analysistypemanual', 'plagiarism_compilatio'),
                           COMPILATIO_ANALYSISTYPE_PROG   => get_string('analysistypeprog', 'plagiarism_compilatio'));

    $mform->addElement('select', 'compilatio_analysistype', get_string('analysistype', 'plagiarism_compilatio'), $analysistypes);
    $mform->addHelpButton('compilatio_analysistype', 'analysistype', 'plagiarism_compilatio');
    $mform->setDefault('compilatio_analysistype', COMPILATIO_ANALYSISTYPE_AUTO);

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector', 'compilatio_timeanalyse', get_string('analysisdate', 'plagiarism_compilatio'),
            array('optional'=>false));
        $mform->setDefault('compilatio_timeanalyse', time()+7*24*3600);
        $mform->disabledif('compilatio_timeanalyse', 'compilatio_analysistype', 'noteq', COMPILATIO_ANALYSISTYPE_PROG);
    }

    $mform->addElement('select', 'compilatio_show_student_score',
        get_string("compilatio_show_student_score", "plagiarism_compilatio"), $tiioptions);
    $mform->addHelpButton('compilatio_show_student_score', 'compilatio_show_student_score', 'plagiarism_compilatio');
    $mform->addElement('select', 'compilatio_show_student_report',
        get_string("compilatio_show_student_report", "plagiarism_compilatio"), $tiioptions);
    $mform->addHelpButton('compilatio_show_student_report', 'compilatio_show_student_report', 'plagiarism_compilatio');
    if ($mform->elementExists('var4')) {
        $mform->addElement('select', 'compilatio_draft_submit',
            get_string("compilatio_draft_submit", "plagiarism_compilatio"), $compilatiodraftoptions);
    }
    $mform->addElement('select', 'compilatio_studentemail',
        get_string("compilatio_studentemail", "plagiarism_compilatio"), $ynoptions);
    $mform->addHelpButton('compilatio_studentemail', 'compilatio_studentemail', 'plagiarism_compilatio');
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

    // Now update or insert record into compilatio_files.
    $plagiarism_file = $DB->get_record_sql(
                                "SELECT * FROM {plagiarism_compilatio_files}
                                 WHERE cm = ? AND userid = ? AND " .
                                "identifier = ?",
                                array($cmid, $userid, $file->get_contenthash()));
    if (!empty($plagiarism_file)) {
            return $plagiarism_file;
    } else {
        $plagiarism_file = new object();
        $plagiarism_file->cm = $cmid;
        $plagiarism_file->userid = $userid;
        $plagiarism_file->identifier = $file->get_contenthash();
        $plagiarism_file->filename = $file->get_filename();
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
function compilatio_send_file($cmid, $userid, $file, $plagiarismsettings) {
    global $DB;
    $plagiarism_file = compilatio_get_plagiarism_file($cmid, $userid, $file);

    // Check if $plagiarism_file actually needs to be submitted.
    if ($plagiarism_file->statuscode <> 'pending') {
        return true;
    }
    if ($plagiarism_file->filename !== $file->get_filename()) {
        // This is a file that was previously submitted and not sent to compilatio but the filename has changed so fix it.
        $plagiarism_file->filename = $file->get_filename();
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
    }
    // Check to see if this is a valid file.
    $mimetype = compilatio_check_file_type($file->get_filename());
    if (empty($mimetype)) {
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        return true;
    }
    // Check if we need to delay this submission.
    $attemptallowed = compilatio_check_attempt_timeout($plagiarism_file);
    if (!$attemptallowed) {
        return false;
    }
    // Increment attempt number.
    $plagiarism_file->attempt = $plagiarism_file->attempt++;
    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);

    $compid = compilatio_send_file_to_compilatio($plagiarism_file, $plagiarismsettings, $file);
    if ($compid !== false) {
        $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>$cmid), '', 'name, value');
        // Check settings to see if we need to tell compilatio to process this file now.
        if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_AUTO) {
            compilatio_startanalyse($plagiarism_file, $plagiarismsettings);
        }
    }

    return false;
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
    } else if ($plagiarism_file->statuscode ==COMPILATIO_STATUSCODE_ACCEPTED) {
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
    $i= 0;
    while ($i < $plagiarism_file->attempt) {
        if ($wait > $maxsubmissiondelay) {
            $wait = $maxsubmissiondelay;
        }
        $wait = $wait * $plagiarism_file->attempt;
        $i++;
    }
    $wait = (int)$wait*60;
    $timetocheck = (int)($plagiarism_file->timesubmitted +$wait);
    // Calculate when this should be checked next.

    if ($timetocheck < $now) {
        return true;
    } else {
        return false;
    }
}

function compilatio_send_file_to_compilatio(&$plagiarism_file, $plagiarismsettings, $file) {
    global $DB, $CFG;

    $mimetype = compilatio_check_file_type($file->get_filename());
    if (empty($mimetype)) { // Sanity check on filetype - this should already have been checked.
        debugging("no mime type for this file found.");
        return false;
    }
    mtrace("sendfile".$plagiarism_file->id);

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'],
        $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    // Get name from module.
    $modulesql = 'SELECT m.id, m.name, cm.instance'.
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
    $name = format_string($module->name)."_".$plagiarism_file->cm;
    $id_compi = $compilatio->SendDoc($name,                 // Title.
                                     $name,                 // Description.
                                     $file->get_filename(), // File_name.
                                     $mimetype,             // Mime data.
                                     $file->get_content()); // Doc content.

    if (compilatio_valid_md5($id_compi)) {
        $plagiarism_file->externalid = $id_compi;
        $plagiarism_file->attempt = 0; // Reset attempts for status checks.
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
        return $id_compi;
    }
    debugging("invalid compilatio response received - will try again later.".$id_compi);
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
    $filetypes = array('doc'  => 'application/msword',
                       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'xls'  => 'application/excel',
                       'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                       'ppt'  => 'application/vnd.ms-powerpoint',
                       'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                       'pdf'  => 'application/pdf',
                       'txt'  => 'text/plain',
                       'rtf'  => 'application/rtf',
                       'html' => 'text/html',
                       'htm'  => 'text/html',
                       'odt'  => 'application/vnd.oasis.opendocument.text');
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
    $count = 0;
    mtrace("getting Compilatio similarity scores");
    // Get all files set that have been submitted.
    $files = $DB->get_records('plagiarism_compilatio_files', array('statuscode'=>COMPILATIO_STATUSCODE_ANALYSING));
    if (!empty($files)) {
        foreach ($files as $plagiarism_file) {
            // Check if we need to delay this submission.
            $attemptallowed = compilatio_check_attempt_timeout($plagiarism_file);
            if (!$attemptallowed) {
                continue;
            }
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
        $pvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>$cmid), '', 'name,value');
        if (!empty($pvalues['use_compilatio'])) {
            $usecompilatio[$cmid] = $pvalues;
        } else {
            $usecompilatio[$cmid] = false;
        }
    }
    return $usecompilatio[$cmid];
}

/**
 * Function that returns the name of the css class to use for a given similarity score.
 * @param integer $score - the similarity score
 * @return string - string name of css class
 */
function compilatio_get_css_rank ($score) {
    $rank = "none";
    if ($score > 90) {
        $rank = "1";
    } else if ($score > 80) {
        $rank = "2";
    } else if ($score > 70) {
        $rank = "3";
    } else if ($score > 60) {
        $rank = "4";
    } else if ($score > 50) {
        $rank = "5";
    } else if ($score > 40) {
        $rank = "6";
    } else if ($score > 30) {
        $rank = "7";
    } else if ($score > 20) {
        $rank = "8";
    } else if ($score > 10) {
        $rank = "9";
    } else if ($score >= 0) {
        $rank = "10";
    }

    return "rank$rank";
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
    $plagiarismsettings = (array)get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'],
                                 $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    return $compilatio->GetQuotas();
}

function compilatio_startanalyse($plagiarism_file, $plagiarismsettings = '') {
    global $CFG, $DB, $OUTPUT;
    if (empty($plagiarismsettings)) {
        $plagiarismsettings = (array)get_config('plagiarism');
    }
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'],
        $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    $analyse = $compilatio->StartAnalyse($plagiarism_file->externalid);
    if ($analyse === true) {
        // Update plagiarism record.
        $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_ANALYSING;
        $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
    } else {
        echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio').$analyse->string);
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

function compilatio_check_analysis($plagiarism_file) {
    global $CFG, $DB;
    $plagiarismsettings = (array)get_config('plagiarism');
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api'],
        $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);

    $docstatus = $compilatio->GetDoc($plagiarism_file->externalid);
    if (isset($docstatus->documentStatus->status)) {
        if ($docstatus->documentStatus->status == "ANALYSE_COMPLETE") {
            $plagiarism_file->statuscode = COMPILATIO_STATUSCODE_COMPLETE;
            $plagiarism_file->similarityscore = round($docstatus->documentStatus->indice);
            // Now get report url.
            $plagiarism_file->reporturl = $compilatio->GetReportUrl($plagiarism_file->externalid);
            $emailstudents = $DB->get_field('plagiarism_compilatio_config', 'value',
                array('cm'=>$plagiarism_file->cm, 'name'=>'compilatio_studentemail'));
            if (!empty($emailstudents)) {
                $compilatio = new plagiarism_plugin_compilatio();
                $compilatio->compilatio_send_student_email($plagiarism_file);
            }
        }
    }
    $plagiarism_file->attempt = $plagiarism_file->attempt+1;
    $DB->update_record('plagiarism_compilatio_files', $plagiarism_file);
}


//function to check for invalid event_handlers
function compilatio_check_event_handlers() {
    global $DB, $CFG;
    $invalidhandlers = array();
    $eventhandlers = $DB->get_records('events_handlers');
    foreach ($eventhandlers as $handler) {
        $function = unserialize($handler->handlerfunction);

        if (is_callable($function)) { //this function is fine.
            continue;
        } else if (file_exists($CFG->dirroot.$handler->handlerfile)) {
            include_once($CFG->dirroot.$handler->handlerfile);
            if (is_callable($function)) { //this function is fine.
                continue;
            }
        }
        $invalidhandlers[] = $handler; //this function can't be found.
    }
    return $invalidhandlers;
}

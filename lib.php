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

// Get global class.
global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/api.class.php');

// Get helper class.
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/output_helper.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/csv_helper.php');

// Get constants.
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

/**
 * Compilatio Class
 * @copyright  2012 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_plugin_compilatio extends plagiarism_plugin
{

    /**
     * Green threshold
     * @var null
     */
    private $_green_threshold_cache = null;

    /**
     * Orange threshold
     * @var null
     */
    private $_orange_threshold_cache = null;

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

        $plagiarismsettings = (array) get_config('plagiarism');
        // Check if compilatio enabled.
        if (isset($plagiarismsettings['compilatio_use']) && $plagiarismsettings['compilatio_use']) {
            // Now check to make sure required settings are set!.
            if (empty($plagiarismsettings['compilatio_password'])) {
                error("Compilatio API URL not set!");
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
            'compilatio_show_student_score',
            'compilatio_show_student_report',
            'compilatio_draft_submit',
            'compilatio_studentemail',
            'compilatio_timeanalyse',
            'compilatio_analysistype',
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

        // Check if Compilatio is enabled in this course module.
        if (compilatio_enabled($linkarray['cmid']) == false) {
            return '';
        }

        global $DB, $CFG, $PAGE;
        $output = '';

        // DOM Compilatio index for ajax callback.
        static $domid = 0;
        $domid++;

        $cm = get_coursemodule_from_id(null, $linkarray['cmid']);
        $modulecontext = context_module::instance($linkarray['cmid']);

        // If the user has permission to see result of all items in this course module.
        $teacher = has_capability('plagiarism/compilatio:viewreport', $modulecontext);
        $indexingstate = null;

        // Get submiter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In asigns.
            $userid = $DB->get_field('assign_submission', 'userid', array('id' => $linkarray['file']->get_itemid()));
        }

        // Make file object.
        $file = new stdClass();
        $file->timestamp = time();

        if (!empty($linkarray['content'])) {
            $file->filename = "content-" . $cm->course . "-" . $linkarray['cmid'] . "-" . $userid . ".htm";
            // Filename is not reliable for posts (forum) !
            $file->identifier = sha1($linkarray['content']);
            $file->filepath = $CFG->dataroot . "/temp/compilatio/" . $file->filename;
            $file->type = "tempcompilatio";
        } else if (!empty($linkarray['file'])) {
            $file->filename = $linkarray['file']->get_filename();
            $file->identifier = $linkarray['file']->get_contenthash();
            $file->filepath = $linkarray['file']->get_filepath();
        } else {
            return $output;
        }

        // Get result in DB if exists.
        $results = $this->get_file_results($linkarray['cmid'], $userid, $file);

        // Add de/indexing feature for teachers.
        if (!empty($results['externalid']) && $teacher) {
            // Ajax API call.
            $PAGE->requires->js_call_amd('plagiarism_compilatio/ajax_api', 'getIndexingState',
                array($CFG->httpswwwroot, $domid, $results['externalid']));
        }

        // No results in DB yet.
        if (empty($results)) {

            if ($teacher) {
                // Only works for assign.
                if (!isset($linkarray["file"]) || $cm->modname != 'assign') {
                    return $output;
                }
                // Catch GET 'sendfile' param.
                $trigger = optional_param('sendfile', 0, PARAM_INT);
                $fileid = $linkarray["file"]->get_id();

                if ($trigger == $fileid) {
                    $res = $DB->get_record("files", array("id" => $fileid));

                    if (!defined("COMPILATIO_MANUAL_SEND")) {
                        define("COMPILATIO_MANUAL_SEND", true); // Hack to hide mtrace in function execution.
                        compilatio_upload_files(array($res), $linkarray['cmid']);
                        return $output . $this->get_links($linkarray);
                    } else {
                        return $output;
                    }
                }
                $moodleurl = new moodle_url("/mod/assign/view.php",
                    array("id" => $linkarray['cmid'], "sendfile" => $fileid, "action" => "grading"));
                $url = array("url" => "$moodleurl", "target-blank" => false);
                $spancontent = get_string("analyze", "plagiarism_compilatio");
                $image = "play";
                $title = get_string('startanalysis', 'plagiarism_compilatio');
                $output .= output_helper::get_plagiarism_area($spancontent, $image, $title, "",
                    $url, false, $indexingstate, $domid);
                return $output;
            } else {
                return '';
            }
        }
        // Catch GET 'compilatioprocess' param.
        $trigger = optional_param('compilatioprocess', 0, PARAM_INT);

        if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED && $trigger == $results['pid']) {
            if (has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext)) {
                // Trigger manual analysis call.
                $plagiarismfile = $DB->get_record('plagiarism_compilatio_files', array('id' => $trigger));
                $analyse = compilatio_startanalyse($plagiarismfile);
                if ($analyse === true) {
                    // Update plagiarism record.
                    $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
                    $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
                    $spancontent = get_string("queue", "plagiarism_compilatio");
                    $image = "queue";
                    $title = get_string('queued', 'plagiarism_compilatio');
                    $output .= output_helper::get_plagiarism_area($spancontent, $image, $title, "",
                        array(), false, $indexingstate, $domid);
                } else {
                    $output .= '<span class="plagiarismreport">' .
                        '</span>';
                }
                return $output;
            }
        }
        if ($results['statuscode'] == 'pending') {
            $spancontent = get_string("pending_status", "plagiarism_compilatio");
            $image = "hourglass";
            $title = get_string('pending', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($spancontent, $image, $title, "",
                array(), false, $indexingstate, $domid);

            return $output;
        }
        if ($results['statuscode'] == 'Analyzed') {
            // Normal situation - Compilatio has successfully analyzed the file.
            // Cache Thresholds values.
            if ($this->_green_threshold_cache == null || $this->_orange_threshold_cache == null) {
                $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config',
                    array('cm' => $linkarray['cmid']), '', 'name, value');

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
                // User is allowed to view the report.
                // Score is contained in report, so they can see the score too.
                $url = $results['reporturl'];
                $append = output_helper::get_image_similarity($results['score'],
                    $this->_green_threshold_cache, $this->_orange_threshold_cache);
            } else if ($results['score'] !== '') {
                // User is allowed to view only the score.
                $append = output_helper::get_image_similarity($results['score'],
                    $this->_green_threshold_cache, $this->_orange_threshold_cache);
            }
            $title = get_string("analysis_completed", 'plagiarism_compilatio', $results['score']);
            $url = array("target-blank" => true, "url" => $url);
            $output .= output_helper::get_plagiarism_area("", "", $title, $append, $url,
                false, null, $domid);
            if (!empty($results['renamed'])) {
                $output .= $results['renamed'];
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_IN_QUEUE) {
            $spancontent = get_string("queue", "plagiarism_compilatio");
            $image = "queue";
            $title = get_string('queued', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($spancontent, $image, $title, "",
                array(), false, $indexingstate, $domid);
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config',
                array('cm' => $linkarray['cmid']), '', 'name, value');
            $title = "";
            $span = "";
            $url = "";
            $image = "";

            // Check settings to see if we need to tell compilatio to process this file now.
            // Check if this is a timed release and add hourglass image.
            if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_PROG) {
                $image = "prog";
                $span = get_string('planned', 'plagiarism_compilatio');
                $title = get_string('waitingforanalysis', 'plagiarism_compilatio',
                    userdate($plagiarismvalues['compilatio_timeanalyse']));
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
            } else if ($results['score'] !== '') { // If score === "" => Student, not allowed to see.
                $image = "inprogress";
                $title = get_string('processing_doc', 'plagiarism_compilatio');
            }
            if ($title !== "") {
                $url = array("target-blank" => false, "url" => $url);
                $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                    $url, false, $indexingstate, $domid);
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ANALYSING) {
            $span = get_string("analyzing", "plagiarism_compilatio");
            $image = "inprogress";
            $title = get_string('processing_doc', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                array(), false, $indexingstate, $domid);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNSUPPORTED) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unsupportedfiletype', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                "", true, $indexingstate, $domid);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_LARGE) {
            $size = ws_helper::get_allowed_file_max_size();
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('toolarge', 'plagiarism_compilatio', $size);
            $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                "", true, $indexingstate, $domid);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNEXTRACTABLE) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unextractablefile', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                "", true, $indexingstate, $domid);

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
                $url = new moodle_url('/plagiarism/compilatio/reset.php',
                    array('cmid' => $linkarray['cmid'], 'pf' => $results['pid'], 'sesskey' => sesskey()));
                $reset = "<a class='reinit' href='$url'>" . get_string('reset') . "</a>";
            }
            $span = get_string('reset', "plagiarism_compilatio");
            $url = array("target-blank" => false, "url" => $url);
            $image = "exclamation";
            $output .= output_helper::get_plagiarism_area($span, $image, $title, "",
                $url, true, $indexingstate, $domid);
        }
        return $output;
    }

    /**
     * Get file result in DB
     *
     * @param  int     $cmid    Course module ID
     * @param  int     $userid  User ID
     * @param  object  $file    File
     * @return boolean          Return true if succeed, false otherwise
     */
    public function get_file_results($cmid, $userid, $file) {

        // Check if plugin is enabled for this cm.
        $plugincm = compilatio_cm_use($cmid);
        if (empty($plugincm['use_compilatio']) || $plugincm['use_compilatio'] != '1') {
            return false;
        }

        global $DB, $USER, $CFG;

        // Collect detail about the specified coursemodule.
        $filehash = $file->identifier;
        $cm = get_coursemodule_from_id(null, $cmid);

        if (!empty($cm)) {
            $sql = "SELECT * FROM {" . $cm->modname . "} WHERE id= ?";
            $module = $DB->get_record_sql($sql, array($cm->instance));
        }
        if (empty($module)) { // No such cmid.
            return false;
        }

        $modulecontext = context_module::instance($cmid);
        // If the user has permission to see result of all items in this course module.
        $viewscore = $viewreport = has_capability('plagiarism/compilatio:viewreport', $modulecontext);

        // Determine if the activity is closed.
        // If report is closed, this can make the report available to more users.
        $assignclosed = false;
        $time = time();
        if ($cm->completionexpected != 0 && $time > $cm->completionexpected) {
            $assignclosed = true;
        }

        // Under certain circumstances, users are allowed to see plagiarism info
        // even if they don't have view report capability.
        if ($USER->id == $userid) {
            $selfreport = true;
            if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") === '1' &&
                isset($plugincm['compilatio_show_student_report']) &&
                ($plugincm['compilatio_show_student_report'] == PLAGIARISM_COMPILATIO_SHOW_ALWAYS ||
                    $plugincm['compilatio_show_student_report'] == PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
                $viewreport = true;
            }
            if (isset($plugincm['compilatio_show_student_score']) &&
                ($plugincm['compilatio_show_student_score'] == PLAGIARISM_COMPILATIO_SHOW_ALWAYS) ||
                ($plugincm['compilatio_show_student_score'] == PLAGIARISM_COMPILATIO_SHOW_CLOSED && $assignclosed)) {
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

        // Get compilatio file record.
        $plagiarismfile = $DB->get_record('plagiarism_compilatio_files',
            array('cm' => $cmid, 'userid' => $userid, 'identifier' => $filehash));

        if (empty($plagiarismfile)) { // Try to get record without userid in forums.
            $sql = "SELECT * FROM {plagiarism_compilatio_files}
                WHERE timesubmitted = (SELECT max(timesubmitted) FROM {plagiarism_compilatio_files}
                WHERE cm = ? AND identifier = ?)
                AND cm = ? AND identifier = ?";
            $plagiarismfile = $DB->get_record_sql($sql, array($cmid, $filehash, $cmid, $filehash));
            if (empty($plagiarismfile)) {
                return false;
            }
        }

        // Returns after this point will include a result set describing information about.
        // interactions with compilatio servers.
        $results = array(
            'statuscode' => '',
            'error' => '',
            'reporturl' => '',
            'score' => '',
            'pid' => '',
            'renamed' => '',
            'analyzed' => 0,
            'externalid' => $plagiarismfile->externalid
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
            if ($viewscore) {
                // If user can see the report, they can see the score on the report
                // so make it directly available.
                $results['score'] = $plagiarismfile->similarityscore;
            }
            if ($viewscore && $viewreport) {
                $results['reporturl'] = $plagiarismfile->reporturl;
                $results['score'] = $plagiarismfile->similarityscore;
            }
            $results['renamed'] = $previouslysubmitted;
        }
        return $results;
    }

    /**
     * Hook to save plagiarism specific settings on a module settings page
     *
     * @param object $data - data from an mform submission
     */
    public function save_form_elements($data) {

        global $DB;

        if (!$this->get_settings()) {
            return null;
        }

        if (isset($data->use_compilatio)) {
            // Array of possible plagiarism config options.
            $plagiarismelements = $this->config_options();

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

            if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") !== '1') {
                $data->compilatio_show_student_report = PLAGIARISM_COMPILATIO_SHOW_NEVER;
            }

            // First get existing values.
            $existingelements = $DB->get_records_menu('plagiarism_compilatio_config', // Table.
                array('cm' => $data->coursemodule), // Where.
                '', // Order by.
                'name, id'); // Select.

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
            // Check if we are changing from timed or manual to instant.
            // If changing to instant, make all existing files to get a report.
            if (isset($existingelements['compilatio_analysistype']) &&
                $existingelements['compilatio_analysistype'] !== $data->compilatio_analysistype
                && $data->compilatio_analysistype == COMPILATIO_ANALYSISTYPE_AUTO) {
                // Get all existing files in this assignment set to manual status.
                $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files',
                    array('cm' => $data->coursemodule, 'statuscode' => COMPILATIO_STATUSCODE_ACCEPTED));
                compilatio_analyse_files($plagiarismfiles);
            }
        }
    }

    /**
     * Hook to add plagiarism specific settings to a module settings page
     *
     * @param  object $mform      Moodle form
     * @param  object $context    current context
     * @param  string $modulename Module name
     * @return void
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
                return; // Return if compilatio is not enabled for the module.
            }
        }
        if (!empty($cmid)) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
        }
        // The cmid(0) is the default list.
        $plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => 0), '', 'name, value');
        $plagiarismelements = $this->config_options();
        if (has_capability('plagiarism/compilatio:enable', $context)) {
            compilatio_get_form_elements($mform, false, $modulename);
            if ($mform->elementExists('compilatio_draft_submit')) {
                if ($mform->elementExists('var4')) {
                    $mform->disabledIf('compilatio_draft_submit', 'var4', 'eq', 0);
                } else if ($mform->elementExists('submissiondrafts')) {
                    $mform->disabledIf('compilatio_draft_submit', 'submissiondrafts', 'eq', 0);
                }
            }
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
     * Hook to allow status of submitted files to be updated - called on grading/report pages
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     * @return string
     */
    public function update_status($course, $cm) {

        global $CFG, $PAGE, $OUTPUT, $DB, $SESSION;

        $alerts = array();
        $plagiarismsettings = (array) get_config('plagiarism');
        $output = '';

        $export = optional_param('compilatio_export', '', PARAM_BOOL);
        if ($export) {
            csv_helper::generate_assign_csv($cm->id);
        }

        // Store plagiarismfiles in $SESSION.
        $sql = "cm = ? AND externalid IS NOT null";
        $params = array($cm->id);
        $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);
        $plagiarismfilesids = array_keys($SESSION->compilatio_plagiarismfiles);

        // Handle the action of the button when course is set on manual analysis.
        $startallanalysis = optional_param('compilatiostartanalysis', '', PARAM_BOOL);
        if ($startallanalysis) {
            $sql = "cm = ? AND name='compilatio_analysistype'";
            $params = array($cm->id);
            $record = $DB->get_record_select('plagiarism_compilatio_config', $sql, $params);

            // Counter incremented on success.
            $countsuccess = 0;
            $plagiarismfiles = array();
            $docsfailed = array();
            if ($record != null && $record->value == COMPILATIO_ANALYSISTYPE_MANUAL) {
                $sql = "cm = ? AND statuscode = ?";
                $params = array($cm->id, COMPILATIO_STATUSCODE_ACCEPTED);
                $plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);

                foreach ($plagiarismfiles as $file) {
                    if (compilatio_startanalyse($file)) {
                        $countsuccess++;
                    } else {
                        $docsfailed[] = $file["filename"];
                    }
                }
            }

            // Handle not sent documents :.
            $files = compilatio_get_non_uploaded_documents($cm->id);
            $countbegin = count($files);

            if ($countbegin != 0) {

                define("COMPILATIO_MANUAL_SEND", true);
                compilatio_upload_files($files, $cm->id);
                $countsuccess += $countbegin - count(compilatio_get_non_uploaded_documents($cm->id));

            }

            $counttotal = count($plagiarismfiles) + $countbegin;
            $counterrors = count($docsfailed);

            if ($counttotal === 0) {

                $alerts[] = array(
                    "class" => "info",
                    "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
                    "content" => get_string("no_document_available_for_analysis", "plagiarism_compilatio"),
                );

            } else if ($counterrors === 0) {

                $alerts[] = array(
                    "class" => "info",
                    "title" => get_string("start_analysis_title", "plagiarism_compilatio"),
                    "content" => get_string("analysis_started", "plagiarism_compilatio", $countsuccess),
                );

            } else {

                $alerts[] = array(
                    "class" => "danger",
                    "title" => get_string("not_analyzed", "plagiarism_compilatio"),
                    "content" => "<ul><li>" . implode("</li><li>", $docsfailed) . "</li></ul>",
                );
            }
        }

        // Handle restart failed document analysis.
        $restartfailedanalysis = optional_param('restartfailedanalysis', '', PARAM_BOOL);
        if ($restartfailedanalysis) {

            // Resend failed files.
            $params = array(
                'cm' => $cm->id,
                'statuscode' => COMPILATIO_STATUSCODE_UNEXTRACTABLE,
            );
            $docsmaxattempsreached = array();
            $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files', $params);
            foreach ($plagiarismfiles as $plagiarismfile) {
                if ($plagiarismfile->attempt < COMPILATIO_MAX_SUBMISSION_ATTEMPTS) {
                    $plagiarismfile->statuscode = 'pending';
                    $plagiarismfile->attempt++;
                    $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
                } else {
                    $docsmaxattempsreached[] = $plagiarismfile->filename;
                }
            }
            compilatio_send_pending_files($plagiarismsettings);

            // Restart analyses.
            $countsuccess = 0;
            $docsfailed = array();
            $plagiarismvalues = $DB->get_records('plagiarism_compilatio_config', array('cm' => $cm->id));
            if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_AUTO) {
                $countsuccess = count($plagiarismfiles);
            } else {
                $params['statuscode'] = COMPILATIO_STATUSCODE_ACCEPTED;
                $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files', $params);
                foreach ($plagiarismfiles as $plagiarismfile) {
                    if (compilatio_startanalyse($plagiarismfile)) {
                        $countsuccess++;
                    } else {
                        $docsfailed[] = $plagiarismfile->filename;
                    }
                }
            }

            $counterrors = count($docsfailed);
            if ($counterrors === 0) {
                $alerts[] = array(
                    "class" => "info",
                    "title" => get_string("restart_failed_analysis_title", "plagiarism_compilatio"),
                    "content" => get_string("analysis_started", "plagiarism_compilatio", $countsuccess),
                );
            } else {
                $alerts[] = array(
                    "class" => "danger",
                    "title" => get_string("not_analyzed", "plagiarism_compilatio"),
                    "content" => "<ul><li>" . implode("</li><li>", $docsfailed) . "</li></ul>",
                );
            }

            $countmaxattemptsreached = count($docsmaxattempsreached);
            $files = compilatio_get_max_attempts_files($cm->id);
            if ($countmaxattemptsreached !== 0) {
                $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
                $alerts[] = array(
                    "class" => "danger",
                    "title" => get_string("max_attempts_reach_files", "plagiarism_compilatio"),
                    "content" => $list,
                );

            }
        }

        $compilatioenabled = $plagiarismsettings["compilatio_use"] && $plagiarismsettings["compilatio_enable_mod_assign"];
        $sql = "select value from {plagiarism_compilatio_config} where cm=? and name='use_compilatio'";
        $activecompilatio = $DB->get_record_sql($sql, array($cm->id));
        // Compilatio not enabled, return.

        if ($activecompilatio === false) {
            // Plagiarism settings have not been saved :.
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
            // Get the new status.
            $activecompilatio = $DB->get_record_sql($sql, array($cm->id));
        }

        if ($activecompilatio == null ||
            $activecompilatio->value != 1 ||
            !$compilatioenabled) {
            return "";
        }

        // Get compilatio analysis type.
        $sql = "cm = ? AND name='compilatio_analysistype'";
        $params = array($cm->id);
        $record = $DB->get_record_select('plagiarism_compilatio_config', $sql, $params);
        $value = $record->value;

        if ($value == COMPILATIO_ANALYSISTYPE_MANUAL) { // Display a button that start all the analysis of the activity.

            $url = $PAGE->url;
            $url->param('compilatiostartanalysis', true);
            $startallanalysisbutton = "
    			<a href='$url' class='compilatio-button button' >
                    <i class='fa fa-play-circle'></i>
                    " . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
                </a>";

        } else if ($value == COMPILATIO_ANALYSISTYPE_PROG) { // Display the date of analysis if its type is set on 'Timed'.
            // Get analysis date :.
            $sql = "cm = ? AND name='compilatio_timeanalyse'";
            $params = array($cm->id);
            $plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_config', $sql, $params);
            $record = reset($plagiarismfiles); // Get the first value of the array.
            $date = userdate($record->value);
            if ($record->value > time()) {
                $programmedanalysisdate = get_string("programmed_analysis_future", "plagiarism_compilatio", $date);
            } else {
                $programmedanalysisdate = get_string("programmed_analysis_past", "plagiarism_compilatio", $date);
            }
        }

        // Get the DB record containing the webservice status :.
        $oldconnectionstatus = $DB->get_record('plagiarism_compilatio_data', array('name' => 'connection_webservice'));
        // If the record exists and if the webservice is marked as unreachable in Cron function :.
        if ($oldconnectionstatus != null && $oldconnectionstatus->value === '0') {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("webservice_unreachable_title", "plagiarism_compilatio"),
                "content" => get_string("webservice_unreachable_content", "plagiarism_compilatio"));
        }

        // Display a notification of the unsupported files.
        $files = compilatio_get_unsupported_files($cm->id);
        if (count($files) !== 0) {
            $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsupported_files", "plagiarism_compilatio"),
                "content" => $list,
            );
        }

        // Display a notification for the unextractable files.
        $files = compilatio_get_unextractable_files($cm->id);
        if (count($files) !== 0) {

            $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";

            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unextractable_files", "plagiarism_compilatio"),
                "content" => $list,
            );

            $url = $PAGE->url;
            $url->param('restartfailedanalysis', true);
            $restartfailedanalysisbutton = "
                <a href='$url' class='compilatio-button button' >
                    <i class='fa fa-play-circle'></i>
                    " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
                </a>";
        }

        // If the account expires within the month, display an alert :.
        if (compilatio_check_account_expiration_date()) {
            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("account_expire_soon_title", "plagiarism_compilatio"),
                "content" => get_string("account_expire_soon_content", "plagiarism_compilatio"),
            );
        }

        $documentsnotuploaded = compilatio_get_non_uploaded_documents($cm->id);
        if (count($documentsnotuploaded) !== 0) {

            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsent_documents", "plagiarism_compilatio"),
                "content" => get_string("unsent_documents_content", "plagiarism_compilatio"),
            );

            $url = $PAGE->url;
            $url->param('compilatiostartanalysis', true);
            $startallanalysisbutton = "
    			<a href='$url' class='compilatio-button button' >
    				<i class='fa fa-play-circle'></i>
    				" . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
    			</a>";

            $url = $PAGE->url;
            $url->param('restartfailedanalysis', true);
            $restartfailedanalysisbutton = "
                <a href='$url' class='compilatio-button button' >
                    <i class='fa fa-play-circle'></i>
                    " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
                </a>";
        }

        // Add the Compilatio news to the alerts displayed :.
        $alerts = array_merge($alerts, compilatio_display_news());

        // Include JQuery.
        $output .= output_helper::get_jquery();

        // Include Font.
        $fontawesomeurl = new moodle_url("/plagiarism/compilatio/fonts/font-awesome.css");
        $output .= "<link rel='stylesheet' href='$fontawesomeurl'>";

        $output .= "<div id='compilatio-container'>";

        // Display logo.
        $output .= output_helper::get_logo();

        // Display the tabs: Notification tab will be hidden if there is 0 alerts.
        $output .= "<div id='compilatio-tabs' style='display:none'>";

        // Help icon.
        $output .= "<a title=\"" . get_string("compilatio_help_assign", "plagiarism_compilatio") .
            "\" id='show-help' class='compilatio-icon' href='../../plagiarism/compilatio/helpcenter.php'" .
            "target='_blank' ><i class='fa fa-question-circle fa-2x'></i></a>";

        // Stat icon.
        $output .= "<div id='show-stats' class='compilatio-icon'  title='" .
        get_string("display_stats", "plagiarism_compilatio") .
            "'><i class='fa fa-bar-chart fa-2x'></i></div>";

        // Alert icon.
        if (count($alerts) !== 0) {
            $output .= "<div id='show-notifications' title='" . get_string("display_notifications", "plagiarism_compilatio") .
                "' class='compilatio-icon active' ><i class='fa fa-bell fa-2x'></i>";
            $output .= "<span>" . count($alerts) . "</span>";
            $output .= "</div>";
        }

        // Hide/Show button.
        $output .= "
            <div id='compilatio-hide-area' class='compilatio-icon'  title='" .
        get_string("hide_area", "plagiarism_compilatio") . "'>
                <i class='fa fa-chevron-up fa-2x'></i>
            </div>";

        $output .= "</div>";

        $output .= "<script>";
        // Focus on notifications if there is any.
        $output .= "var selectedElement = ";
        if (count($alerts) !== 0) {
            $output .= "'#compilatio-notifications';";
        } else {
            $output .= "'#compilatio-home';";
        }

        // JQuery Script to handle click on the tabs.
        $output .= "
            $(document).ready(function(){
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

            });";

        $output .= "</script>";

        $output .= "<div class='clear'></div>";

        // Home tab.
        $output .= "
			<div id='compilatio-home'>
				<p>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>
			</div>";

        // Help tab.
        $output .= "
            <div id='compilatio-help'>
                <p>" . get_string('goto_helpcenter', 'plagiarism_compilatio') . "</p>
            </div>";

        // Stats tab.
        $output .= "
            <div id='compilatio-stats'>
                <h5>" . get_string("tabs_title_stats", "plagiarism_compilatio") . " : </h5>" .
        compilatio_get_statistics($cm->id) .
            "</div>";

        // Alerts tab.
        if (count($alerts) !== 0) {
            $output .= "<div id='compilatio-notifications'>";
            $output .= "<h5>" . get_string("tabs_title_notifications", "plagiarism_compilatio") . " : </h5>";

            foreach ($alerts as $alert) {
                $output .= "
                    <div class='alert alert-" . $alert["class"] . "'>" .
                    "<strong>" . $alert["title"] . "</strong><br/>" .
                    $alert["content"] .
                    "</div>";
            }

            $output .= "</div>";
        }

        // Display timed analysis date.
        if (isset($programmedanalysisdate)) {
            $output .= "<p id='programmed-analysis'>$programmedanalysisdate</p>";
        }

        $output .= "</div>";

        // Display buttons :.
        $output .= "<div id='button-container'>";

        // Update button.
        $url = $PAGE->url;
        $url->param('compilatioupdate', true);
        $output .= "
    		<button class='compilatio-button button'>
    				<i class='fa fa-refresh'></i>
    				" . get_string('updatecompilatioresults', 'plagiarism_compilatio') . "
    		</button>";
        $PAGE->requires->js_call_amd('plagiarism_compilatio/ajax_api', 'refreshButton',
            array($CFG->httpswwwroot, $plagiarismfilesids, get_string('update_in_progress', 'plagiarism_compilatio')));

        // Start all analysis button.
        if (isset($startallanalysisbutton)) {
            $output .= $startallanalysisbutton;
        }

        if (isset($restartfailedanalysisbutton)) {
            $output .= $restartfailedanalysisbutton;
        }

        $output .= "</div>";

        return $output;
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
 * Event trigger timed analyses
 *
 * @return void
 */
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

/**
 * Event update meta
 *
 * @return void
 */
function compilatio_update_meta() {

    global $DB;

    // Send data about plugin version to Compilatio.
    compilatio_send_statistics();

    // Update the expiration date in the DB.
    compilatio_update_account_expiration_date();

    // Get most recent news from Compilatio :.
    compilatio_update_news();

    // Update the "Compilatio unavailable" marker in the database.
    compilatio_update_connection_status();
}

/**
 * Send pending files
 *
 * @param  array $plagiarismsettings Settings
 * @return void
 */
function compilatio_send_pending_files($plagiarismsettings) {

    $fs = get_file_storage();
    global $DB;

    $lastcron = compilatio_update_cron_frequency();
    // Keep track of the last datetime of execution.
    compilatio_update_last_cron_date($lastcron);

    if (!empty($plagiarismsettings)) {

        // Get all files in a pending state.
        $plagiarismfiles = $DB->get_records("plagiarism_compilatio_files", array("statuscode" => "pending"));

        foreach ($plagiarismfiles as $plagiarismfile) {

            $indexingstate = $DB->get_record("plagiarism_compilatio_config",
                array("cm" => $plagiarismfile->cm, "name" => "indexing_state"),
                'name, value');
            if ($indexingstate === false) {
                $indexingstate = $DB->get_record("plagiarism_compilatio_config",
                    array("cm" => 0, "name" => "indexing_state"),
                    'name, value');
                if ($indexingstate === false) {
                    $indexingstate->value = true;
                }
            }

            $tmpfile = compilatio_get_temp_file($plagiarismfile->filename);

            if ($tmpfile !== false) {
                $compid = compilatio_send_file_to_compilatio($plagiarismfile,
                    $plagiarismsettings,
                    $tmpfile);

                ws_helper::set_indexing_state($compid, $indexingstate->value);

                compilatio_start_if_direct_analysis($plagiarismfile,
                    $plagiarismfile->cm,
                    $plagiarismsettings);

                unlink($tmpfile->filepath);
            } else {

                // Not a temporary file.
                $modulecontext = context_module::instance($plagiarismfile->cm);
                $contextid = $modulecontext->id;
                $sql = "SELECT * FROM {files} f WHERE f.contenthash= ? AND contextid = ?";
                $f = $DB->get_record_sql($sql, array($plagiarismfile->identifier, $contextid));
                if (empty($f)) {
                    continue;
                }
                $file = $fs->get_file_by_id($f->id);

                $compid = compilatio_send_file_to_compilatio($plagiarismfile,
                    $plagiarismsettings,
                    $file);

                ws_helper::set_indexing_state($compid, $indexingstate->value);

                compilatio_start_if_direct_analysis($plagiarismfile,
                    $plagiarismfile->cm,
                    $plagiarismsettings);
            }
        }
    }
}

/**
 * Start an analyse if analysis set up on direct
 *
 * @param  object $plagiarismfile     File
 * @param  int    $cmid               Course module ID
 * @param  array  $plagiarismsettings Settings
 * @return void
 */
function compilatio_start_if_direct_analysis($plagiarismfile, $cmid, $plagiarismsettings) {

    global $DB;
    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    // Check settings to see if we need to tell compilatio to process this file now.
    if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_AUTO) {
        compilatio_startanalyse($plagiarismfile, $plagiarismsettings);
    }
}

/**
 * Get temporary file
 *
 * @param  string $filename File name
 * @return mixed            Return a file object if succeed, false oterwise
 */
function compilatio_get_temp_file($filename) {

    global $CFG;
    $filepath = $CFG->dataroot . "/temp/compilatio/" . $filename;
    if (!file_exists($filepath)) {
        return false;
    }
    $file = new stdclass();
    $file->type = "tempcompilatio";
    $file->filename = $filename;
    $file->identifier = sha1_file($filepath);
    $file->filepath = $filepath;
    return $file;
}

/**
 * Create a temporary file
 *
 * @param  int    $cmid      Course module ID
 * @param  object $eventdata Event data
 * @return object            Return a file object
 */
function compilatio_create_temp_file($cmid, $eventdata) {

    global $CFG;

    if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
        mkdir($CFG->dataroot . "/temp/compilatio", 0700);
    }

    if ($eventdata->postid != null) {
        $filename = "post-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->postid . ".htm";
    } else {
        $filename = "content-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->userid . ".htm";
    }

    $filepath = $CFG->dataroot . "/temp/compilatio/" . $filename;

    $fd = fopen($filepath, 'wb'); // Create if not exist, write binary.
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
 * Adds the list of plagiarism settings to a form.
 *
 * @param object  $mform    Moodle form object
 * @param boolean $defaults if this is being loaded from defaults form or from inside a mod.
 * @param string  $modulename
 */
function compilatio_get_form_elements($mform, $defaults = false, $modulename='') {

    $ynoptions = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );

    $tiioptions = array(
        0 => get_string("never"),
        1 => get_string("immediately", "plagiarism_compilatio"),
        2 => get_string("showwhenclosed", "plagiarism_compilatio"),
    );

    $compilatiodraftoptions = array(
        PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE => get_string("submitondraft", "plagiarism_compilatio"),
        PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL => get_string("submitonfinal", "plagiarism_compilatio"),
    );

    $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));
    $mform->addElement('select', 'use_compilatio', get_string("use_compilatio", "plagiarism_compilatio"), $ynoptions);
    $mform->setDefault('use_compilatio', 1);

    $analysistypes = array(COMPILATIO_ANALYSISTYPE_AUTO => get_string('analysistype_direct', 'plagiarism_compilatio'),
        COMPILATIO_ANALYSISTYPE_MANUAL => get_string('analysistype_manual', 'plagiarism_compilatio'),
        COMPILATIO_ANALYSISTYPE_PROG => get_string('analysistype_prog', 'plagiarism_compilatio'));
    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'compilatio_analysistype',
            get_string('analysis_type', 'plagiarism_compilatio'),
            $analysistypes);
        $mform->addHelpButton('compilatio_analysistype', 'analysis_type', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_analysistype', COMPILATIO_ANALYSISTYPE_MANUAL);
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector',
            'compilatio_timeanalyse',
            get_string('analysis_date', 'plagiarism_compilatio'),
            array('optional' => false));
        $mform->setDefault('compilatio_timeanalyse', time() + 7 * 24 * 3600);
        $mform->disabledif('compilatio_timeanalyse', 'compilatio_analysistype', 'noteq', COMPILATIO_ANALYSISTYPE_PROG);
    }

    $mform->addElement('select', 'compilatio_show_student_score',
        get_string("compilatio_display_student_score", "plagiarism_compilatio"),
        $tiioptions);
    $mform->addHelpButton('compilatio_show_student_score', 'compilatio_display_student_score', 'plagiarism_compilatio');
    if (get_config("plagiarism", "compilatio_allow_teachers_to_show_reports") === '1') {
        $mform->addElement('select', 'compilatio_show_student_report',
            get_string("compilatio_display_student_report", "plagiarism_compilatio"),
            $tiioptions);
        $mform->addHelpButton('compilatio_show_student_report', 'compilatio_display_student_report', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }
    if ($mform->elementExists('var4') ||
        $mform->elementExists('submissiondrafts')) {
        $mform->addElement('html', '<div style="display:none">');
        $mform->addElement('select', 'compilatio_draft_submit',
            get_string("compilatio_draft_submit", "plagiarism_compilatio"),
            $compilatiodraftoptions);
        $mform->addElement('html', '</div>');
    }
    $mform->addElement('select', 'compilatio_studentemail',
        get_string("compilatio_studentemail", "plagiarism_compilatio"),
        $ynoptions);
    $mform->addHelpButton('compilatio_studentemail', 'compilatio_studentemail', 'plagiarism_compilatio');

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

    // Max file size allowed.
    $size = ws_helper::get_allowed_file_max_size();
    $mform->addElement('html', '<p>' . get_string("max_file_size_allowed", "plagiarism_compilatio", $size) . '</p>');

    // File types allowed.
    $filetypes = ws_helper::get_allowed_file_types();
    $mform->addElement('html', '<div>' . get_string("help_compilatio_format_content", "plagiarism_compilatio") . '</div>');
    $mform->addElement('html', '<table style="margin-left:10px;"><tbody>');
    foreach ($filetypes as $filetype) {
        $mform->addElement('html', '<tr><td style="padding-right:25px;">.' . $filetype['type'] .
            '</td><td>' . $filetype['title'] . '</td></tr>');
    }
    $mform->addElement('html', '</tbody></table>');

    // Used to append text nicely after the inputs. If Javascript is disabled, it will be displayed on the line below the input.
    $mform->addElement('html', output_helper::get_jquery());
    $mform->addElement('html', '<script>
	$(document).ready(function(){
		var txtGreen = $("<span>", {class:"after-input"}).text("' . get_string('similarity_percent', "plagiarism_compilatio") . '");
		$("#green_threshold").after(txtGreen);
		var txtOrange = $("<span>", {class:"after-input"}).text("' .
        get_string('similarity_percent', "plagiarism_compilatio") .
        ', ' .
        get_string("red_threshold", "plagiarism_compilatio") .
        '.");
		$("#orange_threshold").after(txtOrange);
	});
	</script>');

    // Numeric validation for Thresholds.
    $mform->addRule('green_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');
    $mform->addRule('orange_threshold', get_string("numeric_threshold", "plagiarism_compilatio"), 'numeric', null, 'client');

    $mform->setType('green_threshold', PARAM_INT);
    $mform->setType('orange_threshold', PARAM_INT);

    $mform->setDefault('green_threshold', '10');
    $mform->setDefault('orange_threshold', '25');
}

/**
 * compilatio_remove_duplicates
 *
 * Deindex and remove document(s) in Compilatio
 * Remove entry(ies) in plagiarism_compilatio_files table
 *
 * @param array    $duplicates
 * @param array    $plagiarismsettings
 * @return boolean true if all documents have been processed, false otherwise
 */
function compilatio_remove_duplicates($duplicates, $plagiarismsettings) {

    if (is_array($duplicates)) {

        global $CFG, $DB;

        $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

        $i = 0;
        foreach ($duplicates as $doc) {

            // Deindex document.
            if ($compilatio->set_indexing_state($doc->externalid, 0)) {
                // Delete document.
                $compilatio->del_doc($doc->externalid);
                // Delete DB record.
                $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                $i++;
            } else {
                mtrace('Error deindexing document ' . $doc->externalid);
            }
        }

        if ($i == count($duplicates)) {
            return true;
        }
    }
    return false;
}

/**
 * Get (create if necessary) a plagiarism_compilatio_files record
 *
 * @param  int                   $cmid   course module id
 * @param  int                   $userid user id
 * @param  stdClass|stored_file  $file   content metadata object
 * @return stdClass|false        plagiarism_compilatio_files record or false
 */
function compilatio_get_plagiarism_file($cmid, $userid, $file) {

    global $DB;

    // Check if plugin is activated in this course module.
    if (compilatio_enabled($cmid) == false) {
        return false;
    }

    if (is_a($file, 'stdClass')) {
        // Object from a text content.
        $filename = $file->filename;
        $identifier = $file->identifier;
    } else if (is_a($file, 'stored_file')) {
        // Object from a file.
        $filename = $file->get_filename();
        $identifier = $file->get_contenthash();
    }

    // Get an existing plagiarismfile record ?
    $sql = "SELECT * FROM {plagiarism_compilatio_files}
        WHERE timesubmitted = (SELECT max(timesubmitted) FROM {plagiarism_compilatio_files}
        WHERE cm = ? AND userid = ? AND identifier = ?)
        AND cm = ? AND userid = ? AND identifier = ?";
    $plagiarismfile = $DB->get_record_sql($sql, array($cmid, $userid, $identifier, $cmid, $userid, $identifier));

    // Prepare a new entry if empty.
    if (empty($plagiarismfile)) {

        $plagiarismfile = new stdClass();
        $plagiarismfile->cm = $cmid;
        $plagiarismfile->userid = $userid;
        $plagiarismfile->identifier = $identifier;
        $plagiarismfile->filename = $filename;
        $plagiarismfile->statuscode = 'pending';
        $plagiarismfile->attempt = 0;
        $plagiarismfile->timesubmitted = time();

        // Add new entry and get plagiarism_compilatio_file table record `id` for update_record_raw().
        if (($compid = $DB->insert_record('plagiarism_compilatio_files', $plagiarismfile, true)) === false) {
            debugging("insert into compilatio_files failed");
        }
        $plagiarismfile->id = $compid;
    }
    return $plagiarismfile;
}

/**
 * Queue a file
 *
 * @param  int      $cmid               Course module ID
 * @param  int      $userid             User ID
 * @param  object   $file               File
 * @param  array    $plagiarismsettings Settings
 * @param  bool     $sendfile           Optional send file to Compilatio
 * @return bool                         Return true if succeed, false otherwise
 */
function compilatio_queue_file($cmid,
    $userid,
    $file,
    $plagiarismsettings,
    $sendfile = false) {

    global $DB;

    $plagiarismfile = compilatio_get_plagiarism_file($cmid, $userid, $file);

    // Check if $plagiarismfile actually needs to be submitted.
    if ($plagiarismfile->statuscode != 'pending') {
        return true;
    }

    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
    if ($plagiarismfile->filename !== $filename) {
        // This is a file that was previously submitted and not sent to compilatio but the filename has changed so fix it.
        $plagiarismfile->filename = $filename;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    }

    // Check to see if this is a valid file.
    $mimetype = compilatio_check_file_type($filename);
    if (empty($mimetype)) {
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        return true;
    }

    // Check if the size is too large.
    if (!compilatio_check_allowed_file_max_size($file)) {
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_TOO_LARGE;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        return true;
    }

    // Optionally send the file to Compilatio.
    if ($sendfile !== false) {

        // Check if we need to delay this submission.
        $attemptallowed = compilatio_check_attempt_timeout($plagiarismfile);
        if (!$attemptallowed) {
            return false;
        }

        // Increment attempt number.
        $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        $compid = compilatio_send_file_to_compilatio($plagiarismfile, $plagiarismsettings, $file);
        compilatio_start_if_direct_analysis($plagiarismfile, $cmid, $plagiarismsettings);
        return false;
    }

    return true;
}

/**
 * Function to check timesubmitted and attempt to see if we need to delay an API check
 * Also checks max attempts to see if it has exceeded.
 *
 * @param  array $plagiarismfile    A row of plagiarism_compilatio_files in database
 * @return bool                     Return true if succeed, false otherwise
 */
function compilatio_check_attempt_timeout($plagiarismfile) {

    global $DB;

    // The first time a file is submitted we don't need to wait at all.
    if (empty($plagiarismfile->attempt) && $plagiarismfile->statuscode == 'pending') {
        return true;
    }

    // Set some initial defaults.
    $now = time();
    $submissiondelay = 15;
    $maxsubmissiondelay = 60;
    $maxattempts = 4;

    if ($plagiarismfile->statuscode == 'pending') {

        // Initial wait time - doubled each time a check is made until the max delay is met.
        $submissiondelay = COMPILATIO_SUBMISSION_DELAY;
        // Maximum time to wait between submissions.
        $maxsubmissiondelay = COMPILATIO_MAX_SUBMISSION_DELAY;
        // Maximum number of times to try and send a submission.
        $maxattempts = COMPILATIO_MAX_SUBMISSION_ATTEMPTS;

    } else if ($plagiarismfile->statuscode == COMPILATIO_STATUSCODE_ANALYSING ||
        $plagiarismfile->statuscode == COMPILATIO_STATUSCODE_IN_QUEUE) {

        // Initial wait time - this is doubled each time a check is made until the max delay is met.
        $submissiondelay = COMPILATIO_STATUS_DELAY;
        // Maximum time to wait between checks.
        $maxsubmissiondelay = COMPILATIO_MAX_STATUS_DELAY;
        // Maximum number of times to try and send a submission.
        $maxattempts = COMPILATIO_MAX_STATUS_ATTEMPTS;
    }

    // Check if we have exceeded the max attempts.
    if ($plagiarismfile->attempt > $maxattempts) {
        $plagiarismfile->statuscode = 'timeout';
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        return true; // Return true to cancel the event.
    }

    // Now calculate wait time.
    $wait = $submissiondelay;
    $i = 0;
    while ($i < $plagiarismfile->attempt) {
        if ($wait > $maxsubmissiondelay) {
            $wait = $maxsubmissiondelay;
        }
        $wait = $wait * $plagiarismfile->attempt;
        $i++;
    }
    $wait = (int) $wait * 60;
    $timetocheck = (int) ($plagiarismfile->timesubmitted + $wait);
    // Calculate when this should be checked next.
    if ($timetocheck < $now) {
        return true;
    } else {
        return false;
    }
}

/**
 * Send a file to Compilatio
 *
 * @param  object $plagiarismfile    File
 * @param  array  $plagiarismsettings Settings
 * @param  object $file               File
 * @return mixed                      Return the document ID if succeed, false otherwise
 */
function compilatio_send_file_to_compilatio(&$plagiarismfile, $plagiarismsettings, $file) {

    global $DB, $CFG;

    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();

    $mimetype = compilatio_check_file_type($filename);
    if (empty($mimetype)) { // Sanity check on filetype - this should already have been checked.
        debugging("no mime type for this file found.");
        return false;
    }

    // Display this log only on CRON exec.
    if (!defined("COMPILATIO_MANUAL_SEND")) {
        mtrace("sending file #" . $plagiarismfile->id);
    }

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);
    // Get name from module.
    $modulesql = "
        SELECT m.id, m.name, cm.instance FROM {course_modules} cm
        INNER JOIN {modules} m on cm.module = m.id
        WHERE cm.id = ?";

    $moduledetail = $DB->get_record_sql($modulesql, array($plagiarismfile->cm));
    if (!empty($moduledetail)) {
        $sql = "SELECT * FROM " . $CFG->prefix . $moduledetail->name . " WHERE id= ?";
        $module = $DB->get_record_sql($sql, array($moduledetail->instance));
    }
    if (empty($module)) {
        debugging("could not find this module - it may have been deleted?");
        return false;
    }
    $name = format_string($module->name) . "_" . $plagiarismfile->cm;
    $filecontents = (!empty($file->filepath)) ? file_get_contents($file->filepath) : $file->get_content();
    $idcompi = $compilatio->send_doc($name, // Title.
        //$name, // Description.
        $filename, // File_name.
        //$mimetype, // Mime data.
        $filecontents); // Doc content.

    if (compilatio_valid_md5($idcompi)) {
        $plagiarismfile->externalid = $idcompi;
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        return $idcompi;
    }

    $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_UNEXTRACTABLE;
    $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    debugging("invalid compilatio response received - will try again later." . $idcompi);
    // Invalid response returned - increment attempt value and return false to allow this to be called again.
    return false;
}

/**
 * Used to obtain similarity scores from Compilatio for submitted files.
 *
 * @param object $plagiarismsettings - from a call to plagiarism_get_settings.
 * @return void
 */
function compilatio_get_scores($plagiarismsettings) {

    global $DB;

    mtrace("getting Compilatio similarity scores");

    // Get all files set that have been submitted.
    $sql = "statuscode = ? OR statuscode = ? OR statuscode = ?";
    $params = array(COMPILATIO_STATUSCODE_ANALYSING, COMPILATIO_STATUSCODE_IN_QUEUE, "pending");
    $files = $DB->get_records_select('plagiarism_compilatio_files', $sql, $params);

    if (!empty($files)) {
        foreach ($files as $plagiarismfile) {
            // Check if we need to delay this submission.
            $attemptallowed = compilatio_check_attempt_timeout($plagiarismfile);
            if (!$attemptallowed) {
                continue;
            }
            mtrace("getting score for file " . $plagiarismfile->id);
            compilatio_check_analysis($plagiarismfile); // Get status and set reporturl/status if required.
        }
    }
    // Now check for files that need to be set to analyse.
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
 * Fonction to return current compilatio quota
 *
 * @return $quotas
 */
function compilatio_getquotas() {

    global $CFG;

    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

    return $compilatio->get_quotas();
}

/**
 * Start an analyse
 *
 * @param  object $plagiarismfile     File
 * @param  array  $plagiarismsettings Settings
 * @return mixed                      Return true if succeed, the analyse object
 */
function compilatio_startanalyse($plagiarismfile, $plagiarismsettings = '') {

    global $CFG, $DB, $OUTPUT;

    if (empty($plagiarismsettings)) {
        $plagiarismsettings = (array) get_config('plagiarism');
    }

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

    $analyse = $compilatio->start_analyse($plagiarismfile->externalid);

    if ($analyse === true) {
        // Update plagiarism record.
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    } else {
    echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio').$analyse);
        return $analyse;
    }

    return true;
}

/**
 * Function to check for valid response from Compilatio
 *
 * @param  string $hash Hash
 * @return bool         Return true if succeed, false otherwise
 */
function compilatio_valid_md5($hash) {

    // La nouvelle API REST renvoie des ID longs de 40 caractères, l'ancienne devait en renvoyer des longs de 32, d'où les deux cas de figure
    if (preg_match('/^[a-f0-9]{32}$/', $hash) || preg_match('/^[a-f0-9]{40}$/', $hash)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check an analysis
 *
 * @param  object $plagiarismfile    File
 * @param  bool   $manuallytriggered Manually triggered
 * @return void
 */
function compilatio_check_analysis($plagiarismfile, $manuallytriggered = false) {

    global $CFG, $DB;

    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

    $docstatus = $compilatio->get_doc($plagiarismfile->externalid);

    if (isset($docstatus->documentStatus->status)) {
        if ($docstatus->documentStatus->status == "ANALYSE_COMPLETE") {
            if (isset($docstatus->documentProperties) && $docstatus->documentProperties->wordCount < 10) {
                // Set the code to UNEXTRACTABLE if the documents contains less than 10 words:.
                $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_UNEXTRACTABLE;
            } else {
                $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_COMPLETE;
            }
            $plagiarismfile->similarityscore = round($docstatus->documentStatus->indice);
            // Now get report url.
            $plagiarismfile->reporturl = $compilatio->get_report_url($plagiarismfile->externalid);
            $emailstudents = $DB->get_field('plagiarism_compilatio_config',
                'value',
                array('cm' => $plagiarismfile->cm, 'name' => 'compilatio_studentemail'));
            if (!empty($emailstudents)) {
                $compilatio = new plagiarism_plugin_compilatio();
                $compilatio->compilatio_send_student_email($plagiarismfile);
            }
        } else if ($docstatus->documentStatus->status == "ANALYSE_IN_QUEUE") { // Added for queue support.
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
        } else if ($docstatus->documentStatus->status == "ANALYSE_PROCESSING") {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_ANALYSING;
        } else if ($docstatus->documentStatus->status == "ANALYSE_NOT_STARTED") {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        }
    }
    if (!$manuallytriggered) {
        $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
    }
    $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
}

/**
 * Start all files analysis
 *
 * @param  array $plagiarismfiles Files
 * @return void
 */
function compilatio_analyse_files($plagiarismfiles) {

    $plagiarismsettings = (array) get_config('plagiarism');
    foreach ($plagiarismfiles as $plagiarismfile) {
        compilatio_startanalyse($plagiarismfile, $plagiarismsettings);
    }
}

/**
 * Get the Compilatio subscription expiration date (Year and month)
 *
 * @return string, formatted as "YYYY-MM"
 */
function compilatio_get_account_expiration_date() {

    global $CFG;
    $plagiarismsettings = (array) get_config('plagiarism');
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);
    return $compilatio->get_account_expiration_date();
}

/**
 * Send informations about this configuration to Compilatio
 *
 * @return bool : False if any error occurs, true otherwise
 */
function compilatio_send_statistics() {

    // Get data about installation.
    global $CFG;
    global $DB;

    $language = $CFG->lang;
    $releasephp = phpversion();
    $releasemoodle = $CFG->release;
    $releaseplugin = get_config('plagiarism_compilatio', 'version');
    $cronfrequencyobject = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));
    if ($cronfrequencyobject != null) {
        $cronfrequency = (int) $cronfrequencyobject->value;
    } else {
        $cronfrequency = 0;
    }

    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);
    return $compilatio->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency);
}

/**
 * Get all Compilatio news from Compilatio Webservice
 *
 * @return array of news objects
 */
function compilatio_get_technical_news() {

    global $CFG;
    $plagiarismsettings = (array) get_config('plagiarism');

    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

    return $compilatio->get_technical_news();
}

/**
 * Get statistics for the assignment $cmid
 *
 * @param  string $cmid Course module ID
 * @return string       HTML containing the statistics
 */
function compilatio_get_statistics($cmid) {

    global $DB, $PAGE;

    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
    // Create the thresholds if they don't exist : Case of upgrade from an older plugin version.
    if (!isset($plagiarismvalues["green_threshold"])) {
        $green = new StdClass();
        $green->name = "green_threshold";
        $green->value = "10";
        $green->cm = $cmid;
        $DB->insert_record('plagiarism_compilatio_config', $green, false);
        $greenthreshold = 10;
    } else {
        $greenthreshold = $plagiarismvalues["green_threshold"];
    }
    if (!isset($plagiarismvalues["orange_threshold"])) {
        $orange = new StdClass();
        $orange->name = "orange_threshold";
        $orange->value = "25";
        $orange->cm = $cmid;
        $DB->insert_record('plagiarism_compilatio_config', $orange, false);
        $redthreshold = 25;
    } else {
        $redthreshold = $plagiarismvalues["orange_threshold"];
    }

    $sql = "
        SELECT COUNT(DISTINCT pcf.id)
        FROM {course_modules} cm
        JOIN {assign_submission} ass ON ass.assignment = cm.instance
        JOIN {files} files ON files.itemid = ass.id
        JOIN {plagiarism_compilatio_files} pcf
            ON pcf.identifier = files.contenthash
        WHERE cm.id=? AND pcf.cm=? AND files.filearea='submission_files'";

    $countallsql = $sql;
    $documentscount = $DB->count_records_sql($countallsql, array($cmid, $cmid));

    $countanalyzedsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "'";
    $countanalyzed = $DB->count_records_sql($countanalyzedsql, array($cmid, $cmid));

    $counthigherthanredsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore>$redthreshold";
    $counthigherthanred = $DB->count_records_sql($counthigherthanredsql, array($cmid, $cmid));

    $countlowerthangreensql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore<=$greenthreshold";
    $countlowerthangreen = $DB->count_records_sql($countlowerthangreensql, array($cmid, $cmid));

    $countunsupportedsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNSUPPORTED . "'";
    $countunsupported = $DB->count_records_sql($countunsupportedsql, array($cmid, $cmid));

    $countunextractablesql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNEXTRACTABLE . "'";
    $countunextractable = $DB->count_records_sql($countunextractablesql, array($cmid, $cmid));

    $countinqueuesql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_IN_QUEUE . "'";
    $countinqueue = $DB->count_records_sql($countinqueuesql, array($cmid, $cmid));

    $countanalysingsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_ANALYSING . "'";
    $countanalysing = $DB->count_records_sql($countanalysingsql, array($cmid, $cmid));

    $averagesql = "
    	SELECT AVG(similarityscore) avg
    	FROM {plagiarism_compilatio_files} pcf
    	WHERE id IN (
        	SELECT DISTINCT pcf.id
        	FROM {course_modules} cm
        	JOIN {assign_submission} ass ON ass.assignment = cm.instance
        	JOIN {files} files ON files.itemid = ass.id
        	JOIN {plagiarism_compilatio_files} pcf ON pcf.identifier = files.contenthash
        	JOIN {user} usr ON pcf.userid= usr.id
        	WHERE cm.id=?
                AND pcf.cm=?
                AND files.filearea='submission_files'
                AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "')";

    $avgresult = $DB->get_record_sql($averagesql, array($cmid, $cmid));
    $avg = $avgresult->avg;

    $analysisstats = new StdClass();
    $analysisstats->countAnalyzed = $countanalyzed;
    $analysisstats->documentsCount = $documentscount;

    $analysisstatsthresholds = new StdClass();
    // Total.
    $analysisstatsthresholds->documentsCount = $countanalyzed;
    // Thresholds.
    $analysisstatsthresholds->greenThreshold = $greenthreshold;
    $analysisstatsthresholds->redThreshold = $redthreshold;
    // Number of documents.
    $analysisstatsthresholds->documentsUnderGreenThreshold = $countlowerthangreen;
    $analysisstatsthresholds->documentsAboveRedThreshold = $counthigherthanred;
    $analysisstatsthresholds->documentsBetweenThresholds = $countanalyzed - $counthigherthanred - $countlowerthangreen;

    /**
     * Display an array as a list, using moodle translations and parameters
     * Index 0 for translation index and index 1 for parameter
     *
     * @param  array $listitems List items
     * @return string           Return the stat string
     */
    function compilatio_display_list_stats($listitems) {

        $string = "<ul>";
        foreach ($listitems as $listitem) {
            $string .= "<li>" . get_string($listitem[0], "plagiarism_compilatio", $listitem[1]) . "</li>";
        }
        $string .= "</ul>";
        return $string;
    }

    if ($documentscount === 0) {
        $result = "<span>" . get_string("no_documents_available", "plagiarism_compilatio") . "</span>";
    } else {
        $items = array();
        $items[] = array("documents_analyzed", $analysisstats);
        if ($countanalysing !== 0) {
            $items[] = array("documents_analyzing", $countanalysing);
        }
        if ($countinqueue !== 0) {
            $items[] = array("documents_in_queue", $countinqueue);
        }

        $result = "<span>" . get_string("progress", "plagiarism_compilatio") . "</span>";
        $result .= compilatio_display_list_stats($items);
    }

    $items = array();

    if ($countanalyzed != 0) {
        $items[] = array("average_similarities", round($avg));
        $items[] = array("documents_analyzed_lower_green", $analysisstatsthresholds);
        $items[] = array("documents_analyzed_between_thresholds", $analysisstatsthresholds);
        $items[] = array("documents_analyzed_higher_red", $analysisstatsthresholds);
    }

    $errors = array();
    if ($countunsupported !== 0) {
        $errors[] = array("not_analyzed_unsupported", $countunsupported);
    }
    if ($countunextractable !== 0) {
        $errors[] = array("not_analyzed_unextractable", $countunextractable);
    }

    if (count($items) !== 0) {
        $result .= "<span>" . get_string("results", "plagiarism_compilatio") . "</span>";
        $result .= compilatio_display_list_stats($items);

        if (count($errors) !== 0) {
            $result .= "<span>" . get_string("errors", "plagiarism_compilatio") . "</span>";
            $result .= compilatio_display_list_stats($errors);
        }

        $url = $PAGE->url;
        $url->param('compilatio_export', true);
        $result .= "<a title='" .
        get_string("export_csv", "plagiarism_compilatio") .
            "' class='compilatio-icon' href='$url'><i class='fa fa-download fa-2x'></i></a>";
    }
    return $result;
}

/**
 * Lists unsupported documents in the assignment
 *
 * @param  string $cmid Course module ID
 * @return array        containing the student & the file
 */
function compilatio_get_unsupported_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_UNSUPPORTED);
}

/**
 * Lists unextractable documents in the assignment
 *
 * @param  string $cmid Course module ID
 * @return array        containing the student & the file
 */
function compilatio_get_unextractable_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_UNEXTRACTABLE);
}

/**
 * Lists files of an assignment according to the status code
 *
 * @param  string $cmid       Course module ID
 * @param  int    $statuscode Status Code
 * @return array              containing the student & the file
 */
function compilatio_get_files_by_status_code($cmid, $statuscode) {

    global $DB;

    $sql = "
        SELECT DISTINCT
            pcf.id,
            pcf.filename,
            usr.firstname,
            usr.lastname
        FROM {course_modules} cm
        JOIN {assign_submission} ass ON ass.assignment = cm.instance
        JOIN {files} files ON files.itemid = ass.id
        JOIN {plagiarism_compilatio_files} pcf ON pcf.identifier = files.contenthash
        JOIN {user} usr ON pcf.userid= usr.id
        WHERE cm.id=?
            AND pcf.cm=?
            AND files.filearea='submission_files'
            AND statuscode = '" . $statuscode . "'";

    $files = $DB->get_records_sql($sql, array($cmid, $cmid));

    return array_map(
        function ($file) {
            return $file->lastname . " " . $file->firstname . " : " . $file->filename;
        }, $files);
}

/**
 * List files that have reach max attempts
 *
 * @param  int $cmid    Course module ID
 * @return array        Array contains files
 */
function compilatio_get_max_attempts_files($cmid) {

    global $DB;

    $sql = "
        SELECT DISTINCT pcf.id,pcf.filename, usr.firstname, usr.lastname
        FROM {course_modules} cm
        JOIN {assign_submission} ass ON ass.assignment = cm.instance
        JOIN {files} files ON files.itemid = ass.id
        JOIN {plagiarism_compilatio_files} pcf
            ON pcf.identifier = files.contenthash
        JOIN {user} usr ON pcf.userid= usr.id
        WHERE cm.id=?
            AND pcf.cm=?
            AND files.filearea = 'submission_files'
            AND statuscode = ?
            AND pcf.attempt >= 6
    ";

    $params = array(
        $cmid,
        $cmid,
        COMPILATIO_STATUSCODE_UNEXTRACTABLE,
    );
    $files = $DB->get_records_sql($sql, $params);

    return array_map(
        function ($file) {
            return $file->lastname . " " . $file->firstname . " : " . $file->filename;
        }, $files);

}

/**
 * Get the expiration date from the webservice
 * Insert or update a field in the DB containing that date
 */
function compilatio_update_account_expiration_date() {

    global $DB;
    $expirationdate = compilatio_get_account_expiration_date();
    if ($expirationdate === false) {
        return;
    }
    // Insert / update in db.
    $date = $DB->get_record('plagiarism_compilatio_data', array('name' => 'account_expire_on'));

    if ($date == null) {
        $item = new stdClass();
        $item->name = "account_expire_on";
        $item->value = $expirationdate;
        $DB->insert_record('plagiarism_compilatio_data', $item);
    } else if ($date->value !== $expirationdate) {
        $date->value = $expirationdate;
        $DB->update_record('plagiarism_compilatio_data', $date);
    }
}

/**
 * Check expiration date of the account in the DB
 *
 * @return boolean : false if it's not expiring and true if it's expiring at the end of the month.
 */
function compilatio_check_account_expiration_date() {

    global $DB;

    $expirationdate = $DB->get_record('plagiarism_compilatio_data', array('name' => 'account_expire_on'));

    if ($expirationdate != null && date("Y-m") == $expirationdate->value) {
        return true;
    }
    return false;
}

/**
 * Update the news of Compilatio
 * Remove old entries and insert new ones.
 */
function compilatio_update_news() {

    global $DB;

    $news = compilatio_get_technical_news();
    if ($news === false) {
        return;
    }

    $DB->delete_records_select('plagiarism_compilatio_news', '1=1');
    foreach ($news as $new) {

        $new->id_compilatio = $new->id;
        $new->message_en = compilatio_decode($new->message_en);
        $new->message_fr = compilatio_decode($new->message_fr);
        unset($new->id);
        $DB->insert_record("plagiarism_compilatio_news", $new);
    }
}

/**
 * Used to solve encoding problems from Compilatio API
 * Some string are UTF8 encoded twice
 *
 * @param  string $message UTF-8 String, encoded once or twice
 * @return string          encoded correctly
 */
function compilatio_decode($message) {

    $decodeonce = utf8_decode($message);
    $decodetwice = utf8_decode($decodeonce);

    if (preg_match('!!u', $decodetwice)) {
        return $message;
    } else {
        return $decodeonce;
    }
}

/**
 * Display the news of Compilatio
 *
 * @return array containing alerts according to the news in the DB.
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
            case PLAGIARISM_COMPILATIO_NEWS_UPDATE:
                $title = get_string("news_update", "plagiarism_compilatio"); // Info.
                $class = "info";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_INCIDENT:
                $title = get_string("news_incident", "plagiarism_compilatio"); // Danger.
                $class = "danger";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_MAINTENANCE:
                $title = get_string("news_maintenance", "plagiarism_compilatio"); // Warning.
                $class = "warning";
                break;
            case PLAGIARISM_COMPILATIO_NEWS_ANALYSIS_PERTURBATED:
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
}

/**
 * Format the date from "2015-05" to may 2015 or mai 2015, according to the moodle language
 *
 * @param  string $date formatted like "YYYY-MM"
 * @return string       Litteral month in local language and year
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

/**
 * Get the submissions unknown from Compilatio table plagiarism_compilatio_files
 *
 * @param string $cmid cmid of the assignment
 */
function compilatio_get_non_uploaded_documents($cmid) {

    global $DB;

    return $DB->get_records_sql("
        SELECT files.*
        FROM {course_modules} cm
        JOIN {assignsubmission_file} assf ON assf.assignment = cm.instance
        JOIN {files} files ON files.itemid = assf.submission
            AND component='assf'
            AND filearea='submission_files'
            AND filename<>'.'
        WHERE cm.id=? AND contenthash NOT IN (
            SELECT DISTINCT identifier
            FROM {plagiarism_compilatio_files} pcf
            WHERE cm=cm.id AND files.userid=pcf.userid)",
        array($cmid));
}

/**
 * Uploads files to compilatio
 *
 * @param array  $files Array of file records
 * @param string $cmid  cmid of the assignment
 */
function compilatio_upload_files($files, $cmid) {

    global $DB;

    $fs = get_file_storage();

    $compilatio = new plagiarism_plugin_compilatio();
    $plagiarismsettings = $compilatio->get_settings();

    $analysistype = $DB->get_field('plagiarism_compilatio_config',
        'value',
        array('cm' => $cmid, "name" => "compilatio_analysistype"));
    $timeanalysis = $DB->get_field('plagiarism_compilatio_config',
        'value',
        array('cm' => $cmid, "name" => "compilatio_timeanalyse"));

    foreach ($files as $file) {
        $f = $fs->get_file_by_id($file->id);
        compilatio_queue_file($cmid, $file->userid, $f, $plagiarismsettings, true); // send the file to Compilatio.
        /* Start analysis if the settings are on "manual" or "timed" and the planned time is greater than the current time
        Starting "auto" analysis is handled in "compilatio_send_file" */
        if ($analysistype == COMPILATIO_ANALYSISTYPE_MANUAL ||
            ($analysistype == COMPILATIO_ANALYSISTYPE_PROG &&
                time() >= $timeanalysis)) {
            $plagiarismfile = compilatio_get_plagiarism_file($cmid, $file->userid, $f);
            compilatio_startanalyse($plagiarismfile, $plagiarismsettings);
        }
    }
}

/**
 * Get the cron frequency and store it in the database
 * Cron frequency is the difference between the current time and
 * the time of the last cron execution
 */
function compilatio_update_cron_frequency() {

    global $DB;
    // Create or Update last execution date of CRON task.
    // Get last cron exec.
    $lastcron = $DB->get_record('plagiarism_compilatio_data', array('name' => 'last_cron'));

    // Get & store cron frequency.
    if ($lastcron != null) {
        // Convert in minutes.
        $frequency = round((time() - $lastcron->value) / 60);

        $lastfrequency = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));

        if ($lastfrequency == null) { // Create if not exists.
            $item = new stdClass();
            $item->name = "cron_frequency";
            $item->value = $frequency;
            $DB->insert_record('plagiarism_compilatio_data', $item);
        } else {
            $lastfrequency->value = $frequency;
            $DB->update_record('plagiarism_compilatio_data', $lastfrequency);
        }
    }
    return $lastcron;
}

/**
 * Updates the last CRON date in the database
 *
 * @param object $lastcron stdClass from a get_record call.
 * @return void
 */
function compilatio_update_last_cron_date($lastcron) {

    global $DB;
    // Insert or update the last cron date.
    if ($lastcron == null) { // Create if not exists.
        $item = new stdClass();
        $item->name = "last_cron";
        $item->value = strtotime("now");
        $DB->insert_record('plagiarism_compilatio_data', $item);
    } else {
        $lastcron->value = strtotime("now");
        $DB->update_record('plagiarism_compilatio_data', $lastcron);
    }
}

/**
 * Updates a marker in the database, according to Compilatio's webservice status
 *
 * @return void
 */
function compilatio_update_connection_status() {

    global $DB;

    // Test connection to the Compilatio web service.
    $connectionstatus = ws_helper::test_connection();

    // Insert connection status into DB.
    $oldconnectionstatus = $DB->get_record('plagiarism_compilatio_data',
        array('name' => 'connection_webservice'));

    if ($oldconnectionstatus == null) {
        // Create if not exists.
        $item = new stdClass();
        $item->name = "connection_webservice";
        $item->value = (int) $connectionstatus;
        $DB->insert_record('plagiarism_compilatio_data', $item);

    } else if ($oldconnectionstatus->value != $connectionstatus) {
        $oldconnectionstatus->value = (int) $connectionstatus;
        $DB->update_record('plagiarism_compilatio_data', $oldconnectionstatus);
    }
}

/**
 * Get global plagiarism statistics
 *
 * @param bool   $html display HTML if true, text otherwise
 * @return array       containing associative arrays for the statistics
 */
function compilatio_get_global_statistics($html = true) {

    global $DB;

    $sql = '
        SELECT cm,
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

    $rows = $DB->get_records_sql($sql);

    $results = array();
    foreach ($rows as $row) {

        $courseurl = new moodle_url('/course/view.php', array('id' => $row->id));
        $assignurl = new moodle_url('/mod/assign/view.php', array('id' => $row->cm, 'action' => "grading"));
        $userurl = new moodle_url('/user/view.php', array('id' => $row->userid));

        $result = array();
        if ($html) {
            $result["teacher"] = "<a href='$userurl'>$row->lastname $row->firstname</a>";
            $result["course"] = "<a href='$courseurl'>$row->course</a>";
            $result["assign"] = "<a href='$assignurl'>$row->assign</a>";

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

/**
 * Handle content
 *
 * @param  string $content  Content
 * @param  int    $userid   User ID
 * @param  int    $courseid Course ID
 * @param  int    $cmid     Course module ID
 * @param  int    $postid Post ID
 * @return mixed            Return null if the content is empty, void otherwise
 */
function handle_content($content, $userid, $courseid, $cmid, $postid = null) {

    if (trim($content) == "") {
        return;
    }

    $data = new stdClass();
    $data->courseid = $courseid;
    $data->content = $content;
    $data->userid = $userid;
    $data->postid = $postid;

    $plagiarismsettings = (array) get_config('plagiarism');

    $file = compilatio_create_temp_file($cmid, $data);
    compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings);
}

/**
 * Handle hashes
 *
 * @param  array $hashes Hashes
 * @param  int   $cmid   Course module ID
 * @param  int   $userid User ID
 * @param  int   $postid Post ID
 * @return void
 */
function handle_hashes($hashes, $cmid, $userid, $postid = null) {

    $plagiarismsettings = (array) get_config('plagiarism');

    foreach ($hashes as $hash) {

        $fs = get_file_storage();
        $efile = $fs->get_file_by_hash($hash);

        if ($postid != null) {
            if (!preg_match("/^post-" . $postid . "-/", $efile->get_filename())) {
                $efile->rename($efile->get_filepath(), 'post-' . $postid . '-' . $efile->get_filename());
            }
        }

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

/**
 * Check if Compilatio is enabled
 *
 * @param  int      $cmid Course module ID
 * @return boolean  Return true if enabled, false otherwise
 */
function compilatio_enabled($cmid) {

    global $DB;

    $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config',
        array('cm' => $cmid),
        '',
        'name, value');
    if (empty($plagiarismvalues['use_compilatio'])) {
        return false;
    }

    // Check if the module associated with this event still exists.
    if (!$DB->record_exists('course_modules', array('id' => $cmid))) {
        return false;
    }
    return true;
}

/**
 * Event handler
 * @param  array $eventdata  Event data
 * @param  bool  $hasfile    There is a file ?
 * @param  bool  $hascontent There is a content ?
 * @return mixed             Return null if plugin is not enabled, void otherwise
 */
function event_handler($eventdata, $hasfile = true, $hascontent = true) {

    $cmid = $eventdata["contextinstanceid"];
    if (!compilatio_enabled($cmid)) {
        return;
    }
/*
    var_dump($eventdata);
    exit;
*/

    $fh = fopen("/home/sites/moodle36/moodledata/temp/templog.txt", 'a');
    fwrite($fh, var_export($eventdata, true));
    fclose($fh);

    global $CFG, $DB;
    $duplicates = array();

    // Get user id.
    $userid = $eventdata['relateduserid'];
    if ($userid == null) {
        $userid = $eventdata['userid'];
    }
    // Get forum post id if exists.
    $postid = null;
    if (isset($eventdata['objecttable']) && $eventdata['objecttable'] == 'forum_posts') {
        $postid = $eventdata['objectid'];
    }

    // Get course module.
    $cm = get_coursemodule_from_id(null, $cmid);

    // Deletion events.
    if ($eventdata['crud'] == 'd') {

        // In forums.
        if ($eventdata['objecttable'] == 'forum_posts') {

            $filename = 'post-' . $eventdata['courseid'] . '-' . $cmid . '-' . $eventdata['objectid'] . '.htm';
            $posts = $DB->get_records('plagiarism_compilatio_files', array('filename' => $filename));

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename like ?";
            $attachments = $DB->get_records_sql($sql, array($cmid, 'post-' . $eventdata['objectid'] . '-%'));

            $duplicates = array_merge($posts, $attachments);
        }
        // In workshops.
        if ($eventdata['objecttable'] == 'workshop_submissions') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
        }
        compilatio_remove_duplicates($duplicates, (array) get_config('plagiarism'));
    }

    // Adding/updating a file.
    if ($hasfile) {
        $hashes = $eventdata["other"]["pathnamehashes"];

        if ($eventdata['objecttable'] == 'assign_submission') {
            // Get max submission files in this module.
            $table = 'assign_plugin_config';
            $return = 'value';
            $select = "assignment = '" . $cm->instance . "'
                AND subtype = 'assignsubmission' AND name = 'maxfilesubmissions'";
            $maxfilesubmissions = $DB->get_field_select($table, $return, $select);
            if ($maxfilesubmissions == '1') { // Look for duplicates in "1 file" submissions assigns.
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
            }
        }

        if ($eventdata['objecttable'] == 'forum_posts') {
            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename LIKE ?";
            $duplicates = $DB->get_records_sql($sql, array($cmid, 'post-' . $eventdata['objectid'] . '-%'));
        }

        if ($eventdata['objecttable'] == 'workshop_submissions') {
            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'content-%'";
            $duplicates = $DB->get_records_sql($sql, array($cmid, $userid));
        }
        compilatio_remove_duplicates($duplicates, (array) get_config('plagiarism'));
        handle_hashes($hashes, $cmid, $userid, $postid);
    }

    // Adding/updating a text content.
    if ($hascontent) {
        $content = $eventdata["other"]["content"];
        $courseid = $eventdata["courseid"];

        if ($eventdata['objecttable'] == 'forum_posts') {
            $filename = 'post-' . $eventdata['courseid'] . '-' . $cmid . '-' . $eventdata['objectid'] . '.htm';
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('filename' => $filename));
        }

        if ($eventdata['objecttable'] == 'workshop_submissions') {
            $filename = "content-" . $eventdata['courseid'] . "-" . $cmid . "-" . $userid . ".htm";
            $duplicates = $DB->get_records('plagiarism_compilatio_files',
                array('cm' => $cmid, 'userid' => $userid, 'filename' => $filename));
        }
        compilatio_remove_duplicates($duplicates, (array) get_config('plagiarism'));
        handle_content($content, $userid, $courseid, $cmid, $postid);
    }
}


/**
 * Check allowed max file size
 *
 * @param  object $file File object
 * @return bool         Return true if the size is supported, false otherwise.
 */
function compilatio_check_allowed_file_max_size($file) {

    $allowedsize = ws_helper::get_allowed_file_max_size()["octets"];

    if (isset($file->filepath)) { // Content (workshops).
        $size = filesize($file->filepath);
    } else { // Temp file.
        $size = (int) $file->get_filesize();
    }

    return $size <= $allowedsize;

}

/**
 * Check for the allowed file types
 *
 * @param  string  $filename Filename of the document
 * @return string  Return the MIME type if succeed, an empty string otherwise
 */
function compilatio_check_file_type($filename) {

    $pathinfo = pathinfo($filename);

    if (empty($pathinfo['extension'])) {
        return '';
    }
    $ext = strtolower($pathinfo['extension']);

    $allowedfiletypes = ws_helper::get_allowed_file_types();

    foreach ($allowedfiletypes as $allowedfiletype) {
        if ($allowedfiletype["type"] == $ext) {
            return $allowedfiletype["mimetype"];
        }
    }
    return '';
}

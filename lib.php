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
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');

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
class plagiarism_plugin_compilatio extends plagiarism_plugin {

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

        $plagiarismsettings = (array) get_config('plagiarism_compilatio');
        // Check if compilatio enabled.
        if (isset($plagiarismsettings['enabled']) && $plagiarismsettings['enabled']) {
            // Now check to make sure required settings are set!.
            if (empty($plagiarismsettings['apiconfigid'])) {
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
            'compilatio_show_student_score',
            'compilatio_show_student_report',
            'compi_student_analyses',
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

        // Don't show Compilatio if not allowed.
        $modulecontext = context_module::instance($linkarray['cmid']);
        $teacher = has_capability('plagiarism/compilatio:viewreport', $modulecontext);

        global $DB, $CFG, $PAGE;
        $output = '';

        $studentanalyse = compilatio_student_analysis($plugincm['compi_student_analyses'],
            $linkarray['cmid'], $linkarray['userid']);

        if ($studentanalyse) {
            if ($teacher) {
                $output .= "<div>" . get_string("student_analyze", "plagiarism_compilatio");
            } else {
                $output .= "<div>" . get_string("student_help", "plagiarism_compilatio");
            }
        }

        if ($plugincm['compilatio_show_student_score'] == '0' && !$studentanalyse && !$teacher) {
            return '';
        }

        // DOM Compilatio index for ajax callback.
        static $domid = 0;
        $domid++;

        $cm = get_coursemodule_from_id(null, $linkarray['cmid']);
        $indexingstate = null;

        // Get submiter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In assigns.
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

        // Warning info.
        if (!empty($results['warning'])) {
            $docwarning = $results['warning'];
        } else {
            $docwarning = null;
        }

        // Add de/indexing feature for teachers.
        if (!empty($results['externalid']) && $teacher && !$studentanalyse) {
            // Ajax API call.
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'getIndexingState',
                array($CFG->httpswwwroot, $domid, $results['externalid'], $results['apiconfigid']));
        }

        // No results in DB yet.
        if (empty($results)) {
            if ($teacher) {
                // Only works for assign.
                if (!isset($linkarray["file"]) || $cm->modname != 'assign'
                    || $linkarray['file']->get_filearea() == 'introattachment') {
                    return $output;
                }

                // Catch GET 'sendfile' param.
                $trigger = optional_param('sendfile', 0, PARAM_INT);
                $fileid = $linkarray["file"]->get_id();
                if ($trigger == $fileid) {
                    if (!defined("COMPILATIO_MANUAL_SEND")) {
                        define("COMPILATIO_MANUAL_SEND", true); // Hack to hide mtrace in function execution.
                        compilatio_upload_files(array($linkarray['file']), $linkarray['cmid']);
                        return $output . $this->get_links($linkarray);
                    }
                }
                $urlparams = array("id" => $linkarray['cmid'],
                                "sendfile" => $fileid,
                                "action" => "grading",
                                'page' => optional_param('page', null, PARAM_INT));
                $moodleurl = new moodle_url("/mod/assign/view.php", $urlparams);
                $url = array("url" => "$moodleurl", "target-blank" => false);
                $spancontent = get_string("analyze", "plagiarism_compilatio");
                $image = "play";
                $title = get_string('startanalysis', 'plagiarism_compilatio');
                $output .= output_helper::get_plagiarism_area($domid, $spancontent, $image, $title, "",
                    $url, false, $indexingstate, $docwarning);

                return $output;
            } else {
                return '';
            }
        }

        if ($results['statuscode'] == 'pending') {
            $spancontent = get_string("pending_status", "plagiarism_compilatio");
            $image = "hourglass";
            $title = get_string('pending', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $spancontent, $image, $title, "",
                array(), false, $indexingstate, $docwarning);

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
            $output .= output_helper::get_plagiarism_area($domid, "", "", $title, $append, $url,
                false, null, $docwarning);
            if (!empty($results['renamed'])) {
                $output .= $results['renamed'];
            }
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_IN_QUEUE) {
            $spancontent = get_string("queue", "plagiarism_compilatio");
            $image = "queue";
            $title = get_string('queued', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $spancontent, $image, $title, "",
                array(), false, $indexingstate, $docwarning);
        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ACCEPTED) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_compilatio_config',
                array('cm' => $linkarray['cmid']), '', 'name, value');
            $title = "";
            $span = "";
            $image = "";

            if ($studentanalyse) {
                if ($teacher) {
                    $span = get_string("analyze", "plagiarism_compilatio");
                    $title = get_string("student_start_analyze", "plagiarism_compilatio");
                } else {
                    $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysis',
                    array($CFG->httpswwwroot, $domid, $results['pid']));
                    $span = get_string("analyze", "plagiarism_compilatio");
                    $image = "play";
                    $title = get_string('startanalysis', 'plagiarism_compilatio');
                }

                $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                    "", false, $indexingstate, $docwarning);

            } else {
                // Check settings to see if we need to tell compilatio to process this file now.
                // Check if this is a timed release and add hourglass image.
                if ($plagiarismvalues['compilatio_analysistype'] == COMPILATIO_ANALYSISTYPE_PROG) {
                    $image = "prog";
                    $span = get_string('planned', 'plagiarism_compilatio');
                    $title = get_string('waitingforanalysis', 'plagiarism_compilatio',
                        userdate($plagiarismvalues['compilatio_timeanalyse']));
                } else if (has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext)) {
                    $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysis',
                        array($CFG->httpswwwroot, $domid, $results['pid']));
                    $span = get_string("analyze", "plagiarism_compilatio");
                    $image = "play";
                    $title = get_string('startanalysis', 'plagiarism_compilatio');
                } else if ($results['score'] !== '') { // If score === "" => Student, not allowed to see.
                    $image = "inprogress";
                    $title = get_string('processing_doc', 'plagiarism_compilatio');
                }

                if ($title !== "") {
                    $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                        "", false, $indexingstate, $docwarning);
                }
            }

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_ANALYSING) {
            $span = get_string("analyzing", "plagiarism_compilatio");
            $image = "inprogress";
            $title = get_string('processing_doc', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                array(), false, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNSUPPORTED) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unsupportedfiletype', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_LARGE) {
            $size = json_decode(get_config('plagiarism_compilatio', 'file_max_size'));
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('toolarge', 'plagiarism_compilatio', $size);
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_SHORT) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('tooshort', 'plagiarism_compilatio', get_config('plagiarism_compilatio', 'nb_mots_min'));
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_TOO_LONG) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('toolong', 'plagiarism_compilatio', get_config('plagiarism_compilatio', 'nb_mots_max'));
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_UNEXTRACTABLE) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('unextractablefile', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_NOT_FOUND) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('notfound', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else if ($results['statuscode'] == COMPILATIO_STATUSCODE_FAILED) {
            $span = get_string("error", "plagiarism_compilatio");
            $image = "exclamation";
            $title = get_string('failed', 'plagiarism_compilatio');
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                "", true, $indexingstate, $docwarning);

        } else {
            $title = get_string('unknownwarning', 'plagiarism_compilatio');
            $reset = '';
            $url = '';
            if (has_capability('plagiarism/compilatio:resetfile', $modulecontext)) {
                $urlparams = array('cmid' => $linkarray['cmid'],
                                'pf' => $results['pid'],
                                'sesskey' => sesskey(),
                                'page' => optional_param('page', null, PARAM_INT));
                $url = new moodle_url('/plagiarism/compilatio/reset.php', $urlparams);
                $span = "<a class='compilatio-reinit' href='$url'>" . get_string('reset', 'plagiarism_compilatio') . "</a>";
            }
            $span = get_string('reset', "plagiarism_compilatio");
            $url = array("target-blank" => false, "url" => $url);
            $image = "exclamation";
            $output .= output_helper::get_plagiarism_area($domid, $span, $image, $title, "",
                $url, true, $indexingstate, $docwarning);
        }

        if ($studentanalyse) {
            $output .= "</div>";
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

        // Check if plugin is enabled in moodle->module->cm.
        if (!compilatio_enabled($cmid)) {
            return false;
        }

        global $DB, $USER, $CFG;
        $plugincm = compilatio_cm_use($cmid);
        $filehash = $file->identifier;

        // Collect detail about the specified coursemodule.
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
            if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") === '1' &&
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

            if (compilatio_student_analysis($plugincm['compi_student_analyses'], $cmid, $userid)) {
                $viewreport = true;
                $viewscore = true;
            }
        } else {
            $selfreport = false;
        }
        // End of rights checking.

        // Anyone can see the Compilatio <div> only if they are allowed... We don't need to check anything else.
        if (!$viewscore) {
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
            'externalid' => $plagiarismfile->externalid,
            'warning' => $plagiarismfile->errorresponse,
            'apiconfigid' => $plagiarismfile->apiconfigid
        );

        if ($plagiarismfile->statuscode == 'pending') {
            $results['statuscode'] = 'pending';
            return $results;
        }

        // Now check for differing filename and display info related to it.
        $previouslysubmitted = '';
        if (strpos($plagiarismfile->filename, 'quiz') !== 0 && strpos($plagiarismfile->filename, 'post') !== 0
            && $file->filename !== $plagiarismfile->filename) {
            $previouslysubmitted = '<span class="compilatio-prevsubmitted">(';
            $previouslysubmitted .= get_string('previouslysubmitted', 'plagiarism_compilatio');
            $previouslysubmitted .= ': ' . $plagiarismfile->filename . ')</span>';
        }

        $results['statuscode'] = $plagiarismfile->statuscode;
        $results['pid'] = $plagiarismfile->id;
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
    $sql = "select value from {plagiarism_compilatio_config} where cm=? and name='use_compilatio'";
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

    if ($activecompilatio == null ||
        $activecompilatio->value != 1 ||
        !$compilatioenabled) {
        return;
    }

    $alerts = array();
    $output = '';

    $export = optional_param('compilatio_export', '', PARAM_BOOL);
    if ($export) {
        csv_helper::generate_cm_csv($cmid, $module);
    }

    // Store plagiarismfiles in $SESSION.
    $sql = "cm = ? AND externalid IS NOT null AND statuscode != 'Analyzed'";
    $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, array($cmid));
    $plagiarismfilesids = array_keys($SESSION->compilatio_plagiarismfiles);

    if (isset($SESSION->compilatio_alert)) {
        $alerts[] = $SESSION->compilatio_alert;
        unset($SESSION->compilatio_alert);
    }
    if (isset($SESSION->compilatio_alert_max_attempts)) {
        $alerts[] = $SESSION->compilatio_alert_max_attempts;
        unset($SESSION->compilatio_alert_max_attempts);
    }

    // Get compilatio analysis type.
    $sql = "cm = ? AND name='compilatio_analysistype'";
    $params = array($cmid);
    $record = $DB->get_record_select('plagiarism_compilatio_config', $sql, $params);
    $value = $record->value;

    if ($value == COMPILATIO_ANALYSISTYPE_MANUAL) { // Display a button that start all the analysis of the activity.

        $url = $PAGE->url;
        $url->param('compilatiostartanalysis', true);
        $startallanalysisbutton = "
            <button class='compilatio-button comp-button comp-start-btn' >
                <i class='fa fa-play-circle'></i>
                " . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
            </button>";

    } else if ($value == COMPILATIO_ANALYSISTYPE_PROG) { // Display the date of analysis if its type is set on 'Timed'.
        // Get analysis date :.
        $sql = "cm = ? AND name='compilatio_timeanalyse'";
        $params = array($cmid);
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
    $files = compilatio_get_unsupported_files($cmid);
    if (count($files) !== 0) {
        $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
        $alerts[] = array(
            "class" => "danger",
            "title" => get_string("unsupported_files", "plagiarism_compilatio"),
            "content" => $list,
        );
    }

    // Display a notification for the too short files.
    $files = compilatio_get_too_short_files($cmid);
    if (count($files) !== 0) {
        $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
        $alerts[] = array(
            "class" => "danger",
            "title" => get_string("tooshort_files", "plagiarism_compilatio",
                get_config('plagiarism_compilatio', 'nb_mots_min')),
            "content" => $list,
        );
    }

    // Display a notification for the too long files.
    $files = compilatio_get_too_long_files($cmid);
    if (count($files) !== 0) {
        $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";
        $alerts[] = array(
            "class" => "danger",
            "title" => get_string("toolong_files", "plagiarism_compilatio",
                get_config('plagiarism_compilatio', 'nb_mots_max')),
            "content" => $list,
        );
    }

    // Display a notification for the unextractable files.
    $files = compilatio_get_unextractable_files($cmid);
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
            <button class='compilatio-button comp-button comp-restart-btn' >
                <i class='fa fa-play-circle'></i>
                " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
            </button>";
    }

    // Display a notification for failed analyses.
    $files = compilatio_get_failed_analysis_files($cmid);
    if (count($files) !== 0) {

        $list = "<ul><li>" . implode("</li><li>", $files) . "</li></ul>";

        $alerts[] = array(
            "class" => "danger",
            "title" => get_string("failedanalysis_files", "plagiarism_compilatio"),
            "content" => $list,
        );

        $url = $PAGE->url;
        $url->param('restartfailedanalysis', true);
        $restartfailedanalysisbutton = "
            <button class='compilatio-button comp-button comp-restart-btn' >
                <i class='fa fa-play-circle'></i>
                " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
            </button>";
    }

    // Display restart analyses button for timeout.
    $files = compilatio_get_files_by_status_code($cmid, 'timeout');
    if (count($files) !== 0) {
        $url = $PAGE->url;
        $url->param('restartfailedanalysis', true);
        $restartfailedanalysisbutton = "
            <button class='compilatio-button comp-button comp-restart-btn' >
                <i class='fa fa-play-circle'></i>
                " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
            </button>";
    }

    if ($module == 'assign') {
        $countdocnotuploaded = count(compilatio_get_non_uploaded_documents($cmid));

        if ($countdocnotuploaded !== 0) {

            $alerts[] = array(
                "class" => "danger",
                "title" => get_string("unsent_documents", "plagiarism_compilatio"),
                "content" => get_string("unsent_documents_content", "plagiarism_compilatio"),
            );

            $startallanalysisbutton = "
                <button class='compilatio-button comp-button comp-start-btn' >
                    <i class='fa fa-play-circle'></i>
                    " . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
                </button>";

            $restartfailedanalysisbutton = "
                <button class='compilatio-button comp-button comp-restart-btn' >
                    <i class='fa fa-play-circle'></i>
                    " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
                </button>";
        }
    } else {
        $countdocnotuploaded = 0;
    }

    // Add the Compilatio news to the alerts displayed :.
    $alerts = array_merge($alerts, compilatio_display_news());

    $output .= "<div id='compilatio-container'>";

    // Display the tabs: Notification tab will be hidden if there is 0 alerts.
    $output .= "<div id='compilatio-tabs' style='display:none'>";

    // Display logo.
    $output .= output_helper::get_logo();

    // Help icon.
    $output .= "<div title='" . get_string("compilatio_help_assign", "plagiarism_compilatio") .
        "' id='show-help' class='compilatio-icon'><i class='fa fa-question-circle fa-2x'></i></div>";

    // Stat icon.
    $output .= "<div id='show-stats' class='compilatio-icon'  title='" .
    get_string("display_stats", "plagiarism_compilatio") .
        "'><i class='fa fa-bar-chart fa-2x'></i></div>";

    // Alert icon.
    if (count($alerts) !== 0) {
        $output .= "<div id='compilatio-show-notifications' title='";
        $output .= get_string("display_notifications", "plagiarism_compilatio");
        $output .= "' class='compilatio-icon active' ><i class='fa fa-bell fa-2x'></i>";
        $output .= "<span id='count-alerts'>" . count($alerts) . "</span></div>";
    }

    if ($plagiarismsettings["allow_search_tab"]) {
        // Search icon.
        $output .= "<div title='" . get_string("compilatio_search_tab", "plagiarism_compilatio") .
            "' id='show-search' class='compilatio-icon'><i class='fa fa-search fa-2x'></i></div>";
    }

    // Hide/Show button.
    $output .= "
        <div id='compilatio-hide-area' class='compilatio-icon'  title='" .
    get_string("hide_area", "plagiarism_compilatio") . "'>
            <i class='fa fa-chevron-up fa-2x'></i>
        </div>";

    $output .= "</div>";

    $output .= "<div class='compilatio-clear'></div>";

    // Home tab.
    $output .= "<div id='compi-home' class='compilatio-tabs-content'>
                    <p>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>";
    if ($module == "quiz") {
        $nbmotsmin = get_config('plagiarism_compilatio', 'nb_mots_min');
        $output .= "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>";
    }
    $output .= "</div>";

    // Help tab.
    $output .= "<div id='compi-help' class='compilatio-tabs-content'>";

    if (empty($plagiarismsettings['idgroupe'])) {
        $output .= "<p>" . get_string('helpcenter_error', 'plagiarism_compilatio')
            . "<a href='https://support.compilatio.net/'>https://support.compilatio.net</a></p>";
    } else {
        $output .= "<p><a href='../../plagiarism/compilatio/helpcenter.php?idgroupe=" . $plagiarismsettings['idgroupe'] . "'" .
        "target='_blank' >" . get_string('helpcenter', 'plagiarism_compilatio') . "
        <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='-5 -11 24 24'>
            <path fill='none' stroke='#555' stroke-linecap='round'
            stroke-linejoin='round' d='M8 2h4v4m0-4L6 8M4 2H2v10h10v-2'></path>
        </svg></a></p>";
    }

    $output .= "
            <p><a href='http://etat-services.compilatio.net/?lang=FR'" .
                "target='_blank' >" . get_string('goto_compilatio_service_status', 'plagiarism_compilatio') . "
                <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='-5 -11 24 24'>
                    <path fill='none' stroke='#555' stroke-linecap='round'
                    stroke-linejoin='round' d='M8 2h4v4m0-4L6 8M4 2H2v10h10v-2'></path>
                </svg></a></p>
        </div>";

    // Stats tab.
    $output .= "
        <div id='compi-stats' class='compilatio-tabs-content'>
            <h5>" . get_string("tabs_title_stats", "plagiarism_compilatio") . " : </h5>" .
    compilatio_get_statistics($cmid) .
        "</div>";

    // Alerts tab.
    if (count($alerts) !== 0) {
        $output .= "<div id='compi-notifications' class='compilatio-tabs-content'>";
        $output .= "<h5 id='compi-notif-title'>" . get_string("tabs_title_notifications", "plagiarism_compilatio") . " : </h5>";

        foreach ($alerts as $alert) {
            $output .= "
                <div class='compilatio-alert compilatio-alert-" . $alert["class"] . "'>" .
                "<strong>" . $alert["title"] . "</strong><br/>" .
                $alert["content"] .
                "</div>";
        }

        $output .= "</div>";
    }

    $iddocument = optional_param('idcourt', null, PARAM_RAW);

    // Search tab.
    $output .= "<div id='compi-search' class='compilatio-tabs-content'>
        <h5>" . get_string("compilatio_search_tab", "plagiarism_compilatio") . "</h5>
        <p>" . get_string("compilatio_search_help", "plagiarism_compilatio") . "</p>
        <form class='form-inline' action=" . $PAGE->url . " method='post'>
            <input class='form-control m-2' type='text' id='idcourt' name='idcourt' value='" . $iddocument
                . "' placeholder='" . get_string("compilatio_iddocument", "plagiarism_compilatio") . "'>
            <input class='btn btn-primary' type='submit' value='" .get_string("compilatio_search", "plagiarism_compilatio"). "'>
        </form>";

    if (!empty($iddocument)) {
        $sql = "SELECT usr.lastname, usr.firstname, cf.idcourt, cf.cm FROM {plagiarism_compilatio_files} cf
            JOIN {user} usr on cf.userid = usr.id
            WHERE cf.idcourt = ? OR cf.externalid = ?";
        $doc = $DB->get_record_sql($sql, array($iddocument, $iddocument));

        if ($doc) {
            $module = get_coursemodule_from_id(null, $doc->cm);
            $doc->modulename = $module->name;
            $output .= get_string('compilatio_author', 'plagiarism_compilatio', $doc);
        } else {
            $output .= get_string("compilatio_search_notfound", "plagiarism_compilatio");
        }
    }

    $output .= "</div>";

    // Display timed analysis date.
    if (isset($programmedanalysisdate)) {
        $output .= "<p id='compilatio-programmed-analysis'>$programmedanalysisdate</p>";
    }

    $output .= "</div>";

    if (has_capability('plagiarism/compilatio:triggeranalysis', $PAGE->context)) {
        // Display buttons :.
        $output .= "<div id='compilatio-button-container'>";

        // Update button.
        $url = $PAGE->url;
        $url->param('compilatioupdate', true);
        $output .= "
            <button class='compilatio-button comp-button'>
                    <i class='fa fa-refresh'></i>
                    " . get_string('updatecompilatioresults', 'plagiarism_compilatio') . "
            </button>";
        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'refreshButton',
            array($CFG->httpswwwroot, $plagiarismfilesids, $countdocnotuploaded,
            get_string('update_in_progress', 'plagiarism_compilatio')));

        // Start all analysis button.
        if (isset($startallanalysisbutton)) {
            $output .= $startallanalysisbutton;
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAllAnalysis',
                array($CFG->httpswwwroot, $cmid, get_string("start_analysis_title", "plagiarism_compilatio"),
                get_string("start_analysis_in_progress", "plagiarism_compilatio")));
        }

        if (isset($restartfailedanalysisbutton)) {
            $output .= $restartfailedanalysisbutton;
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'restartFailedAnalysis',
                array($CFG->httpswwwroot, $cmid, get_string("restart_failed_analysis_title", "plagiarism_compilatio"),
                get_string("restart_failed_analysis_in_progress", "plagiarism_compilatio")));
        }

        $output .= "</div>";
    }

    $params = array(
        $CFG->httpswwwroot,
        count($alerts),
        $iddocument,
        "<div id='compilatio-show-notifications' title='" . get_string("display_notifications", "plagiarism_compilatio")
            . "' class='compilatio-icon active'><i class='fa fa-bell fa-2x'></i><span id='count-alerts'>1</span></div>",
        "<div id='compi-notifications'><h5 id='compi-notif-title'>" .
            get_string("tabs_title_notifications", "plagiarism_compilatio") . " : </h5>"
    );

    $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'compilatioTabs', $params);

    return $output;
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

    $configs = $DB->get_records('plagiarism_compilatio_apicon');
    foreach ($configs as $config) {
        if ($config->startdate != 0 && $config->startdate <= time()) {
            set_config('apiconfigid', $config->id, 'plagiarism_compilatio');
            $config->startdate = 0;
            $DB->update_record('plagiarism_compilatio_apicon', $config);
        }
    }

    // Send data about plugin version to Compilatio.
    compilatio_send_statistics();

    // Update the expiration date in the DB.
    compilatio_update_account_expiration_date();

    // Get most recent news from Compilatio :.
    compilatio_update_news();

    // Update the "Compilatio unavailable" marker in the database.
    compilatio_update_connection_status();

    $filemaxsize = ws_helper::get_allowed_file_max_size();
    $filetypes = ws_helper::get_allowed_file_types();

    if (empty(get_config('plagiarism_compilatio', 'nb_mots_min'))) {
        set_config('nb_mots_min', 100, 'plagiarism_compilatio');
    }

    $compilatio = compilatio_get_compilatio_service(get_config('plagiarism_compilatio', 'apiconfigid'));
    $idgroupe = $compilatio->get_id_groupe();

    set_config('file_max_size', json_encode($filemaxsize), 'plagiarism_compilatio');
    set_config('file_types', json_encode($filetypes), 'plagiarism_compilatio');
    set_config('idgroupe', $idgroupe, 'plagiarism_compilatio');
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
        $plagiarismfiles = $DB->get_records("plagiarism_compilatio_files",
            array("statuscode" => "pending", "recyclebinid" => null));

        foreach ($plagiarismfiles as $plagiarismfile) {
            $module = get_coursemodule_from_id(null, $plagiarismfile->cm);
            if (empty($module)) {
                mtrace("Course module id:" .$plagiarismfile->cm . " does not exist, deleting record #" . $plagiarismfile->id);
                $DB->delete_records('plagiarism_compilatio_files', array('id' => $plagiarismfile->id));
                continue;
            }

            $plugincm = compilatio_cm_use($plagiarismfile->cm);
            if (isset($plugincm['indexing_state'])) {
                $indexingstate = $plugincm['indexing_state'];
            } else {
                $indexingstate = true;
            }

            if (compilatio_student_analysis($plugincm['compi_student_analyses'], $plagiarismfile->cm, $plagiarismfile->userid)) {
                $indexingstate = false;
            }

            $tmpfile = compilatio_get_temp_file($plagiarismfile->filename);

            if ($tmpfile !== false) {
                $compid = compilatio_send_file_to_compilatio($plagiarismfile, $plagiarismsettings, $tmpfile);
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

                $compid = compilatio_send_file_to_compilatio($plagiarismfile, $plagiarismsettings, $file);
            }

            // Wait for document to be extract in Elsaf to be indexed.
            unset($result);
            do {
                if (isset($result)) {
                    sleep(1);
                }
                $result = ws_helper::set_indexing_state($compid, $indexingstate, $plagiarismfile->apiconfigid);
            } while ($result === 'Error set_indexing_state() setIndexRefLibrary error Document extraction in progress');
        }
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

    if (!empty($eventdata->postid)) {
        $filename = "post-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->postid . ".htm";
    } else if (isset($eventdata->attemptid)) {
        $filename = "quiz-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->attemptid
            . "-" . $eventdata->question . ".htm";
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
function compilatio_get_form_elements($mform, $defaults = false, $modulename = '') {

    global $PAGE, $CFG;

    $ynoptions = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );

    $tiioptions = array(
        0 => get_string("never"),
        1 => get_string("immediately", "plagiarism_compilatio"),
        2 => get_string("showwhenclosed", "plagiarism_compilatio"),
    );

    $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));

    if ($modulename === 'mod_quiz') {
        $nbmotsmin = get_config('plagiarism_compilatio', 'nb_mots_min');
        $mform->addElement('html', "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>");
    }

    $mform->addElement('select', 'use_compilatio', get_string("use_compilatio", "plagiarism_compilatio"), $ynoptions);
    $mform->setDefault('use_compilatio', 1);

    $analysistypes = array(COMPILATIO_ANALYSISTYPE_MANUAL => get_string('analysistype_manual', 'plagiarism_compilatio'),
        COMPILATIO_ANALYSISTYPE_PROG => get_string('analysistype_prog', 'plagiarism_compilatio'));
    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('select', 'compilatio_analysistype',
            get_string('analysis', 'plagiarism_compilatio'),
            $analysistypes);
        $mform->addHelpButton('compilatio_analysistype', 'analysis', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_analysistype', COMPILATIO_ANALYSISTYPE_MANUAL);
    }

    if (!$defaults) { // Only show this inside a module page - not on default settings pages.
        $mform->addElement('date_time_selector',
            'compilatio_timeanalyse',
            get_string('analysis_date', 'plagiarism_compilatio'),
            array('optional' => false));
        $mform->setDefault('compilatio_timeanalyse', time() + 7 * 24 * 3600);
        $mform->disabledif('compilatio_timeanalyse', 'compilatio_analysistype', 'noteq', COMPILATIO_ANALYSISTYPE_PROG);

        $lang = current_language();
        if ($lang == 'fr' && $CFG->version >= 2017111300) { // Method hideIf is available since moodle 3.4.
            $group = [];
            $group[] = $mform->createElement('static', 'calendar', '',
                "<img style='width: 45em;' src='https://content.compilatio.net/images/calendrier_affluence_magister.png'>");
            $mform->addGroup($group, 'calendargroup', '', ' ', false);
            $mform->hideIf('calendargroup', 'compilatio_analysistype', 'noteq', COMPILATIO_ANALYSISTYPE_PROG);
        }
    }

    $mform->addElement('select', 'compilatio_show_student_score',
        get_string("compilatio_display_student_score", "plagiarism_compilatio"),
        $tiioptions);
    $mform->addHelpButton('compilatio_show_student_score', 'compilatio_display_student_score', 'plagiarism_compilatio');
    if (get_config("plagiarism_compilatio", "allow_teachers_to_show_reports") === '1') {
        $mform->addElement('select', 'compilatio_show_student_report',
            get_string("compilatio_display_student_report", "plagiarism_compilatio"),
            $tiioptions);
        $mform->addHelpButton('compilatio_show_student_report', 'compilatio_display_student_report', 'plagiarism_compilatio');
    } else {
        $mform->addElement('html', '<p>' . get_string("admin_disabled_reports", "plagiarism_compilatio") . '</p>');
    }

    if (get_config("plagiarism_compilatio", "allow_student_analyses") === '1' && !$defaults) {
        if ($mform->elementExists('submissiondrafts')) {
            $mform->addElement('select', 'compi_student_analyses',
                get_string("compi_student_analyses", "plagiarism_compilatio"), $ynoptions);
            $mform->addHelpButton('compi_student_analyses', 'compi_student_analyses', 'plagiarism_compilatio');

            $plugincm = compilatio_cm_use($PAGE->context->instanceid);
            if ($plugincm["compi_student_analyses"] === '0') {
                $mform->disabledif('compi_student_analyses', 'submissiondrafts', 'eq', '0');
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

    $mform->addElement('select', 'compilatio_studentemail',
        get_string("compilatio_studentemail", "plagiarism_compilatio"), $ynoptions);
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
    $size = json_decode(get_config('plagiarism_compilatio', 'file_max_size'));

    $mform->addElement('html', '<p>' . get_string("max_file_size_allowed", "plagiarism_compilatio", $size) . '</p>');

    // File types allowed.
    $filetypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));
    $mform->addElement('html', '<div>' . get_string("help_compilatio_format_content", "plagiarism_compilatio") . '</div>');
    $mform->addElement('html', '<table style="margin-left:10px;"><tbody>');
    foreach ($filetypes as $filetype) {
        $mform->addElement('html', '<tr><td style="padding-right:25px;">.' . $filetype->type .
            '</td><td>' . $filetype->title . '</td></tr>');
    }
    $mform->addElement('html', '</tbody></table>');

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
 * compilatio_remove_duplicates
 *
 * Deindex and remove document(s) in Compilatio
 * Remove entry(ies) in plagiarism_compilatio_files table
 *
 * @param array    $duplicates
 * @param bool     $deletefilesmoodledb
 * @return boolean true if all documents have been processed, false otherwise
 */
function compilatio_remove_duplicates($duplicates, $deletefilesmoodledb = true) {

    if (is_array($duplicates)) {

        global $DB;

        $i = 0;
        foreach ($duplicates as $doc) {
            if (is_null($doc->externalid)) {
                if ($deletefilesmoodledb) {
                    $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                }
            } else {
                $compilatio = compilatio_get_compilatio_service($doc->apiconfigid);
                // Deindex document.
                if ($compilatio->set_indexing_state($doc->externalid, 0)) {
                    // Delete document.
                    $compilatio->del_doc($doc->externalid);
                    // Delete DB record.
                    if ($deletefilesmoodledb) {
                        $DB->delete_records('plagiarism_compilatio_files', array('id' => $doc->id));
                    }
                    $i++;
                } else {
                    mtrace('Error deindexing document ' . $doc->externalid);
                }
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
        $plagiarismfile->apiconfigid = 0;

        // Add new entry and get plagiarism_compilatio_file table record `id` for update_record_raw().
        if (($compid = $DB->insert_record('plagiarism_compilatio_files', $plagiarismfile, true)) === false) {
            throw new dml_write_exception("insert into compilatio_files failed");
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
        $attemptallowed = compilatio_check_attempt_timeout($plagiarismfile, true);
        if (!$attemptallowed) {
            return false;
        }
        // Increment attempt number.
        $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        $compid = compilatio_send_file_to_compilatio($plagiarismfile, $plagiarismsettings, $file);
        return false;
    }

    return true;
}

/**
 * Function to check timesubmitted and attempt to see if we need to delay an API check
 * Also checks max attempts to see if it has exceeded.
 *
 * @param  array $plagiarismfile    A row of plagiarism_compilatio_files in database
 * @param  bool $hasmaxattempt      True for queue_file, false for get_scores
 * @return bool                     Return true if succeed, false otherwise
 */
function compilatio_check_attempt_timeout($plagiarismfile, $hasmaxattempt = false) {

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
    }

    if ($hasmaxattempt) {
        // Check if we have exceeded the max attempts.
        if ($plagiarismfile->attempt > $maxattempts) {
            $plagiarismfile->statuscode = 'timeout';
            $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
            return true; // Return true to cancel the event.
        }
    }

    // Now calculate wait time.
    $i = 0;
    $wait = 0;
    while ($i < $plagiarismfile->attempt) {
        $time = $submissiondelay * ($plagiarismfile->attempt - $i);
        if ($time > $maxsubmissiondelay) {
            $time = $maxsubmissiondelay;
        }
        $wait += $time;
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

    global $DB, $CFG, $COURSE;

    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();

    $mimetype = compilatio_check_file_type($filename);
    if (empty($mimetype)) { // Sanity check on filetype - this should already have been checked.
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        mtrace("no mime type for this file found #" . $plagiarismfile->id);
        return false;
    }

    // Display this log only on CRON exec.
    if (!defined("COMPILATIO_MANUAL_SEND")) {
        mtrace("sending file #" . $plagiarismfile->id);
    }

    $compilatio = compilatio_get_compilatio_service($plagiarismsettings['apiconfigid']);

    $module = get_coursemodule_from_id(null, $plagiarismfile->cm);
    if (empty($module)) {
        mtrace("could not find this module - it may have been deleted?");
        return false;
    }

    $name = format_string($module->name, true, $COURSE->id) . "(" . $plagiarismfile->cm . ")_" . $filename;
    $filecontents = (!empty($file->filepath)) ? file_get_contents($file->filepath) : $file->get_content();
    $idcompi = $compilatio->send_doc($name, // Title.
        $name, // Description.
        $filename, // File_name.
        $mimetype, // Mime data.
        $filecontents); // Doc content.

    if (compilatio_valid_md5($idcompi)) {
        $plagiarismfile->externalid = $idcompi;
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_ACCEPTED;
        $plagiarismfile->apiconfigid = $plagiarismsettings['apiconfigid'];
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
        return $idcompi;
    }

    $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_UNEXTRACTABLE;
    $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    mtrace("invalid compilatio response received - will try again later." . $idcompi);
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
    $sql = "statuscode = ? OR statuscode = ?";
    $params = array(COMPILATIO_STATUSCODE_ANALYSING, COMPILATIO_STATUSCODE_IN_QUEUE);
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
 * @param bool $apiconfigid
 * @return $quotas
 */
function compilatio_getquotas($apiconfigid = null) {

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');

    if (isset($apiconfigid)) {
        $compilatio = compilatio_get_compilatio_service($apiconfigid);
    } else {
        $compilatio = compilatio_get_compilatio_service($plagiarismsettings['apiconfigid']);
    }

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

    global $DB, $OUTPUT;

    if (empty($plagiarismsettings)) {
        $plagiarismsettings = (array) get_config('plagiarism_compilatio');
    }

    $compilatio = compilatio_get_compilatio_service($plagiarismfile->apiconfigid);

    $analyse = $compilatio->start_analyse($plagiarismfile->externalid);

    if ($analyse === true) {
        // Update plagiarism record.
        $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_IN_QUEUE;
        $plagiarismfile->timesubmitted = time();
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    } else {
        // VP SOAP Faults.
        if ($analyse->code == 'INVALID_ID_DOCUMENT') {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_NOT_FOUND;
            $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
            return $analyse;
        } else if ($analyse->code == 'NOT_ENOUGH_WORDS') {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_TOO_SHORT;
            $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
            preg_match('~least (\d+)~', $analyse->string, $nbmotsmin);
            set_config('nb_mots_min', $nbmotsmin[1], 'plagiarism_compilatio');
            return $analyse;

        // Elastisafe SOAP Faults.
        } else if ($analyse->code == 'startDocumentAnalyse error') {
            if ($analyse->string == 'Invalid document id') {
                $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_NOT_FOUND;
                $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
                return $analyse;

            } else if (strpos($analyse->string, 'max file size') !== false) {
                $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_TOO_LONG;
                $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
                preg_match('~of (\d+)~', $analyse->string, $nbmotsmax);
                set_config('nb_mots_max', $nbmotsmax[1], 'plagiarism_compilatio');
                return $analyse;
            }

        } else {
            echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio') . $analyse->string);
            return $analyse;
        }
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

    if (preg_match('/^[a-f0-9]{32}$/', $hash)) {
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

    global $DB;

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');
    $compilatio = compilatio_get_compilatio_service($plagiarismfile->apiconfigid);

    $docstatus = $compilatio->get_doc($plagiarismfile->externalid);

    if (isset($docstatus->documentStatus->status)) {
        if ($docstatus->documentStatus->status == "ANALYSE_COMPLETE") {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_COMPLETE;
            $plagiarismfile->similarityscore = round($docstatus->documentStatus->indice);
            $plagiarismfile->idcourt = $docstatus->documentProperties->Shortcut;
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
        } else if ($docstatus->documentStatus->status == "ANALYSE_CRASHED") {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_FAILED;
        }
    }
    if (!$manuallytriggered) {
        $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
    }

    if (is_object($docstatus)) {
        // Failed analysis error when similarity score = -9%.
        if ($docstatus->documentStatus->indice == -9) {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_FAILED;
        }

        $nbmotsmin = get_config('plagiarism_compilatio', 'nb_mots_min');
        if (!empty($nbmotsmin) && $docstatus->documentProperties->wordCount < $nbmotsmin) {
            $plagiarismfile->statuscode = COMPILATIO_STATUSCODE_TOO_SHORT;
        }

        // Optional yellow warning in submissions.
        $plagiarismfile->errorresponse = $docstatus->documentProperties->warning;
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    }
}

/**
 * Start all files analysis
 *
 * @param  array $plagiarismfiles Files
 * @return void
 */
function compilatio_analyse_files($plagiarismfiles) {

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');
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
    $plagiarismsettings = (array) get_config('plagiarism_compilatio');
    $compilatio = compilatio_get_compilatio_service($plagiarismsettings['apiconfigid']);

    return $compilatio->get_account_expiration_date();
}

/**
 * Send informations about this configuration to Compilatio
 *
 * @return bool : False if any error occurs, true otherwise
 */
function compilatio_send_statistics() {

    global $CFG, $DB;

    $language = $CFG->lang;
    $releasephp = phpversion();
    $releasemoodle = $CFG->release;
    $releaseplugin = get_config('plagiarism_compilatio', 'version');
    $cronfrequencyobject = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));
    if ($cronfrequencyobject != null) {
        $cronfrequency = $cronfrequencyobject->value;
    } else {
        $cronfrequency = 0;
    }

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');
    $compilatio = compilatio_get_compilatio_service($plagiarismsettings['apiconfigid']);

    return $compilatio->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency);
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
        FROM {plagiarism_compilatio_files} pcf
        WHERE pcf.cm=?";

    $countallsql = $sql;
    $documentscount = $DB->count_records_sql($countallsql, array($cmid));

    $countanalyzedsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "'";
    $countanalyzed = $DB->count_records_sql($countanalyzedsql, array($cmid));

    $counthigherthanredsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore>$redthreshold";
    $counthigherthanred = $DB->count_records_sql($counthigherthanredsql, array($cmid));

    $countlowerthangreensql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "' AND similarityscore<=$greenthreshold";
    $countlowerthangreen = $DB->count_records_sql($countlowerthangreensql, array($cmid));

    $countunsupportedsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNSUPPORTED . "'";
    $countunsupported = $DB->count_records_sql($countunsupportedsql, array($cmid));

    $countunextractablesql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_UNEXTRACTABLE . "'";
    $countunextractable = $DB->count_records_sql($countunextractablesql, array($cmid));

    $counttooshortsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_TOO_SHORT . "'";
    $counttooshort = $DB->count_records_sql($counttooshortsql, array($cmid));

    $counttoolongsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_TOO_LONG . "'";
    $counttoolong = $DB->count_records_sql($counttoolongsql, array($cmid));

    $countnotfoundsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_NOT_FOUND . "'";
    $countnotfound = $DB->count_records_sql($countnotfoundsql, array($cmid));

    $countfailedsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_FAILED . "'";
    $countfailed = $DB->count_records_sql($countfailedsql, array($cmid));

    $countinqueuesql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_IN_QUEUE . "'";
    $countinqueue = $DB->count_records_sql($countinqueuesql, array($cmid));

    $countanalysingsql = $sql . "AND statuscode='" . COMPILATIO_STATUSCODE_ANALYSING . "'";
    $countanalysing = $DB->count_records_sql($countanalysingsql, array($cmid));

    $averagesql = "
        SELECT AVG(similarityscore) avg
        FROM {plagiarism_compilatio_files} pcf
        WHERE pcf.cm=? AND statuscode='" . COMPILATIO_STATUSCODE_COMPLETE . "'";

    $avgresult = $DB->get_record_sql($averagesql, array($cmid));
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
    if ($counttooshort !== 0) {
        $errors[] = array("not_analyzed_tooshort", $counttooshort);
    }
    if ($counttoolong !== 0) {
        $errors[] = array("not_analyzed_toolong", $counttoolong);
    }
    if ($countnotfound !== 0) {
        $errors[] = array("documents_notfound", $countnotfound);
    }
    if ($countfailed !== 0) {
        $errors[] = array("documents_failed", $countfailed);
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
 * Lists too short documents in the assignment
 *
 * @param  string $cmid Course module ID
 * @return array        containing the student & the file
 */
function compilatio_get_too_short_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_TOO_SHORT);
}

/**
 * Lists too long documents in the assignment
 *
 * @param  string $cmid Course module ID
 * @return array        containing the student & the file
 */
function compilatio_get_too_long_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_TOO_LONG);
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
 * Lists failed analysis documents in the assignment
 *
 * @param  string $cmid Course module ID
 * @return array        containing the student & the file
 */
function compilatio_get_failed_analysis_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_FAILED);
}

/**
 * List files that have reach max attempts
 *
 * @param  int $cmid    Course module ID
 * @return array        Array contains files
 */
function compilatio_get_max_attempts_files($cmid) {
    return compilatio_get_files_by_status_code($cmid, COMPILATIO_STATUSCODE_UNEXTRACTABLE, 6);
}

/**
 * Lists files of an assignment according to the status code
 *
 * @param  string $cmid       Course module ID
 * @param  int    $statuscode Status Code
 * @param  int    $attempt    Number of attempts
 * @return array              containing the student & the file
 */
function compilatio_get_files_by_status_code($cmid, $statuscode, $attempt = 0) {

    global $DB;

    $sql = "SELECT DISTINCT pcf.id, pcf.filename, pcf.userid
        FROM {plagiarism_compilatio_files} pcf
        WHERE pcf.cm=? AND statuscode = ? AND pcf.attempt >= ?";

    $files = $DB->get_records_sql($sql, array($cmid, $statuscode, $attempt));

    if (!empty($files)) {
        // Don't display user name for anonymous assign.
        $sql = "SELECT blindmarking, assign.id FROM {course_modules} cm
            JOIN {assign} assign ON cm.instance= assign.id
            WHERE cm.id = $cmid";
        $anonymousassign = $DB->get_record_sql($sql);

        if (!empty($anonymousassign) && $anonymousassign->blindmarking) {
            foreach ($files as $file) {
                $anonymousid = $DB->get_field('assign_user_mapping', 'id',
                    array("assignment" => $anonymousassign->id, "userid" => $file->userid));
                $file->user = get_string('hiddenuser', 'assign') . " " . $anonymousid;
            }

            return array_map(
                function ($file) {
                    return $file->user . " : " . $file->filename;
                }, $files);
        } else {
            foreach ($files as $file) {
                $user = $DB->get_record('user', array("id" => $file->userid));
                $file->lastname = $user->lastname;
                $file->firstname = $user->firstname;
            }

            return array_map(
                function ($file) {
                    return $file->lastname . " " . $file->firstname . " : " . $file->filename;
                }, $files);
        }
    } else {
        return array();
    }
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
 * Update the news of Compilatio
 * Remove old entries and insert new ones.
 */
function compilatio_update_news() {

    global $DB;

    $compilatio = compilatio_get_compilatio_service(get_config('plagiarism_compilatio', 'apiconfigid'));

    $news = $compilatio->get_alerts();

    if ($news !== false) {
        $DB->delete_records_select('plagiarism_compilatio_news', '1=1');
        foreach ($news as $new) {
            $DB->insert_record("plagiarism_compilatio_news", $new);
        }
    }

    $news = $compilatio->get_technical_news();

    if ($news !== false) {
        $DB->delete_records_select('plagiarism_compilatio_news', '1=1');
        foreach ($news as $new) {
            $new->message_en = compilatio_decode($new->message_en);
            $new->message_fr = compilatio_decode($new->message_fr);
            $new->message_it = compilatio_decode($new->message_it);
            $new->message_es = compilatio_decode($new->message_es);
            $new->message_de = compilatio_decode($new->message_de);
            unset($new->id);
            $DB->insert_record("plagiarism_compilatio_news", $new);
        }
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
 * @return array containing almerts according to the news in the DB.
 */
function compilatio_display_news() {

    global $DB;

    $language = substr(current_language(), 0, 2);

    $news = $DB->get_records_select('plagiarism_compilatio_news', 'end_display_on>? AND begin_display_on<?', array(time(), time()));

    $alerts = array();

    foreach ($news as $new) {
        $message = $new->{'message_' . $language} ?? $new->message_en;

        // Get the title of the notification according to the type of news:.
        $title = "<i class='fa-lg fa fa-info-circle'></i>";
        $class = "info";
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
 * Uploads files to compilatio
 *
 * @param array  $files Array of file records
 * @param string $cmid  cmid of the assignment
 */
function compilatio_upload_files($files, $cmid) {

    global $DB;

    $compilatio = new plagiarism_plugin_compilatio();
    $plagiarismsettings = $compilatio->get_settings();

    $analysistype = $DB->get_field('plagiarism_compilatio_config',
        'value',
        array('cm' => $cmid, "name" => "compilatio_analysistype"));
    $timeanalysis = $DB->get_field('plagiarism_compilatio_config',
        'value',
        array('cm' => $cmid, "name" => "compilatio_timeanalyse"));

    foreach ($files as $file) {
        $userid = $DB->get_field('assign_submission', 'userid', array('id' => $file->get_itemid()));

        compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings, true); // Send the file to Compilatio.
    }

    foreach ($files as $file) {
        $userid = $DB->get_field('assign_submission', 'userid', array('id' => $file->get_itemid()));

        /* Start analysis if the settings are on "manual" or "timed" and the planned time is greater than the current time
        Starting "auto" analysis is handled in "compilatio_send_file" */
        if ($analysistype == COMPILATIO_ANALYSISTYPE_MANUAL ||
            ($analysistype == COMPILATIO_ANALYSISTYPE_PROG &&
                time() >= $timeanalysis)) {
            $plagiarismfile = compilatio_get_plagiarism_file($cmid, $userid, $file);
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
            modules.name "module_type",
            CONCAT(COALESCE(assign.name, \'\'), COALESCE(forum.name, \'\'), COALESCE(workshop.name, \'\'),
            COALESCE(quiz.name, \'\')) "module_name",
            AVG(similarityscore) "avg",
            MIN(similarityscore) "min",
            MAX(similarityscore) "max",
            COUNT(DISTINCT plagiarism_compilatio_files.id) "count"
        FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
        JOIN {course_modules} course_modules
            ON plagiarism_compilatio_files.cm = course_modules.id
        JOIN {modules} modules ON modules.id = course_modules.module
        LEFT JOIN {assign} assign ON course_modules.instance = assign.id AND course_modules.module = 1
        LEFT JOIN {forum} forum ON course_modules.instance = forum.id AND course_modules.module = 9
        LEFT JOIN {workshop} workshop ON course_modules.instance = workshop.id AND course_modules.module = 23
        LEFT JOIN {quiz} quiz ON course_modules.instance = quiz.id AND course_modules.module = 17
        JOIN {course} course ON course_modules.course= course.id
        WHERE statuscode=\'Analyzed\'
        GROUP BY cm,
            course.id,
            course.fullname,
            assign.name,
            forum.name,
            quiz.name,
            workshop.name,
            modules.name
        ORDER BY course.fullname, assign.name';

    $rows = $DB->get_records_sql($sql);

    $results = array();
    foreach ($rows as $row) {
        $query = '
            SELECT usr.id "userid",
                usr.firstname "firstname",
                usr.lastname "lastname"
            FROM {course} course
            JOIN {context} context ON context.instanceid= course.id
            JOIN {role_assignments} role_assignments ON role_assignments.contextid= context.id
            JOIN {user} usr ON role_assignments.userid= usr.id
            WHERE context.contextlevel=50
                AND role_assignments.roleid=3
                AND course.id='. $row->id;

        $teachers = $DB->get_records_sql($query);
        $courseurl = new moodle_url('/course/view.php', array('id' => $row->id));
        $assignurl = new moodle_url('/mod/' . $row->module_type . '/view.php', array('id' => $row->cm, 'action' => "grading"));

        $result = array();
        if ($html) {
            $result["course"] = "<a href='$courseurl'>$row->course</a>";
            $result["assign"] = "<a href='$assignurl'>$row->module_name</a>";

        } else {
            $result["courseid"] = $row->id;
            $result["course"] = $row->course;
            $result["assignid"] = $row->cm;
            $result["assign"] = $row->module_name;
        }

        $result["analyzed_documents_count"] = $row->count;
        $result["minimum_rate"] = $row->min;
        $result["maximum_rate"] = $row->max;
        $result["average_rate"] = round($row->avg, 2);

        $result["teacher"] = "";
        $teacherid = [];
        $teachername = [];
        foreach ($teachers as $teacher) {
            $userurl = new moodle_url('/user/view.php', array('id' => $teacher->userid));
            if ($html) {
                $result["teacher"] .= "- <a href='$userurl'>$teacher->lastname $teacher->firstname</a></br>";

            } else {
                array_push($teacherid, $teacher->userid);
                array_push($teachername, $teacher->lastname . " " . $teacher->firstname);
            }
        }
        if (!$html) {
            $result["teacherid"] = implode(', ', $teacherid);
            $result["teacher"] = implode(', ', $teachername);
        }

        if ($html) {
            $result["errors"] = "";
            $sql = "SELECT COUNT(DISTINCT id) FROM {plagiarism_compilatio_files} WHERE cm=? AND statuscode=?";

            $countunsupported = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_UNSUPPORTED));
            if ($countunsupported > 0) {
                $result["errors"] .= '- ' . get_string("stats_unsupported", "plagiarism_compilatio")
                . ' : ' . $countunsupported . '</br>';
            };

            $countunextractable = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_UNEXTRACTABLE));
            if ($countunextractable > 0) {
                $result["errors"] .= '- ' . get_string("stats_unextractable", "plagiarism_compilatio")
                . ' : ' . $countunextractable . '</br>';
            };

            $counttooshort = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_TOO_SHORT));
            if ($counttooshort > 0) {
                $result["errors"] .= '- ' . get_string("stats_tooshort", "plagiarism_compilatio")
                . ' : ' . $counttooshort . '</br>';
            };

            $counttoolong = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_TOO_LONG));
            if ($counttoolong > 0) {
                $result["errors"] .= '- ' . get_string("stats_toolong", "plagiarism_compilatio")
                . ' : ' . $counttoolong . '</br>';
            };

            $countnotfound = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_NOT_FOUND));
            if ($countnotfound > 0) {
                $result["errors"] .= '- ' . get_string("stats_notfound", "plagiarism_compilatio")
                . ' : ' . $countnotfound . '</br>';
            };

            $countfailed = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_FAILED));
            if ($countfailed > 0) {
                $result["errors"] .= '- ' . get_string("stats_failed", "plagiarism_compilatio") . ' : ' . $countfailed . '</br>';
            };
        } else {
            $sql = "SELECT COUNT(DISTINCT id) FROM {plagiarism_compilatio_files} WHERE cm=? AND statuscode=?";
            $result["errors_unsupported"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_UNSUPPORTED));
            $result["errors_unextractable"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_UNEXTRACTABLE));
            $result["errors_tooshort"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_TOO_SHORT));
            $result["errors_toolong"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_TOO_LONG));
            $result["errors_notfound"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_NOT_FOUND));
            $result["errors_failed"] = $DB->count_records_sql($sql, array($row->cm, COMPILATIO_STATUSCODE_FAILED));
        }

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
function compilatio_handle_content($content, $userid, $courseid, $cmid, $postid = null) {

    if (trim($content) == "") {
        return;
    }

    $nbmotsmin = get_config('plagiarism_compilatio', 'nb_mots_min');

    if (str_word_count(utf8_decode(strip_tags($content))) >= $nbmotsmin) {
        $data = new stdClass();
        $data->courseid = $courseid;
        $data->content = $content;
        $data->userid = $userid;
        $data->postid = $postid;

        $plagiarismsettings = (array) get_config('plagiarism_compilatio');

        $file = compilatio_create_temp_file($cmid, $data);

        compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings);
    }
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
function compilatio_handle_hashes($hashes, $cmid, $userid, $postid = null) {

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');

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
 * compilatio_course_delete
 *
 * Remove Compilatio files of a given course for all course module or for given module type.
 *
 * @param int      $courseid
 * @param string   $modulename
 */
function compilatio_course_delete($courseid, $modulename = null) {

    global $DB;

    $duplicates = array();

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
        $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $coursemodule->cm));
        compilatio_remove_duplicates($duplicates);
    }
}

/**
 * Event handler
 * @param  array $eventdata  Event data
 * @param  bool  $hasfile    There is a file ?
 * @param  bool  $hascontent There is a content ?
 * @return mixed             Return null if plugin is not enabled, void otherwise
 */
function compilatio_event_handler($eventdata, $hasfile = true, $hascontent = true) {

    $cmid = $eventdata["contextinstanceid"];

    if ($eventdata['objecttable'] == 'quiz_attempts' && $eventdata['action'] == 'submitted') {
        $attemptid = $eventdata['objectid'];
        compilatio_handle_quiz_attempt($attemptid);
        return;
    }

    if ($eventdata['crud'] != 'u' && $eventdata['crud'] != 'd'
        && $eventdata['component'] != 'tool_recyclebin') {
        if (!compilatio_enabled($cmid)) {
            return;
        }
    }

    global $CFG, $DB, $SESSION;
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
            if (!isset($SESSION->compilatio_bin_created)) {
                $filename = 'post-' . $eventdata['courseid'] . '-' . $cmid . '-' . $eventdata['objectid'] . '.htm';
                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE filename like ? AND recyclebinid IS NULL";
                $posts = $DB->get_records_sql($sql, array($filename));

                $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename like ? AND recyclebinid IS NULL";
                $attachments = $DB->get_records_sql($sql, array($cmid, 'post-' . $eventdata['objectid'] . '-%'));

                $duplicates = array_merge($posts, $attachments);
            }
        }

        // In workshops.
        if ($eventdata['objecttable'] == 'workshop_submissions') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
        }

        // In quiz.
        if ($eventdata['objecttable'] == 'quiz_attempts') {
            $filename = "quiz-" . $eventdata['courseid'] . "-" . $cmid . "-" . $eventdata['objectid'] . ".htm";
            $duplicates = $DB->get_records('plagiarism_compilatio_files',
                array('cm' => $cmid, 'userid' => $userid, 'filename' => $filename));
            compilatio_remove_duplicates($duplicates);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'quiz-%'";
            $duplicates = $DB->get_records_sql($sql, array($cmid, $userid));
        }

        // Course module delete.
        if ($eventdata['objecttable'] == 'course_modules') {
            if (class_exists('\tool_recyclebin\course_bin') && \tool_recyclebin\course_bin::is_enabled()) {
                $DB->set_field('plagiarism_compilatio_files', 'recyclebinid',
                    $SESSION->compilatio_bin_created, array('cm' => $cmid));
                unset($SESSION->compilatio_bin_created);
            } else {
                $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid));
            }
        }

        // Recycle_bin item deleted.
        if ($eventdata['objecttable'] == 'tool_recyclebin_course' ||
            $eventdata['objecttable'] == 'tool_recyclebin_category') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('recyclebinid' => $eventdata['objectid']));
        }

        // User delete.
        if ($eventdata['objecttable'] == 'user') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('userid' => $eventdata['objectid']));
        }

        // Course reset.
        if ($eventdata['target'] == 'course_reset') {
            $options = $eventdata['other']['reset_options'];

            $modules = [
                'assign' => "reset_assign_submissions",
                'quiz' => "reset_quiz_attempts",
                'workshop' => "reset_workshop_submissions",
                'forum' => "reset_forum_all",
            ];

            foreach ($modules as $modulename => $option) {
                if (isset($options[$option]) && $options[$option] == 1) {
                    compilatio_course_delete($eventdata['courseid'], $modulename);
                }
            }
        }
        compilatio_remove_duplicates($duplicates);
    }

    // Update events.
    if ($eventdata['crud'] == 'u') {
        // Restored recycle_bin.
        if ($eventdata['objecttable'] == 'tool_recyclebin_course' ||
            $eventdata['objecttable'] == 'tool_recyclebin_category') {

            // Update 'filename' for restored forum posts.
            $posts = $DB->get_records_sql('SELECT * FROM {plagiarism_compilatio_files} WHERE recyclebinid = ?
                AND filename LIKE \'post%\'', array($eventdata['objectid']));

            $cmids = array();

            foreach ($posts as $post) {
                $restoredpost = $DB->get_record('plagiarism_compilatio_files',
                    array('filename' => $post->filename, 'recyclebinid' => null));

                if (!isset($courseid)) {
                    $courseid = $DB->get_record('course_modules', array('id' => $restoredpost->cm), 'course')->course;
                }

                if (preg_match('~^post-\d+-\d+-\d+.htm$~', $restoredpost->filename)) {
                    if (!in_array($restoredpost->cm, $cmids)) {
                        array_push($cmids, $restoredpost->cm);
                    }
                } else {
                    $moodlefile = $DB->get_record('files',
                        array('filename' => $restoredpost->filename, 'filearea' => 'attachment'));

                    $filename = substr($restoredpost->filename, strpos($restoredpost->filename, '-') + 1);
                    $filename = substr($filename, strpos($filename, '-'));
                    $filename = 'post-' . $moodlefile->itemid . $filename;

                    $DB->set_field('files', 'filename', $filename, array('filename' => $restoredpost->filename));
                    $DB->set_field('plagiarism_compilatio_files', 'filename', $filename, array('id' => $restoredpost->id));
                }
            }

            foreach ($cmids as $cmid) {
                $sql = '
                    SELECT forum_posts.id, forum_posts.message
                    FROM {course_modules} course_modules
                    JOIN {forum} forum ON course_modules.instance= forum.id AND course_modules.module= 9
                    JOIN {forum_discussions} forum_discussions ON forum.id= forum_discussions.forum
                    JOIN {forum_posts} forum_posts ON forum_discussions.id= forum_posts.discussion
                    WHERE course_modules.id = ?';
                $posts = $DB->get_records_sql($sql, array($cmid));

                foreach ($posts as $post) {
                    $filename = 'post-' . $courseid . '-' . $cmid . '-' . $post->id . '.htm';
                    $DB->set_field('plagiarism_compilatio_files', 'filename', $filename,
                        array('identifier' => sha1($post->message), 'cm' => $cmid));
                }
            }

            $DB->delete_records('plagiarism_compilatio_files', array('recyclebinid' => $eventdata['objectid']));
        }

        // Delete in assign.
        if ($eventdata['target'] == 'submission_status' && $eventdata['other']['newstatus'] != 'draft') {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
            compilatio_remove_duplicates($duplicates);
        }

        // Re-submit file when student submit a draft submission.
        $plugincm = compilatio_cm_use($cmid);
        if ($eventdata['target'] == 'assessable' && $plugincm['compi_student_analyses'] === '1') {

            $plagiarismfiles = $DB->get_records('plagiarism_compilatio_files', array('cm' => $cmid, 'userid' => $userid));
            compilatio_remove_duplicates($plagiarismfiles, false);

            foreach ($plagiarismfiles as $pf) {
                $pf->externalid = null;
                $pf->reporturl = null;
                $pf->statuscode = 'pending';
                $pf->similarityscore = 0;
                $pf->attempt = 0;
                $pf->errorresponse = null;
                $pf->recyclebinid = null;
                $pf->apiconfigid = 0;
                $pf->idcourt = null;
                $pf->timesubmitted = time();

                $DB->update_record('plagiarism_compilatio_files', $pf);
            }
        }
    }

    // Creation events.
    if ($eventdata['crud'] == 'c') {
        // Course module recycle_bin created.
        if ($eventdata['objecttable'] == 'tool_recyclebin_course') {
            $SESSION->compilatio_bin_created = $eventdata['objectid'];
        }
        // Course recycle_bin created.
        if ($eventdata['objecttable'] == 'tool_recyclebin_category') {
            $sql = '
                SELECT plagiarism_compilatio_files.id
                FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
                JOIN {course_modules} course_modules
                    ON plagiarism_compilatio_files.cm = course_modules.id
                WHERE course_modules.course ='. $SESSION->compilatio_course_deleted_id;
            $files = $DB->get_records_sql($sql);
            foreach ($files as $file) {
                $DB->set_field('plagiarism_compilatio_files', 'recyclebinid', $eventdata['objectid'], array('id' => $file->id));
            }
            unset($SESSION->compilatio_course_deleted_id);
        }
    }

    // Adding/updating a file.
    if ($hasfile) {
        $hashes = $eventdata["other"]["pathnamehashes"];

        $compifilestokeep = array();

        $fs = get_file_storage();

        if ($eventdata['objecttable'] == 'assign_submission') {
            $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                'submission_files', $eventdata["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'content-%'";
            $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid, $userid));
        }

        if ($eventdata['objecttable'] == 'forum_posts') {
            $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                'attachment', $eventdata["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND filename LIKE ?";
            $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid, 'post-' . $eventdata['objectid'] . '-%'));
        }

        if ($eventdata['objecttable'] == 'workshop_submissions') {
            $mdlsubmissionfiles = $fs->get_area_files($eventdata["contextid"], $eventdata["component"],
                'submission_attachment', $eventdata["objectid"]);

            $sql = "SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND userid = ? AND filename NOT LIKE 'content-%'";
            $allcompisubmissionfiles = $DB->get_records_sql($sql, array($cmid, $userid));
        }

        foreach ($mdlsubmissionfiles as $submissionfile) {
            $file = $DB->get_record('plagiarism_compilatio_files',
                array('cm' => $cmid, 'userid' => $userid, 'identifier' => $submissionfile->get_contenthash()));
            if ($file) {
                array_push($compifilestokeep, $file);
            }
        }

        $duplicates = array_udiff($allcompisubmissionfiles, $compifilestokeep,
            function ($filea, $fileb) {
                return $filea->id - $fileb->id;
            }
        );

        compilatio_remove_duplicates($duplicates);
        compilatio_handle_hashes($hashes, $cmid, $userid, $postid);
    }

    // Adding/updating a text content.
    if ($hascontent) {
        $content = $eventdata["other"]["content"];
        $courseid = $eventdata["courseid"];
        if ($eventdata['objecttable'] == 'forum_posts') {
            $filename = 'post-' . $eventdata['courseid'] . '-' . $cmid . '-' . $eventdata['objectid'] . '.htm';
            $mdltext = $DB->get_record('forum_posts', array('id' => $eventdata["objectid"]));
            $compifile = $DB->get_record('plagiarism_compilatio_files',
                array('filename' => $filename, 'identifier' => sha1($mdltext->message)));
        } else if ($eventdata['objecttable'] == 'workshop_submissions') {
            $filename = "content-" . $eventdata['courseid'] . "-" . $cmid . "-" . $userid . ".htm";
            $mdltext = $DB->get_record('workshop_submissions', array('id' => $eventdata["objectid"]));
            $compifile = $DB->get_record('plagiarism_compilatio_files',
                array('filename' => $filename, 'identifier' => sha1($mdltext->content)));
        } else if ($eventdata['objecttable'] == 'assign_submission') {
            $filename = "content-" . $eventdata['courseid'] . "-" . $cmid . "-" . $userid . ".htm";
            $compifile = false;
        }

        if (!$compifile) {
            $duplicates = $DB->get_records('plagiarism_compilatio_files', array('filename' => $filename));
            compilatio_remove_duplicates($duplicates);
            compilatio_handle_content($content, $userid, $courseid, $cmid, $postid);
        }
    }
}

/**
 * Function to handle Quiz attempts.
 *
 * @param int $attemptid - quiz attempt id
 */
function compilatio_handle_quiz_attempt($attemptid) {

    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $plagiarismsettings = (array) get_config('plagiarism_compilatio');
    $fs = get_file_storage();

    $attempt = \quiz_attempt::create($attemptid);
    $userid = $attempt->get_userid();
    $cmid = $attempt->get_cmid();

    foreach ($attempt->get_slots() as $slot) {
        $answer = $attempt->get_question_attempt($slot);
        if ($answer->get_question()->get_type_name() == 'essay') {

            // Check for duplicates files.
            $identifier = sha1($answer->get_response_summary());
            $duplicate = $DB->get_records('plagiarism_compilatio_files',
                array('identifier' => $identifier, 'userid' => $userid, 'cm' => $cmid));
            compilatio_remove_duplicates($duplicate);

            // Online text content.
            $nbmotsmin = get_config('plagiarism_compilatio', 'nb_mots_min');
            if (str_word_count(utf8_decode(strip_tags($answer->get_response_summary()))) >= $nbmotsmin) {

                $data = new stdClass();
                $data->courseid = $attempt->get_courseid();
                $data->content = $answer->get_response_summary();
                $data->userid = $userid;
                $data->attemptid = $attemptid;
                $data->question = "Q" . $answer->get_question_id();
                $file = compilatio_create_temp_file($cmid, $data);

                compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings);
            }

            // Files attachments.
            $context = context_module::instance($cmid);
            $files = $answer->get_last_qt_files('attachments', $context->id);
            foreach ($files as $file) {

                // Check for duplicate files.
                $sql = "SELECT * FROM {plagiarism_compilatio_files}
                    WHERE cm = ? AND userid = ? AND identifier = ?";
                $duplicates = $DB->get_records_sql($sql, array($cmid, $userid, $file->get_contenthash()));
                compilatio_remove_duplicates($duplicates);

                compilatio_queue_file($cmid, $userid, $file, $plagiarismsettings);
            }
        }
    }
}

/**
 * Check allowed max file size
 *
 * @param  object $file File object
 * @return bool         Return true if the size is supported, false otherwise.
 */
function compilatio_check_allowed_file_max_size($file) {

    $allowedsize = json_decode(get_config('plagiarism_compilatio', 'file_max_size'))->octets;

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

    $allowedfiletypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));

    foreach ($allowedfiletypes as $allowedfiletype) {
        if ($allowedfiletype->type == $ext) {
            return $allowedfiletype->mimetype;
        }
    }
    return '';
}

/**
 * Get or create a compilatio service.
 *
 * @param  int  $apiconfigid Identifier of the API configuration
 * @return compilatioservice  Return compilatioservice
 */
function compilatio_get_compilatio_service($apiconfigid) {

    global $CFG;

    return compilatioservice::getinstance($apiconfigid,
        $CFG->proxyhost,
        $CFG->proxyport,
        $CFG->proxyuser,
        $CFG->proxypassword);
}

/**
 * Check if a submission can be analyzed by student.
 *
 * @param  int  $studentanalysesparam Value of the parameter compi_student_analyses for the cm
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

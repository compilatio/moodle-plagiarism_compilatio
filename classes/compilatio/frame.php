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
 * frame.php - Contains method to get Compilatio Frame.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');

/**
 * CompilatioFrame class
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CompilatioFrame {

    /**
     * Display compilatio frame
     * @return string Return the HTML formatted string.
     */
    public static function get_frame() {

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

        $compilatioenabled = $plagiarismsettings['enabled'] && $plagiarismsettings['enable_mod_' . $module];

        $compilatioactivated = $DB->get_field('plagiarism_compilatio_module', 'activated', ['cmid' => $cmid]);

        if ($compilatioactivated != 1 || !$compilatioenabled) {
            return;
        }

        $export = optional_param('compilatio_export', '', PARAM_BOOL);
        if ($export) {
            csv_helper::generate_cm_csv($cmid, $module);
        }

        // Store plagiarismfiles in $SESSION.
        $sql = 'cm = ? AND externalid IS NOT null';
        $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, [$cmid]);
        $filesids = array_keys($SESSION->compilatio_plagiarismfiles);

        $alerts = [];

        if (isset($SESSION->compilatio_alert)) {
            $alerts[] = $SESSION->compilatio_alert;
            unset($SESSION->compilatio_alert);
        }

        $startallanalyses = $sendalldocs = $resetdocsinerror = false;

        $analysistype = $DB->get_field('plagiarism_compilatio_module', 'analysistype', ['cmid' => $cmid]);

        if ($analysistype == 'manual' && $DB->count_records('plagiarism_compilatio_files', ['status' => 'sent', 'cm' => $cmid]) !== 0) {
            $startallanalyses = true;

        } else if ($analysistype == 'planned') { // Display the date of analysis if its type is set on 'Planned'.
            $analysistime = $DB->get_field('plagiarism_compilatio_module', 'analysistime', ['cmid' => $cmid]);
            $date = userdate($analysistime);
            if ($analysistime > time()) {
                $analysisdate = get_string('programmed_analysis_future', 'plagiarism_compilatio', $date);
            } else {
                $analysisdate = get_string('programmed_analysis_past', 'plagiarism_compilatio', $date);
            }
        }

        // Get webservice status :.
        $webservicestatus = get_config('plagiarism_compilatio', 'connection_webservice');
        // If the record exists and if the webservice is marked as unreachable in Cron function :.
        if ($webservicestatus != null && $webservicestatus === '0') {
            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('webservice_unreachable', 'plagiarism_compilatio')
            ];
        }

        // Display reset docs in error button if necesseary.
        $sql = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_files} pcf
            WHERE pcf.cm=? AND (status = 'error_analysis_failed' OR status = 'error_sending_failed')";
        if ($DB->count_records_sql($sql, [$cmid]) !== 0) {
            $resetdocsinerror = true;
        }

        // Check for unsend documents.
        if ($module == 'assign') {
            $countunsend = count(compilatio_get_unsent_documents($cmid));

            if ($countunsend !== 0) {
                $alerts[] = [
                    'class' => 'danger',
                    'content' => get_string('unsent_documents', 'plagiarism_compilatio')
                ];
                $sendalldocs = true;
            }
        } else {
            $countunsend = 0;
        }

        $compilatio = new CompilatioAPI(get_config('plagiarism_compilatio', 'apikey'));

        foreach ($compilatio->get_alerts() as $alert) {
            $language = substr(current_language(), 0, 2);
            $translation = $compilatio->get_translation($language, $alert->text);

            if (empty($translation)) {
                $text = $alert->text;
            } else {
                $text = $translation;
            }

            if (time() > strtotime($alert->activation_period->start) && time() < strtotime($alert->activation_period->end)) {
                $alerts[] = [
                    'class' => 'info',
                    'content' => $text,
                ];
            }
        }

        $user = $DB->get_record('plagiarism_compilatio_user', ['userid' => $USER->id]);

        if (!empty($user)) {
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'checkUserInfo', [$CFG->httpswwwroot, $user->compilatioid]);

            if ($user->validatedtermsofservice == 0) {
                $lang = substr(current_language(), 0, 2);

                $termsofservice = 'https://app.compilatio.net/api/private/terms-of-service/magister/' . $lang;

                $alerts[] = "
                    <div class='cmp-alert cmp-alert-danger'>
                        <div>
                            <p>" . get_string('terms_of_service_alert', 'plagiarism_compilatio', $termsofservice) . "<p>
                            <input id='tos-btn' class='btn btn-primary' type='submit'
                                value=\"" . get_string('terms_of_service_alert_btn', 'plagiarism_compilatio') . "\">
                        </div>
                    </div>";
                    $PAGE->requires->js_call_amd(
                        'plagiarism_compilatio/compilatio_ajax_api',
                        'validateTermsOfService',
                        [$CFG->httpswwwroot, $user->compilatioid]
                    );
            }
        }

        $output = '';
        $output .= "<div id='cmp-container'>";

        // Display the tabs: Notification tab will be hidden if there is 0 alerts.
        $output .= "<div id='cmp-tabs' style='display:none'>";

        // Display logo.
        $output .= "<img id='cmp-logo' src='" . new moodle_url('/plagiarism/compilatio/pix/compilatio.png') . "'>";

        // Help icon.
        $output .= "<div title='" . get_string('compilatio_help_assign', 'plagiarism_compilatio') .
            "' id='show-help' class='cmp-icon'><i class='fa fa-question-circle fa-2x'></i></div>";

        // Stat icon.
        $output .= "<div id='show-stats' class='cmp-icon'  title='" .
            get_string('display_stats', 'plagiarism_compilatio') . "'><i class='fa fa-bar-chart fa-2x'></i></div>";

        // Alert icon.
        if (count($alerts) !== 0) {
            $output .= "<div id='cmp-show-notifications' title='";
            $output .= get_string('display_notifications', 'plagiarism_compilatio');
            $output .= "' class='cmp-icon active' ><i class='fa fa-bell fa-2x'></i>";
            $output .= "<span id='cmp-count-alerts' class='cmp-badge cmp-badge-danger'>" . count($alerts) . "</span></div>";
        }

        if ($plagiarismsettings['enable_search_tab']) {
            // Search icon.
            $output .= "<div title='" . get_string('compilatio_search_tab', 'plagiarism_compilatio') .
                "' id='show-search' class='cmp-icon'><i class='fa fa-search fa-2x'></i></div>";
        }

        // Hide/Show button.
        $output .= "
            <div id='cmp-hide-area' class='cmp-icon'  title='" .
        get_string('hide_area', 'plagiarism_compilatio') . "'>
                <i class='fa fa-chevron-up fa-2x'></i>
            </div>";

        // TODO factoriser cette merde.
        // Display buttons.
        if (has_capability('plagiarism/compilatio:triggeranalysis', $PAGE->context)) {
            if ($startallanalyses) {
                $output .= "<button class='cmp-btn-lg cmp-btn-primary cmp-start-btn' >
                        " . get_string('start_all_analysis', 'plagiarism_compilatio') . "
                        <i class='cmp-icon-lg cmp-ml-10 fa fa-play-circle'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAllAnalysis',
                    [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio')]);
            }

            if ($sendalldocs) {
                $output .= "<button class='cmp-btn-lg cmp-btn-primary cmp-send-btn' >
                        " . get_string('send_all_documents', 'plagiarism_compilatio') . "
                        <i class='cmp-icon-lg cmp-ml-10 fa fa-play-circle'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'sendUnsentDocs',
                    [$CFG->httpswwwroot, $cmid, get_string('send_documents_in_progress', 'plagiarism_compilatio')]);
            }

            if ($resetdocsinerror) {
                $output .= "<button class='cmp-btn-lg cmp-btn-primary cmp-reset-btn' >
                        " . get_string('reset_docs_in_error', 'plagiarism_compilatio') . "
                        <i class='cmp-icon-lg cmp-ml-10 fa fa-rotate-right'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'resetDocsInError',
                    [$CFG->httpswwwroot, $cmid, get_string('reset_docs_in_error_in_progress', 'plagiarism_compilatio')]);
            }
        }

        $output .= "</div>";

        $output .= "<div id='cmp-tabs-separator'></div>";

        // Home tab.
        $output .= "<div id='cmp-home' class='cmp-tabs-content'>
                        <p>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>";
        if ($module == 'quiz') {
            $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
            $output .= "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>";
        }
        $output .= "</div>";

        // Help tab.
        $output .= "<div id='cmp-help' class='cmp-tabs-content'>";

        if (empty($plagiarismsettings['apikey']) || empty($user)) {
            $output .= "<p>" . get_string('helpcenter_error', 'plagiarism_compilatio')
                . "<a href='https://support.compilatio.net/'>https://support.compilatio.net</a></p>";
        } else {
            $output .= "<p><a href='../../plagiarism/compilatio/helpcenter.php?userid=" . $user->compilatioid . "'
            target='_blank' >" . get_string('helpcenter', 'plagiarism_compilatio') . "
            <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='-5 -11 24 24'>
                <path fill='none' stroke='#555' stroke-linecap='round'
                stroke-linejoin='round' d='M8 2h4v4m0-4L6 8M4 2H2v10h10v-2'></path>
            </svg></a></p>";
        }

        $output .= "
                <p><a href='http://etat-services.compilatio.net/?lang=FR'
                    target='_blank' >" . get_string('goto_compilatio_service_status', 'plagiarism_compilatio') . "
                    <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='-5 -11 24 24'>
                        <path fill='none' stroke='#555' stroke-linecap='round'
                        stroke-linejoin='round' d='M8 2h4v4m0-4L6 8M4 2H2v10h10v-2'></path>
                    </svg></a></p>
            </div>";

        // Stats tab.
        $output .= "
            <div id='cmp-stats' class='cmp-tabs-content text-center'>
                <div class='row'>"
                . CompilatioStatistics::get_statistics($cmid) .
                "</div>
            </div>";

        // Alerts tab.
        if (count($alerts) !== 0) {
            $output .= "<div id='cmp-notifications' class='cmp-tabs-content'>";

            foreach ($alerts as $alert) {
                if (isset($alert['content'])) {
                    switch ($alert['class']) {
                        case 'info':
                            $icon = 'fa-bell';
                            break;
                        case 'warning':
                            $icon = 'fa-exclamation-circle';
                            break;
                        case 'danger':
                            $icon = 'fa-exclamation-triangle';
                            break;
                        case 'success':
                            $icon = 'fa-check-circle';
                            break;
                    }

                    $output .= "
                        <div class='cmp-alert cmp-alert-" . $alert['class'] . "'>
                        <i class='cmp-alert-icon fa-lg fa " . $icon . "'></i>" . $alert['content'] .
                        "</div>";
                } else {
                    $output .= $alert;
                }
            }

            $output .= "</div>";
        }

        $docid = optional_param('docId', null, PARAM_RAW);

        // Search tab.
        $output .= "<div id='cmp-search' class='cmp-tabs-content'>
            <h5>" . get_string('compilatio_search_tab', 'plagiarism_compilatio') . "</h5>
            <p>" . get_string('compilatio_search_help', 'plagiarism_compilatio') . "</p>
            <form class='form-inline' action=" . $PAGE->url . " method='post'>
                <input class='form-control m-2' type='text' id='docId' name='docId' value='" . $docid
                    . "' placeholder='" . get_string('compilatio_iddocument', 'plagiarism_compilatio') . "'>
                <input class='btn btn-primary' type='submit' value='" .get_string('compilatio_search', 'plagiarism_compilatio'). "'>
            </form>";

        if (!empty($docid)) {
            $sql = "SELECT usr.lastname, usr.firstname, cf.cm
                FROM {plagiarism_compilatio_files} cf
                JOIN {user} usr on cf.userid = usr.id
                WHERE cf.externalid = ?";
            $doc = $DB->get_record_sql($sql, [$docid]);

            if ($doc) {
                $module = get_coursemodule_from_id(null, $doc->cm);
                $doc->modulename = $module->name;
                $output .= get_string('compilatio_author', 'plagiarism_compilatio', $doc);
            } else {
                $output .= get_string('compilatio_search_notfound', 'plagiarism_compilatio');
            }
        }

        $output .= "</div>";

        // Display timed analysis date.
        if (isset($analysisdate)) {
            $output .= "<span id='cmp-planned-analyses'>$analysisdate</span>";
        }

        $output .= "</div>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'compilatioTabs', [count($alerts), $docid]);

        return $output;
    }
}

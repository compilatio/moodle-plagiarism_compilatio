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
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/csv.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/icons.php');

/**
 * CompilatioFrame class
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

        $compilatioactivated = $DB->get_field('plagiarism_compilatio_cm_cfg', 'activated', ['cmid' => $cmid]);

        if ($compilatioactivated != 1 || !$compilatioenabled) {
            return;
        }

        $export = optional_param('cmp_csv_export', '', PARAM_BOOL);
        if ($export) {
            CompilatioCsv::generate_cm_csv($cmid, $module);
        }

        // Store plagiarismfiles in $SESSION.
        $sql = 'cm = ? AND externalid IS NOT null';
        $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_file', $sql, [$cmid]);
        $filesids = array_keys($SESSION->compilatio_plagiarismfiles);

        $alerts = [];
        $notices = [];

        if (isset($SESSION->compilatio_alerts)) {
            $notices = $SESSION->compilatio_alerts;
            unset($SESSION->compilatio_alerts);
        }

        $startallanalyses = $sendalldocs = $resetdocsinerror = $multipleanalysesoptionss = false;

        $analysistype = $DB->get_field('plagiarism_compilatio_cm_cfg', 'analysistype', ['cmid' => $cmid]);

        if (
            $analysistype == 'manual'
                && $DB->count_records('plagiarism_compilatio_file', ['status' => 'sent', 'cm' => $cmid]) !== 0
        ) {
            $startallanalyses = $multipleanalysesoptionss = true;

        } else if ($analysistype == 'planned') { // Display the date of analysis if its type is set on 'Planned'.
            $analysistime = $DB->get_field('plagiarism_compilatio_cm_cfg', 'analysistime', ['cmid' => $cmid]);
            $date = userdate($analysistime);
            if ($analysistime > time()) {
                $analysisdate = get_string('programmed_analysis_future', 'plagiarism_compilatio', $date);
            } else {
                $analysisdate = get_string('programmed_analysis_past', 'plagiarism_compilatio', $date);
            }
        }

        $webservicestatus = get_config('plagiarism_compilatio', 'connection_webservice');
        if ($webservicestatus != null && $webservicestatus === '0') {
            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('webservice_unreachable', 'plagiarism_compilatio'),
            ];
        }

        if (get_config('plagiarism_compilatio', 'read_only_apikey') === '1') {
            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('read_only_apikey', 'plagiarism_compilatio'),
            ];
        }

        // Display reset docs in error button if necesseary.
        $sql = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_file} pcf
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
                    'content' => get_string('unsent_docs', 'plagiarism_compilatio'),
                ];
                $sendalldocs = true;
            }
        } else {
            $countunsend = 0;
        }

        $compilatio = new CompilatioAPI();

        foreach ($compilatio->get_alerts() as $alert) {
            $language = substr(current_language(), 0, 2);
            $translation = $compilatio->get_translation($language, $alert->text);

            if (empty($translation)) {
                $text = $alert->text;
            } else {
                $text = $translation;
            }

            if ($text === 'DONT_DISPLAY') {
                continue;
            }

            if (time() > strtotime($alert->activation_period->start) && time() < strtotime($alert->activation_period->end)) {
                $alerts[] = [
                    'class' => 'info',
                    'content' => "<span class='cmp-md'>" . $text . '</span>',
                ];
            }
        }

        $user = $DB->get_record('plagiarism_compilatio_user', ['userid' => $USER->id]);

        if (!empty($user)) {
            $PAGE->requires->js_call_amd(
                'plagiarism_compilatio/compilatio_ajax_api',
                'checkUserInfo',
                [$CFG->httpswwwroot, $user->compilatioid]
            );

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

        $output =
            "<div id='cmp-display-frame' style='display:none;' title='" . get_string('show_area', 'plagiarism_compilatio') . "'>
                <img src='" . new moodle_url("/plagiarism/compilatio/pix/c.svg") . "'>
                <i class='cmp-icon ml-2 fa-2x fa fa-bars'></i>
                " . CompilatioIcons::bell() . "
            </div>";

        $output .= "<div id='cmp-container'>";

        // Display the tabs.
        $output .= "<div id='cmp-tabs'>";

        // Display logo.
        $output .= "<img id='cmp-logo' src='" . new moodle_url('/plagiarism/compilatio/pix/compilatio.png') . "'>";

        // Help icon.
        $output .= "<i id='show-help' title='" . get_string('compilatio_help_assign', 'plagiarism_compilatio') .
            "' class='cmp-icon fa fa-question-circle'></i>";

        // Stat icon.
        $output .=
            "<i
                id='show-stats'
                title='" . get_string('display_stats', 'plagiarism_compilatio') . "'
                class='cmp-icon fa fa-bar-chart'
            >
            </i>";

        if ($plagiarismsettings['enable_search_tab']) {
            // Search icon.
            $output .= "<i id='show-search' title='" . get_string('compilatio_search_tab', 'plagiarism_compilatio') .
                "' class='cmp-icon fa fa-search fa-2x'></i>";
        }

        // Alert icon.
        if (count($alerts) !== 0) {
            $output .= "<span>
                <i
                    id='cmp-show-notifications'
                    title='" . get_string("display_notifications", "plagiarism_compilatio") . "'
                    class='cmp-icon fa fa-bell'
                >
                </i>
                <span id='cmp-count-alerts' class='badge badge-pill badge-danger'>" . count($alerts) . "</span>
            </span>";
        }

        // Display buttons.
        if (has_capability('plagiarism/compilatio:triggeranalysis', $PAGE->context)) {
            if ($startallanalyses) {
                $output .=
                    "<button
                        id='cmp-start-btn'
                        title='" . get_string('start_all_analysis', 'plagiarism_compilatio') . "'
                        class='btn btn-primary cmp-action-btn mx-1 '
                        data-toggle='tooltip'
                    >
                        <i class='fa fa-play-circle'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAllAnalysis',
                    [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio')]);
            }

            if ($multipleanalysesoptionss) {
                $output .="
                    <button 
                        id='show-multiple-analyse-options' 
                        class='btn btn-secondary cmp-action-btn mx-1' 
                        data-toggle='tooltip'
                        title='" . get_string('other_analysis_options', 'plagiarism_compilatio') . "'
                    >
                        <i  class='fa fa-chevron-down'></i>
                    </button>
                    ";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysesOnSelectedFiles',
                [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio')]);
            }

            if ($sendalldocs) {
                $output .=
                    "<button
                        id='cmp-send-btn'
                        title='" . get_string('send_all_documents', 'plagiarism_compilatio') . "'
                        class='btn btn-primary cmp-action-btn mx-1'
                    >
                        <i class='cmp-icon-lg fa fa-paper-plane'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'sendUnsentDocs',
                    [$CFG->httpswwwroot, $cmid, get_string('send_documents_in_progress', 'plagiarism_compilatio')]);
            }

            if ($resetdocsinerror) {
                $output .=
                    "<button
                        id='cmp-reset-btn'
                        title='" . get_string('reset_docs_in_error', 'plagiarism_compilatio') . "'
                        class='btn btn-primary cmp-action-btn mx-1'
                    >
                        <i class='cmp-icon-lg fa fa-rotate-right'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'resetDocsInError',
                    [$CFG->httpswwwroot, $cmid, get_string('reset_docs_in_error_in_progress', 'plagiarism_compilatio')]);
            }
        }

        $output .=
            "<i
                id='cmp-hide-frame'
                title='" . get_string('hide_area', 'plagiarism_compilatio') . "'
                class='cmp-icon pl-3 fa fa-bars'
            >
            </i>";

        $output .= "</div>";

        // Home tab.
        $output .= "<div id='cmp-home'></div>";

        // Help tab.
        $output .= "<div id='cmp-help' class='cmp-tabs-content'>
            <p>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>";

        if ($module == 'quiz') {
            $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
            $output .= "<p><b>" . get_string('quiz_help', 'plagiarism_compilatio', $nbmotsmin) . "</b></p>";
        }

        if (empty($plagiarismsettings['apikey']) || empty($user)) {
            $output .= "<p>" . get_string('helpcenter_error', 'plagiarism_compilatio')
                . "<a href='https://support.compilatio.net/'>https://support.compilatio.net</a></p>";
        } else {
            $output .= "<p>
                <a href='../../plagiarism/compilatio/helpcenter.php?userid=" . $user->compilatioid . "' target='_blank' >"
                    . get_string('helpcenter', 'plagiarism_compilatio') .
                    "<i class='ml-2 fa fa-external-link'></i>
                </a>
            </p>";
        }

        $output .= "<p>
            <a href='../../plagiarism/compilatio/helpcenter.php?page=service_status' target='_blank' >"
                . get_string('goto_compilatio_service_status', 'plagiarism_compilatio') .
                "<i class='ml-2 fa fa-external-link'></i>
            </a>
            </p></div>";

        // Stats tab.
        $url = $PAGE->url;
        $url->param('cmp_csv_export', true);
        $exportbutton = "<a title='" . get_string("export_csv", "plagiarism_compilatio") . "' class='cmp-icon pr-3' href='$url'>
                <i class='fa fa-download'></i>
            </a>";

        $output .= "
            <div id='cmp-stats' class='cmp-tabs-content'>
                <div class='row text-center'>"
                . CompilatioStatistics::get_statistics($cmid) . $exportbutton .
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
                FROM {plagiarism_compilatio_file} cf
                JOIN {user} usr on cf.userid = usr.id
                WHERE cf.externalid = ?";
            $doc = $DB->get_record_sql($sql, [$docid]);

            if ($doc) {
                $module = get_coursemodule_from_id(null, $doc->cm);
                $doc->modulename = $module->name;
                $output .= get_string('compilatio_depositor', 'plagiarism_compilatio', $doc);
            } else {
                $output .= get_string('compilatio_search_notfound', 'plagiarism_compilatio');
            }
        }

        $output .= "</div>";

        $output .= "<div id='cmp-notices'>";
        foreach ($notices as $notice) {
            $output .= "<div class='d-flex cmp-alert cmp-alert-" . $notice['class'] . "'>"
                . $notice['content'] . "<i class='ml-auto my-auto fa fa-times'></i>
            </div>";
        }
        $output .= "</div>";

        // Display multiple analysis options
        $output .= "<div id='cmp-multiple-analyse-options' class='cmp-tabs-content'>
                <h5>" . get_string('other_analysis_options', 'plagiarism_compilatio') . "</h5>
                <div class='ml-2'>
                    <div class='row mt-3'>
                        <button
                            id='cmp-start-btn'
                            class='btn btn-primary cmp-action-btn mx-1'
                        >
                            <i class='fa fa-play-circle'></i>
                        </button>
                        <h7 class='mt-2'>" . get_string('start_all_analysis', 'plagiarism_compilatio') . "</h7>
                    </div>
                    <div class='row'>
                        <button
                            id='cmp-start-selected-btn'
                            class='btn btn-primary cmp-action-btn mx-1 mt-1'
                        >
                            <i class='fa fa-check-double'></i>
                        </button>
                        <h7 class='mt-2'>" . get_string('start_selected_files_analysis', 'plagiarism_compilatio') . "</h7>
                    </div>
                </div>
            </div>";

        // Display timed analysis date.
        if (isset($analysisdate)) {
            $output .= "<span class='border-top pt-2 mt-2 text-center font-italic'>$analysisdate</span>";
        }

        $output .= "</div>";

        $output .= "<script src=" . $CFG->wwwroot . "/plagiarism/compilatio/js/drawdown.min.js></script>";
        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'compilatioTabs', [count($alerts), $docid]);

        return $output;
    }
}


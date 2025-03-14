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
 * compilatio_frame.php - Contains method to get Compilatio frame.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\output;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use mod_quiz\question\bank\qbank_helper;
use core\hook\output\before_standard_top_of_body_html_generation;
use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\compilatio\csv_generator;
use plagiarism_compilatio\output\statistics;
use plagiarism_compilatio\output\icons;
use plagiarism_compilatio\compilatio\analysis;
use moodle_url;

/**
 * compilatio_frame class
 */
class compilatio_frame {

    /**
     * Hook callback to insert a chunk of html at the start of the html document.
     * This allow us to display the Compilatio frame with statistics, alerts,
     * author search tool and buttons to launch all analyses and update submitted files status.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {

        global $SESSION;

        if (optional_param('refreshAllDocs', false, PARAM_BOOL)) {
            foreach ($SESSION->compilatio_plagiarismfiles as $file) {
                analysis::check_analysis($file);
            }
        }

        $hook->add_html(self::get_frame());
    }

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
            csv_generator::generate_cm_csv($cmid, $module);
        }

        // Store plagiarismfiles in $SESSION.
        $sql = 'cm = ? AND externalid IS NOT null';
        $SESSION->compilatio_plagiarismfiles = $DB->get_records_select('plagiarism_compilatio_files', $sql, [$cmid]);
        $filesids = array_keys($SESSION->compilatio_plagiarismfiles);

        $alerts = [];

        if (isset($SESSION->compilatio_alerts)) {
            $alerts = $SESSION->compilatio_alerts;
            unset($SESSION->compilatio_alerts);
        }

        $startallanalyses = $sendalldocs = $resetdocsinerror = false;

        $cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

        if (
            $cmconfig->analysistype == 'manual'
                && $DB->count_records('plagiarism_compilatio_files', ['status' => 'sent', 'cm' => $cmid]) !== 0
        ) {
            $startallanalyses = true;

        } else if ($cmconfig->analysistype == 'planned') { // Display the date of analysis if its type is set on 'Planned'.
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
        $sql = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_files} pcf
            WHERE pcf.cm=? AND
            (status = 'error_analysis_failed' OR status = 'error_sending_failed' OR status = 'error_extraction_failed')";
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

        $compilatio = new api();
        $language = substr(current_language(), 0, 2);

        foreach ($compilatio->get_alerts() as $alert) {
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
        }

        $output = "<div id='cmp-container'>";
        $output .= "<div class='d-flex'><div id='cmp-navbar' class='ml-auto'>";

        // Display the tabs.
        $output .= "<div id='cmp-tabs' data-toggle='tooltip'>";

        // Display logo.
        $output .= "<img id='cmp-logo' src='" . new moodle_url('/plagiarism/compilatio/pix/compilatio.png') . "'>";

        // Help icon.
        $output .= "<i id='show-help' title='" . get_string('compilatio_help_assign', 'plagiarism_compilatio') .
            "' class='cmp-icon fa fa-question-circle' data-toggle='tooltip'></i>";

        // Settings icon.
        $output .= "
            <i
                id='cmp-show-settings'
                title='" . get_string("display_settings_frame", "plagiarism_compilatio") . "'
                class='cmp-icon fa fa-cog'
                data-toggle='tooltip'
            >
            </i>";

        // Stat icon.
        $output .=
            "<i
                id='show-stats'
                title='" . get_string('display_stats', 'plagiarism_compilatio') . "'
                class='cmp-icon fa fa-bar-chart'
                data-toggle='tooltip'
            >
            </i>";

        // Stat per student quiz icon.
        if ($module == 'quiz') {
            $output .=
            "<span
                id='show-stats-per-student'
                title='" . get_string('display_stats_per_student', 'plagiarism_compilatio') . "'
                class='cmp-icon'
                data-toggle='tooltip'
            >" . icons::statistics_per_student() . "
            </span>";
        }

        if ($plagiarismsettings['enable_search_tab']) {
            // Search icon.
            $output .= "<i id='show-search' title='" . get_string('compilatio_search_tab', 'plagiarism_compilatio') .
                "' class='cmp-icon fa fa-search fa-2x' data-toggle='tooltip'></i>";
        }

        // Notification icon.
        $output .= "<span class='position-relative'>
            <i
                id='cmp-show-notifications'
                title='" . get_string("display_notifications", "plagiarism_compilatio") . "'
                class='cmp-icon fa fa-bell'
                data-toggle='tooltip'
            >
            </i>
            <span id='cmp-count-notifications' class='badge badge-pill badge-primary'></span>
        </span>";

        // Display buttons.
        if (has_capability('plagiarism/compilatio:triggeranalysis', $PAGE->context)
            && ($startallanalyses || $sendalldocs || $resetdocsinerror)) {

            $output .= "<div class='btn-group ml-auto pl-5' role='group'>";

            if ($startallanalyses) {
                $output .= self::display_start_all_analyses_button($cmid, $module);
            }

            if ($sendalldocs) {
                $output .=
                    "<button
                        data-toggle='tooltip'
                        id='cmp-send-btn'
                        title='" . get_string('send_all_documents', 'plagiarism_compilatio') . "'
                        class='btn btn-outline-primary cmp-action-btn'
                    >
                        <i class='cmp-icon-lg fa fa-paper-plane'></i>
                    </button>";

                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'sendUnsentDocs',
                    [$CFG->httpswwwroot, $cmid, get_string('send_documents_in_progress', 'plagiarism_compilatio')]);
            }

            if ($resetdocsinerror) {
                $output .=
                    "<button
                        data-toggle='tooltip'
                        id='cmp-reset-btn'
                        title='" . get_string('reset_docs_in_error', 'plagiarism_compilatio') . "'
                        class='btn btn-outline-primary cmp-action-btn'
                    >
                        <i class='cmp-icon-lg fa fa-rotate-right'></i>
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'resetDocsInError',
                    [$CFG->httpswwwroot, $cmid, get_string('reset_docs_in_error_in_progress', 'plagiarism_compilatio')]);
            }

            $output .= "</div>";
        }

        $output .= "</div>";

        // Help tab.
        $output .= "<div id='cmp-help' class='cmp-tabs-content'>
            <p>" . get_string('similarities_disclaimer', 'plagiarism_compilatio') . "</p>";

        // Elements included in subscription.
        $output .= "<p>" . get_string('element_included_in_subscription', 'plagiarism_compilatio');

        $output .= get_config('plagiarism_compilatio', 'recipe') === 'anasim-premium'
            ? "<li>" . get_string('ai_included_in_subscription', 'plagiarism_compilatio'). "</li></ul></p>"
            : "</ul>" . get_string('ai_not_included_in_subscription', 'plagiarism_compilatio'). "</p>";

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
        $exportbutton = "<a title='" . get_string("export_csv", "plagiarism_compilatio") .
            "' class='cmp-icon position-absolute' style='right: 1rem;' href='$url' data-toggle='tooltip' >
                <i class='fa fa-download'></i>
            </a>";

        $output .= "
            <div id='cmp-stats' class='cmp-tabs-content'>
                <div class='row text-center position-relative'>"
                . statistics::get_statistics($cmid) . $exportbutton .
                "</div>
            </div>";

        $output .= "
            <div id='cmp-stats-per-student' class='cmp-tabs-content'>
                <div class='text-center'>"
                . statistics::get_quiz_students_statistics($cmid) . "</div>
            </div>";

        // Notifications tab.
        $output .= "<div id='cmp-notifications' class='cmp-tabs-content'></div>";

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
                $output .= get_string('compilatio_depositor', 'plagiarism_compilatio', $doc);
            } else {
                $output .= get_string('compilatio_search_notfound', 'plagiarism_compilatio');
            }
        }

        $output .= "</div>";

        // Settings.
        $output .= "
            <div id='cmp-settings' class='cmp-tabs-content'>
               " . self::display_score_settings($cmid) . "
            </div>";

        // Display timed analysis date.
        if (isset($analysisdate)) {
            $output .= "<span class='border-top pt-2 mt-2 text-center font-italic'>$analysisdate</span>";
        }

        $output .= "</div></div>";

        // Alerts.
        $output .= "<div class='d-flex'><div id='cmp-alerts' class='ml-auto mt-1'>";

        foreach ($alerts as $index => $alert) {
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
                        <span class='mr-1 d-flex'>
                            <i class='cmp-alert-icon fa-lg fa " . $icon . "'></i>" . $alert['content'] .
                        "</span>
                        <i id='cmp-alert-" . $index . "' class='cmp-cursor-pointer ml-auto my-auto fa fa-times'></i>
                    </div>";
            } else {
                $output .= $alert;
            }
        }
        $output .= "</div></div>";

        // Close container.
        $output .= "</div>";

        $output .= "<script src=" . $CFG->wwwroot . "/plagiarism/compilatio/js/drawdown.min.js></script>";
        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'compilatioTabs', [$docid]);

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'getNotifications',
            [$CFG->httpswwwroot, $cmconfig->userid]);

        return $output;
    }

    /**
     * Build HTML for Start all aalyses button
     *
     * @param  int    $cmid   Course module ID
     * @param  string $module Module name
     * @return string HTML
     */
    private static function display_start_all_analyses_button($cmid, $module) {
        global $DB, $CFG, $PAGE;

        $output = $questionselector = '';

        $output .=
            "<button
                title='" . get_string('start_all_analysis', 'plagiarism_compilatio') . "'
                class='btn btn-primary cmp-action-btn cmp-start-btn'
                data-toggle='tooltip'
            >
                <i class='cmp-icon-lg fa fa-play-circle'></i>
            </button>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAllAnalysis',
            [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio')]);

        if ($module !== 'quiz' && $module !== 'assign') {
            return $output;
        }

        if ($module == 'quiz') {
            $sql = "SELECT {quiz}.id
                FROM {quiz}
                INNER JOIN {course_modules} ON {course_modules}.instance = {quiz}.id
                WHERE {course_modules}.id = ?";

            $quizid = $DB->get_field_sql($sql, [$cmid]);

            $modulecontext = \context_module::instance($cmid);
            $quizquestions = qbank_helper::get_question_structure($quizid, $modulecontext);

            $questionselector .= "<div class='text-center'>";

            foreach ($quizquestions as $quizquestion) {
                if ($quizquestion->qtype !== 'essay') {
                    continue;
                }

                $questionselector .= "
                    <div>
                        <input class='checkbox-question-selector' type='checkbox' id='" . $quizquestion->questionid . "'
                            value='" . $quizquestion->questionid . "'>
                            <label class='form-check-label' for='" . $quizquestion->questionid . "'>
                                " . get_string('question', 'core') . " " . $quizquestion->slot . "
                            </label>
                    </div>";
            }

            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysesOnSelectedQuestions',
                [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio'), $quizid]);

            $questionselector .= "</div>";
            $questionselector .= "
                <div
                    class='cmp-action-btn p-2'
                    role='button'
                    id='start-selected-questions-btn'
                >
                    <div class='text-nowrap'>" . get_string('start_selected_questions_analysis', 'plagiarism_compilatio') . "</div>
                </div>";
        }

        $output .= "
            <div
                class='dropdown btn btn-outline-primary cmp-action-btn'
                role='button'
                data-toggle='dropdown'
                title='" . get_string('other_analysis_options', 'plagiarism_compilatio') . "'
            >
                <i class='cmp-icon-lg fa fa-ellipsis-v'></i>
                <div id='cmp-dropdown-menu' class='dropdown-menu overflow-hidden p-0' aria-labelledby='dropdownMenuButton'>
                    <div
                        class='cmp-action-btn p-2 cmp-start-btn'
                        role='button'
                    >
                        <div class='text-nowrap'>" . get_string('start_all_analysis', 'plagiarism_compilatio') . "</div>
                    </div>
                    <div
                        class='cmp-action-btn p-2'
                        role='button'
                        id='start-selected-students-btn'
                    >
                        <div class='text-nowrap'>" . get_string('start_selected_files_analysis', 'plagiarism_compilatio') . "</div>
                    </div>
                    " . $questionselector . "
                </div>
            </div>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysesOnSelectedStudents',
            [$CFG->httpswwwroot, $cmid, get_string('start_analysis_in_progress', 'plagiarism_compilatio')]);

        return $output;
    }

    /**
     * Build HTML for score settings
     *
     * @param  int $cmid Course module ID
     * @return string HTML
     */
    private static function display_score_settings($cmid) {
        global $DB, $PAGE, $CFG;

        $cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

        $ignoredscores = $cmconfig->ignoredscores;
        $ignoredscores = $ignoredscores == '' ? [] : explode(',', $ignoredscores);

        $scores = ['simscore', 'utlscore'];

        $recipe = get_config('plagiarism_compilatio', 'recipe');
        $recipe === 'anasim-premium' ? array_push($scores, 'aiscore') : null;

        $output = get_string('include_percentage_in_suspect_text', 'plagiarism_compilatio');

        foreach ($scores as $score) {
            $output .= "
                <div class='form-check mt-2 mr-1'>
                    <input class='checkbox-score-settings' type='checkbox' id='" . $score . "' value='" . $score . "'
                        " . (in_array($score, $ignoredscores) ? '' : 'checked') . ">
                    <label class='form-check-label' for='" . $score . "'>
                        " . get_string($score . '_percentage', 'plagiarism_compilatio') . "
                    </label>
                </div>";
        }

        $output .= "
            <div class='mt-2'>
                <span class='font-weight-lighter font-italic mt-4'>"
                    . get_string('score_settings_info', 'plagiarism_compilatio') . "</span>
            </div>
            <div class='d-flex flex-row-reverse mr-1'>
                <button id='score-settings-ignored' type='button' class='btn btn-primary'>"
                    . get_string('update', 'core') . "</button>
            </div>";

        $PAGE->requires->js_call_amd(
            'plagiarism_compilatio/compilatio_ajax_api',
            'updateScoreSettings',
            [$CFG->httpswwwroot, $cmid, $scores]
        );

        return $output;
    }
}


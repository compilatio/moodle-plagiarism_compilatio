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
 * output_helper.php - Contains Plagiarism plugin helper methods for display graphics elements.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');

/**
 * Helper class for display elements
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_helper {

    /**
     * Get the jquery HTML <script> tag
     *
     * @return string   HTML <script> tag
     */

    /**
     * Display Compilatio's logo according to the user's language (English by default)
     * FR, IT, ES & EN are currently available
     *
     * @return string   HTML tag displaying the right image
     */
    public static function get_logo() {

        global $OUTPUT;

        $ln = current_language();

        if (!in_array($ln, array("fr", "en", "it", "es"))) {
            $language = "en";
        } else {
            $language = $ln;
        }

        return html_writer::img($OUTPUT->image_url('compilatio-logo-' . $language, 'plagiarism_compilatio'),
            'Compilatio', array('title' => 'Compilatio', 'id' => 'compilatio-logo'));
    }

    /**
     * Get the indexing state HTML tag
     *
     * @param  mixed  $indexingstate Indexing state
     * @return string                Return the HTML tag
     */
    public static function get_indexing_state($indexingstate) {

        $html = ''; // Do not show indexing state for a "non-teacher" user.

        if ($indexingstate === true) {
            $html = html_writer::div('', 'compilatio-library-in',
                array('title' => get_string("indexed_document", "plagiarism_compilatio")));
        } else if ($indexingstate === false) {
            $html = html_writer::div('', 'compilatio-library-out',
                array('title' => get_string("not_indexed_document", "plagiarism_compilatio")));
        }

        return $html;
    }


    /**
     * Display plagiarism document area
     *
     * @param string  $domid                DOM Compilatio ID
     * @param object  $file                 Compilatio File
     * @param object  $config               Activity configuration
     * @param boolean $teacher              Is a teacher
     * @param boolean $cantriggeranalysis   Has the right to trigger analysis
     * @param boolean $studentanalyse       Is a student analysis
     * @param boolean $viewreport           Has the right to see report
     * @param string  $url                  Url
     *
     * @return string Return the HTML formatted string.
     */
    public static function get_compilatio_btn(
        $domid,
        $file,
        $config,
        $teacher,
        $cantriggeranalysis,
        $studentanalyse,
        $viewreport,
        $url
    ) {
        global $OUTPUT, $PAGE, $CFG;

        $error = false;
        $titlevariable = null;
        $image = '';
        $status = $file->status;

        // Add de/indexing feature for teachers.
        $indexed = null;
        if (!empty($file->externalid) && $cantriggeranalysis && !$studentanalyse) {
            $indexed = $file->indexed ? true : false;
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'toggleIndexingState',
                array($CFG->httpswwwroot, $domid, $file->externalid));
        }

        if ($status == 'scored') {
            if ($viewreport) {
                $url = array("target-blank" => true, "url" => $file->reporturl);
            }
            $span = self::get_image_similarity($file->similarityscore, $config["green_threshold"] ?? 10, $config["orange_threshold"] ?? 25);
            $titlevariable = $file->similarityscore;

        } else if ($status == 'sent') {
            if ($config["analysis_type"] == 'planned') {
                $image = "prog";
                $span = get_string('btn_planned', "plagiarism_compilatio");
                $titlevariable = userdate($config["time_analyse"]);
            } else if ($cantriggeranalysis || ($studentanalyse && !$teacher)) {
                $image = "play";
                if (null == $url) {
                    $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAnalysis', array($CFG->httpswwwroot, $domid, $file->id));
                }
            } else if ($studentanalyse && $teacher) {
                $title = get_string("student_start_analyze", "plagiarism_compilatio");
            } else {
                return '';
            }

        } else if ($status == 'pending') {
            $image = "hourglass";

        } else if ($status == "queue") {
            $image = "queue";

        } else if ($status == "analyzing") {
            $image = "inprogress";
        // TODO str_starts_with = php8.
        } else if (str_starts_with($status, "error")) {
            $image = "exclamation";
            $error = true;
            // TODO ? Relancer les analyses individuellement.
            /*if ($status !== "error_failed_analysis" && $status !== "error_sending_failed") {
                $span = get_string('btn_error', "plagiarism_compilatio");
            }*/
            $span = get_string('btn_error', "plagiarism_compilatio");

            if ($status == "error_too_large") {
                $titlevariable = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
            } else if ($status == "error_too_long") {
                $titlevariable = get_config('plagiarism_compilatio', 'max_word');
            } else if ($status == "error_too_short") {
                $titlevariable = get_config('plagiarism_compilatio', 'min_word');
            }
        } else {
            return '';
        }

        if (!isset($title)) {
            $title = get_string('title_' . $status, "plagiarism_compilatio", $titlevariable);
        }

        if (!isset($span)) {
            $span = get_string('btn_' . $status, "plagiarism_compilatio");
        }

        $html = html_writer::empty_tag('br');
        $html .= html_writer::div("", 'compilatio-clear');
        // Var $compid is spread via class because it may be purified inside id attribute.
        $html .= html_writer::start_div('compilatio-area compi-'.$domid);

        // Indexing state.
        $html .= self::get_indexing_state($indexed);

        $html .= html_writer::start_div('compilatio-plagiarismreport',
            array('title' => htmlspecialchars($title, ENT_QUOTES)));

        if (!empty($url) && !empty($url["url"])) {
            if ($url["target-blank"] === true) {
                $target = '_blank';
            } else {
                $target = '_self';
            }
            // Var $url contain & that must not be escaped.
            $html .= "<a target='" . $target . "' class='compilatio-plagiarismreport-link' href='" . $url["url"] . "'>";
        }

        $compisquare = new moodle_url("/plagiarism/compilatio/pix/logo_compilatio_carre.png");
        $html .= html_writer::div('', 'small-logo-compi',
            array('style' => 'background-image: url(\'' . $compisquare . '\'); background-size:cover;'));

        // Image.
        if ($image !== "") {
            $imgsrc = $OUTPUT->image_url($image, 'plagiarism_compilatio');
            $html .= html_writer::img($imgsrc, '%', array('class' => 'float-right'));
        }

        // State information.
        if ($span !== "") {
            if ($error) {
                $class = "compilatio-error";
            } else {
                $class = "";
            }
            if (!empty($url) && !empty($url["url"])) {
                $class .= " link"; // Used to underline span on hover.
            }
            $html .= html_writer::span($span, $class);
        }
        if (!empty($url) && !empty($url["url"])) {
            $html .= html_writer::end_tag('a');
        }

        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::div("", 'compilatio-clear');

        return $html;
    }

    /**
     * Display an image and similarity percentage according to the thresholds
     *
     * @param  float  $score          Similarity score to be displayed
     * @param  int    $greenthreshold Green for $score lower than that threshold
     * @param  int    $redthreshold   Red for $score higher than that threshold
     * @return string                 the HTML string displaying colored score and an image
     */
    public static function get_image_similarity($score,
                                                $greenthreshold,
                                                $redthreshold) {

        global $OUTPUT;

        if ($score <= $greenthreshold) {
            $color = 'green';
        } else if ($score <= $redthreshold) {
            $color = 'orange';
        } else {
            $color = 'red';
        }
        $imgsrc = $OUTPUT->image_url($color, 'plagiarism_compilatio');

        $html = html_writer::start_span();
        $html .= html_writer::img($imgsrc, 'dot', array('class' => 'compilatio-similarity-image'));
        $html .= html_writer::start_span('compilatio-similarity compilatio-similarity-' . $color);
        $html .= $score;
        $html .= html_writer::span('%', 'compilatio-percentage');
        $html .= html_writer::end_span();
        $html .= html_writer::end_span();

        return $html;
    }

    /**
     * Display compilatio frame
     *
     * @param array   $alerts                Alerts
     * @param array   $plagiarismsettings    Plagiarism settings
     * @param boolean $startallanalysis      Boolean to display start all analyses
     * @param boolean $restartfailedanalysis Boolean to display restart failed analyses
     * @param array   $filesids              Files ids for update button
     * @param int     $countunsend           Count of files unsend to Compilatio
     * @param string  $analysisdate          Timed analyses date
     *
     * @return string Return the HTML formatted string.
     */
    public static function get_compilatio_frame(
        $cmid,
        $alerts,
        $plagiarismsettings,
        $startallanalysis,
        $restartfailedanalysis,
        $filesids,
        $module,
        $countunsend,
        $analysisdate
    ) {
        global $DB, $CFG, $PAGE;

        $output = '';
        $output .= "<div id='compilatio-container'>";

        // Display the tabs: Notification tab will be hidden if there is 0 alerts.
        $output .= "<div id='compilatio-tabs' style='display:none'>";

        // Display logo.
        $output .= self::get_logo();

        // Help icon.
        $output .= "<div title='" . get_string("compilatio_help_assign", "plagiarism_compilatio") .
            "' id='show-help' class='compilatio-icon'><i class='fa fa-question-circle fa-2x'></i></div>";

        // Stat icon.
        $output .= "<div id='show-stats' class='compilatio-icon'  title='" .
            get_string("display_stats", "plagiarism_compilatio") . "'><i class='fa fa-bar-chart fa-2x'></i></div>";

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
            $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');
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
                CompilatioStatistics::get_statistics($cmid) .
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

        $docid = optional_param('docId', null, PARAM_RAW);

        // Search tab.
        $output .= "<div id='compi-search' class='compilatio-tabs-content'>
            <h5>" . get_string("compilatio_search_tab", "plagiarism_compilatio") . "</h5>
            <p>" . get_string("compilatio_search_help", "plagiarism_compilatio") . "</p>
            <form class='form-inline' action=" . $PAGE->url . " method='post'>
                <input class='form-control m-2' type='text' id='docId' name='docId' value='" . $docid
                    . "' placeholder='" . get_string("compilatio_iddocument", "plagiarism_compilatio") . "'>
                <input class='btn btn-primary' type='submit' value='" .get_string("compilatio_search", "plagiarism_compilatio"). "'>
            </form>";

        if (!empty($docid)) {
            $sql = "SELECT usr.lastname, usr.firstname, cf.cm
                FROM {plagiarism_compilatio_files} cf
                JOIN {user} usr on cf.userid = usr.id
                WHERE cf.externalid = ?";
            $doc = $DB->get_record_sql($sql, array($docid));

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

        // Display buttons
        if (has_capability('plagiarism/compilatio:triggeranalysis', $PAGE->context)) {
            $output .= "<div id='compilatio-button-container'>";

            $output .= "
                <button class='compilatio-button comp-button'>
                        <i class='fa fa-refresh'></i>
                        " . get_string('updatecompilatioresults', 'plagiarism_compilatio') . "
                </button>";
            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'refreshButton',
                array($CFG->httpswwwroot, $filesids, $countunsend,
                get_string('update_in_progress', 'plagiarism_compilatio')));

            if ($startallanalysis) {
                $output .= "<button class='compilatio-button comp-button comp-start-btn' >
                        <i class='fa fa-play-circle'></i>
                        " . get_string('startallcompilatioanalysis', 'plagiarism_compilatio') . "
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startAllAnalysis',
                    array($CFG->httpswwwroot, $cmid, get_string("start_analysis_title", "plagiarism_compilatio"),
                    get_string("start_analysis_in_progress", "plagiarism_compilatio")));
            }

            if ($restartfailedanalysis) {
                $output .= "<button class='compilatio-button comp-button comp-restart-btn' >
                        <i class='fa fa-play-circle'></i>
                        " . get_string('restart_failed_analysis', 'plagiarism_compilatio') . "
                    </button>";
                $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'restartFailedAnalysis',
                    array($CFG->httpswwwroot, $cmid, get_string("restart_failed_analysis_title", "plagiarism_compilatio"),
                    get_string("restart_failed_analysis_in_progress", "plagiarism_compilatio")));
            }

            $output .= "</div>";
        }

        $params = array($CFG->httpswwwroot, count($alerts), $docid,
            "<div id='compilatio-show-notifications' title='" . get_string("display_notifications", "plagiarism_compilatio")
                . "' class='compilatio-icon active'><i class='fa fa-bell fa-2x'></i><span id='count-alerts'>1</span></div>",
            "<div id='compi-notifications'><h5 id='compi-notif-title'>" .
                get_string("tabs_title_notifications", "plagiarism_compilatio") . " : </h5>"
        );

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'compilatioTabs', $params);

        return $output;
    }
}

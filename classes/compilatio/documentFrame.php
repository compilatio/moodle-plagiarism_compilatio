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
 * documentFrame.php - Contains method to get Compilatio document frame with score, report, indexing state, ...
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/icons.php');

/**
 * CompilatioDocumentFrame class
 */
class CompilatioDocumentFrame {

    /**
     * Display plagiarism document area
     * @param string  $linkarray
     * @return string Return the HTML formatted string.
     */
    public static function get_document_frame($linkarray) {

        // Quiz management - Only essay question are supported for the moment.
        if (!empty($linkarray['component']) && $linkarray['component'] == 'qtype_essay') {
            $linkarray = self::manage_quiz($linkarray);
        }

        // Check if Compilatio is enabled in moodle->module->cm.
        if (!isset($linkarray['cmid']) || !compilatio_enabled($linkarray['cmid'])) {
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

        // Get submitter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In assigns.
            $userid = $DB->get_field('assign_submission', 'userid', ['id' => $linkarray['file']->get_itemid()]);
        }

        if (!empty($linkarray['content'])) {
            $identifier = sha1($linkarray['content']);
        } else if (!empty($linkarray['file'])) {
            $identifier = $linkarray['file']->get_contenthash();
        } else {
            return $output;
        }

        // Don't show Compilatio if not allowed.
        $modulecontext = context_module::instance($linkarray['cmid']);
        $isteacher = $canviewscore = $canviewreport = has_capability('plagiarism/compilatio:viewreport', $modulecontext);
        $cantriggeranalysis = has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext);
        $isstudentanalyse = compilatio_student_analysis($plugincm->studentanalyses, $linkarray['cmid'], $userid);

        if ($USER->id == $userid) {
            if ($isstudentanalyse) {
                $canviewreport = true;
                $canviewscore = true;
            }

            $assignclosed = false;
            if ($cm->completionexpected != 0 && time() > $cm->completionexpected) {
                $assignclosed = true;
            }

            $allowed = get_config('plagiarism_compilatio', 'enable_show_reports');
            $showreport = $plugincm->showstudentreport ?? null;
            if ($allowed === '1' && ($showreport == 'immediately' || ($showreport == 'closed' && $assignclosed))) {
                $canviewreport = true;
            }

            $showscore = $plugincm->showstudentscore ?? null;
            if ($showscore == 'immediately' || ($showscore == 'closed' && $assignclosed)) {
                $canviewscore = true;
            }
        }
        if (!$canviewscore) {
            return '';
        }

        // Get compilatio file record.
        $cmpfile = $DB->get_record('plagiarism_compilatio_files',
            ['cm' => $linkarray['cmid'], 'userid' => $userid, 'identifier' => $identifier]);

        if (empty($cmpfile) && isset($linkarray['cmp_filename'])) {
            $cmpfile = $DB->get_record('plagiarism_compilatio_files',
                ['cm' => $linkarray['cmid'], 'userid' => $userid, 'identifier' => sha1($linkarray['cmp_filename'])]);
        }

        if (empty($cmpfile)) { // Try to get record without userid in forums.
            $sql = 'SELECT * FROM {plagiarism_compilatio_files} WHERE cm = ? AND identifier = ?';
            $cmpfile = $DB->get_record_sql($sql, [$linkarray['cmid'], $identifier]);
        }

        $url = null;

        // No compilatio file in DB yet.
        if (empty($cmpfile)) {
            if ($cantriggeranalysis) {
                // Only works for assign.
                if (!isset($linkarray['file']) || $cm->modname != 'assign'
                    || $linkarray['file']->get_filearea() == 'introattachment') {
                    return $output;
                }

                // Catch GET 'sendfile' param.
                $trigger = optional_param('sendfile', 0, PARAM_INT);
                $fileid = $linkarray['file']->get_id();
                if ($trigger == $fileid) {
                    CompilatioSendFile::send_unsent_files([$linkarray['file']], $linkarray['cmid']);
                    return self::get_document_frame($linkarray);
                }

                $urlparams = [
                    'id' => $linkarray['cmid'],
                    'sendfile' => $fileid,
                    'action' => 'grading',
                    'page' => optional_param('page', null, PARAM_INT),
                ];
                $url = new moodle_url('/mod/assign/view.php', $urlparams);
                $url = $url->__toString();
            } else {
                return '';
            }
        }

        $output .= "<div id='cmp-" . $domid . "'></div>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'displayDocumentFrame', [
            $CFG->httpswwwroot,
            $cantriggeranalysis,
            $isstudentanalyse,
            $cmpfile->id ?? null,
            $canviewreport,
            $isteacher,
            $url,
            $domid,
        ]);

        return $output;
    }

    /**
     * Display plagiarism document area
     * @param string  $linkarray
     * @return string Return the HTML formatted string.
     */
    public static function display_document_frame(
        $cantriggeranalysis,
        $isstudentanalyse,
        $cmpfileid,
        $canviewreport,
        $isteacher,
        $url
    ) {
        global $DB, $CFG;

        if (!empty($cmpfileid)) {
            $cmpfile = $DB->get_record('plagiarism_compilatio_files', ['id' => $cmpfileid]);
        }

        $status = $cmpfile->status ?? null;

        // Plugin v2 docs management.
        $status = $status == 'to_analyze' ? 'queue' : $status;

        $config = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmpfile->cm ?? null]);

        $documentframe = $score = '';
        $bgcolor = 'primary';
        if ($status == 'scored') {
            if ($canviewreport) {

                $params = [
                    'docid' => $cmpfile->externalid,
                    'cmid' => $cmpfile->cm,
                    'type' => $config->reporttype,
                ];

                $href = "{$CFG->httpswwwroot}/plagiarism/compilatio/redirect_report.php?" . http_build_query($params);

                // Plugin v2 docs management.
                if (isset($cmpfile->reporturl)) {
                    $href = $cmpfile->reporturl;
                }

                $documentframe =
                    "<a href='{$href}' target='_blank' class='cmp-btn cmp-btn-doc cmp-btn-primary'>"
                        . CompilatioIcons::report() . get_string('report', 'core') .
                    "</a>";
            }

            $score = self::get_score($cmpfile, $config, $isteacher);

        } else if ($status == 'sent') {
            if (($config->analysistype ?? null) == 'planned') {
                $documentframe =
                    "<div
                        title='" . get_string('title_planned', 'plagiarism_compilatio', userdate($config->analysistime)) . "'
                        class='cmp-color-secondary'
                    >
                        <i class='cmp-icon-lg mx-2 fa fa-clock-o'></i>"
                        . get_string('btn_planned', 'plagiarism_compilatio') .
                    "</div>";
                $bgcolor = 'primary';
            } else if ($cantriggeranalysis || ($isstudentanalyse && !$isteacher)) {
                $documentframe =
                    "<div
                        title='" . get_string('title_sent', 'plagiarism_compilatio') . "'
                        class='cmp-btn cmp-btn-doc cmp-btn-primary cmp-start-btn'
                    >
                        <i class='cmp-icon-lg mr-2 fa fa-play-circle'></i>"
                        . get_string('btn_sent', "plagiarism_compilatio") .
                    "</div>";
            } else if ($isstudentanalyse && $isteacher) {
                $documentframe = '';
            } else {
                return '';
            }

        } else if ($status == "queue" || $status == "analysing") {
            $documentframe =
                "<div title='" . get_string('title_' . $status, "plagiarism_compilatio") . "' class='cmp-color-secondary'>
                    <i class='cmp-icon-lg mx-2 fa fa-spinner fa-spin'></i>"
                    . get_string('btn_' . $status, "plagiarism_compilatio") .
                "</div>";
            $bgcolor = 'primary';
        } else if (isset($status) && strpos($status, "error") === 0) {
            if ($status == "error_too_large") {
                $value = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
            } else if ($status == "error_too_long") {
                $value = get_config('plagiarism_compilatio', 'max_word');
            } else if ($status == "error_too_short") {
                $value = get_config('plagiarism_compilatio', 'min_word');
            }

            $documentframe =
                "<div title='" . get_string("title_" . $status, "plagiarism_compilatio", $value ?? null) . "' class='cmp-color-error'>
                    <i class='mx-2 fa fa-exclamation-triangle'></i>"
                    . get_string('btn_' . $status, "plagiarism_compilatio") .
                "</div>";
            $bgcolor = 'error';
        } else if (isset($url) && ($cantriggeranalysis || ($isstudentanalyse && !$isteacher))) {
            $documentframe =
                "<a
                    href='" . $url . "'
                    target='_self'
                    title='" . get_string('title_unsent', "plagiarism_compilatio") . "'
                    class='cmp-btn cmp-btn-doc cmp-btn-primary'
                >
                    <i class='mr-2 fa fa-paper-plane'></i>"
                    . get_string('btn_unsent', "plagiarism_compilatio") .
                "</a>";
        } else {
            return '';
        }

        $info = '';
        if ($isstudentanalyse) {
            if ($isteacher) {
                $info = "<div>" . get_string('student_analyse', 'plagiarism_compilatio') . "</div>";
            } else {
                $info = "<div>" . get_string('student_help', 'plagiarism_compilatio') . "</div>";
            }
        }

        // Add de/indexing feature for teachers.
        $indexed = null;
        if (!empty($cmpfile->externalid) && $cantriggeranalysis && !$isstudentanalyse) {
            // Plugin v2 docs management.
            if (null === $cmpfile->indexed) {
                $compilatio = new CompilatioAPI($config->userid);
                $document = $compilatio->get_document($cmpfile->externalid);
                $cmpfile->indexed = $document->indexed;
                $DB->update_record('plagiarism_compilatio_files', $cmpfile);
            }

            $indexed = $cmpfile->indexed ? true : false;
        }

        $output = $info . "
            <div class='cmp-area cmp-border-" . $bgcolor . "'>
                <img class='cmp-small-logo' src='" . new moodle_url("/plagiarism/compilatio/pix/c.svg") . "'>
                " . self::get_indexing_state($indexed) . $score . $documentframe . "
            </div>";
        return $output;
    }

    /**
     * Get the indexing state HTML
     *
     * @param  mixed  $indexingstate Indexing state
     * @return string                Return the HTML
     */
    private static function get_indexing_state($indexingstate) {
        $html = ''; // Do not show indexing state for a "non-teacher" user.

        if (isset($indexingstate)) {
            if ($indexingstate === true) {
                $class = 'cmp-library-in fa-check-circle';
                $title = get_string('indexed_document', 'plagiarism_compilatio');
            } else if ($indexingstate === false) {
                $class = 'cmp-library-out fa-times-circle';
                $title = get_string('not_indexed_document', 'plagiarism_compilatio');
            }

            $html = "<div class='cmp-library' title='" . $title . "'>
                " . CompilatioIcons::library() . "
                <i class='" . $class . " fa'></i>
            </div>";
        }

        return $html;
    }

    /**
     * Get the indexing state HTML
     *
     * @param  mixed  $indexingstate Indexing state
     * @return string                Return the HTML
     */
    public static function get_score($cmpfile, $config, $isteacher, $nowrap = false) {
        global $DB;

        $color = $cmpfile->globalscore <= ($config->warningthreshold ?? 10)
            ? 'green'
            : ($cmpfile->globalscore <= ($config->criticalthreshold ?? 25)
                ? 'orange'
                : 'red');

        $ignoredscores = empty($cmpfile->ignoredscores) ? [] : explode(',', $cmpfile->ignoredscores);

        $title = get_string('title_score', 'plagiarism_compilatio', $cmpfile->globalscore);
        $title .= $isteacher ? ' ' . get_string('title_score_teacher', 'plagiarism_compilatio') : '';

        $html = "<span title='{$title}' class='cmp-similarity cmp-color-{$color} align-middle'>
                    <i style='display: none;' class='fa fa-refresh'></i><span>{$cmpfile->globalscore}<small>%</small></span>
                </span>";

        $scores = ['similarityscore', 'utlscore'];
        $recipe = get_config('plagiarism_compilatio', 'recipe');

        $recipe === 'anasim-premium' ? array_push($scores, 'aiscore') : '';

        $icons = '';
        foreach ($scores as $score) {
            if (!isset($cmpfile->$score)) {
                continue;
            }

            if (in_array($score, $ignoredscores)) {
                $icon = 'ignored' . $score;
                $icons .= CompilatioIcons::$icon();
            } else {
                $icons .= CompilatioIcons::$score($cmpfile->$score > 0 ? $color : null);
            }
        }

        $tooltip = "<b>{$cmpfile->globalscore}" . get_string('tooltip_detailed_scores', 'plagiarism_compilatio') . "</b><br>";
        $ignoredtooltip = "<b>" . get_string('excluded_from_score', 'plagiarism_compilatio') . ' </b><br>';

        foreach ($scores as $score) {
            $message = isset($cmpfile->$score) ? $cmpfile->$score . '%' : get_string('unmeasured', 'plagiarism_compilatio');
            $message = get_string($score, 'plagiarism_compilatio') . " : <b>{$message}</b><br>";

            in_array($score, $ignoredscores) ? $ignoredtooltip .= $message : $tooltip .= $message;
        }

        if ($recipe !== 'anasim-premium') {
            $tooltip .= get_string('aiscore', 'plagiarism_compilatio') . " : <b>" . get_string('ai_score_not_included', 'plagiarism_compilatio') . "</b><br>";
        }

        $tooltip .= $ignoredtooltip;

        $html .= "<span id='cmp-score-icons' class='" . ($nowrap === true ? "flex-nowrap" : "d-flex") .
            "' data-toggle='tooltip' data-html='true' title='{$tooltip}'>
                        {$icons}
                </span>";

        return $html;
    }

    /**
     * Complete linkarray informations for quizzes
     *
     * @param array $linkarray
     * @return array Return linkarray
     */
    private static function manage_quiz($linkarray) {
        global $DB;

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
                        $courseid = $DB->get_field('course_modules', 'course', ['id' => $linkarray['cmid']]);
                        $attemptid = $DB->get_field('quiz_attempts', 'id', ['uniqueid' => $attempt->get_usage_id()]);
                        $linkarray['cmp_filename'] = "quiz-" . $courseid . "-" . $linkarray['cmid'] . "-" . $attemptid . "-Q" . $attempt->get_question_id() . ".htm";

                        $linkarray['content'] = $attempt->get_response_summary();
                    }
                }
            } else {
                return null;
            }
        }
        return $linkarray;
    }
}

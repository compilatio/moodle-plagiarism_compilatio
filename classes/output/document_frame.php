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
 * document_frame.php - Contains method to get Compilatio document frame with score, report, indexing state, ...
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\output;

use plagiarism_compilatio\output\icons;
use plagiarism_compilatio\compilatio\file;
use plagiarism_compilatio\compilatio\api;
use moodle_url;
use plagiarism_compilatio\compilatio\identifier;

/**
 * document_frame class
 */
class document_frame {

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

        global $DB, $CFG, $PAGE, $USER;
        $output = '';

        // Check if Compilatio is enabled in moodle->module->cm.
        if (!isset($linkarray['cmid']) || !compilatio_enabled($linkarray['cmid'])) {
            return $output;
        }

        // Get Compilatio's module configuration.
        $plugincm = compilatio_cm_use($linkarray['cmid']);

        // DOM Compilatio index for ajax callback.
        static $domid = 0;
        $domid++;

        $cm = get_coursemodule_from_id(null, $linkarray['cmid']);

        if (!$cm) {
            return $output;
        }

        // Get submitter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In assigns.
            $userid = $DB->get_field('assign_submission', 'userid', ['id' => $linkarray['file']->get_itemid()]);
        }

        if (empty($linkarray['content']) && empty($linkarray['file'])) {
            return $output;
        }

        // Don't show Compilatio if not allowed.
        $modulecontext = \context_module::instance($linkarray['cmid']);
        $isteacher = $canviewscore = $canviewreport = has_capability('plagiarism/compilatio:viewreport', $modulecontext);
        $cantriggeranalysis = has_capability('plagiarism/compilatio:triggeranalysis', $modulecontext);
        $isstudentanalyse = compilatio_student_analysis($plugincm->studentanalyses, $linkarray['cmid'], $userid);

        $groupid = null;
        $isonlinetext = false;

        if (isset($linkarray['file'])) {
            $content = $linkarray['file'];
            $itemid = $content->get_itemid();
            $filename = $content->get_filename();
        } else {
            $isonlinetext = true;
            $content = $linkarray['content'];

            if (isset($linkarray['assignment'])) {
                $assignmentid = $linkarray['assignment'];
                $itemid = null;

                $sql = "SELECT s.id
                        FROM {assign_submission} s
                        JOIN {assignsubmission_onlinetext} sot ON sot.submission = s.id
                        WHERE s.assignment = ?
                        AND sot.onlinetext = ?
                        LIMIT 1";

                $params = [$assignmentid, $content];

                $submission = $DB->get_record_sql($sql, $params);

                if ($submission) {
                    $itemid = $submission->id;
                }
                $filename = 'assign-' . $itemid . '.htm';

                $submission = $DB->get_record('assign_submission', ['id' => $itemid]);

                if ($submission && $submission->groupid != 0) {
                    $userid = 0;
                    $groupid = $submission->groupid;

                    $usergroupids = groups_get_user_groups($cm->course, $USER->id);
                    $userbelongstogroup = false;

                    foreach ($usergroupids as $grouptypeids) {
                        if (in_array($groupid, $grouptypeids)) {
                            $userbelongstogroup = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($USER->id == $userid || (isset($userbelongstogroup) && $userbelongstogroup)) {

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
        $compilatiofile = new file();

        // Get compilatio file record.
        $cmpfile = $compilatiofile->compilatio_get_document_with_failover(
            $linkarray['cmid'], $content, $userid, null, ['groupid' => $groupid]
        );

        if (empty($cmpfile) && isset($linkarray['cmp_filename'])) {
            $cmpfile = $compilatiofile->compilatio_get_document_with_failover(
                $linkarray['cmid'], $linkarray['cmp_filename'], $userid, null, ['groupid' => $groupid]);
        }

        if (empty($cmpfile)) { // Try to get record without userid in forums.
            $cmpfile = $compilatiofile->compilatio_get_document_with_failover(
                $linkarray['cmid'], $content, $linkarray['userid']);
        }

        $url = null;

        // No compilatio file in DB yet.
        if (empty($cmpfile)) {
            if ($cantriggeranalysis) {
                // Only works for assign.
                if ($cm->modname != 'assign') {
                    return $output;
                }

                // Handle online text submissions.
                if ($isonlinetext) {
                    $identifier = new identifier($linkarray['userid'], $linkarray['cmid']);

                    // Catch GET 'sendcontent'.
                    $trigger = optional_param('sendcontent', 0, PARAM_TEXT);
                    $contentid = $identifier->create_from_linkarray($linkarray);

                    if ($trigger === $contentid) {
                        $sql = 'SELECT assot.submission
                        FROM {assignsubmission_onlinetext} assot
                        JOIN {assign_submission} ass ON assot.submission = ass.id
                        WHERE ass.assignment = ? AND ass.userid = ?';

                        $onlineassignment = $DB->get_record_sql($sql, [$linkarray['assignment'], $linkarray['userid']]);
                        $filename = 'assign-' . $onlineassignment->submission . '.htm';

                        file::send_file($linkarray['cmid'], $userid,  null, $filename, $linkarray['content']);
                        return self::get_document_frame($linkarray);
                    }

                    $urlparams = [
                        'id' => $linkarray['cmid'],
                        'sendcontent' => $contentid,
                        'action' => 'grading',
                        'page' => optional_param('page', null, PARAM_INT),
                    ];
                    $url = new moodle_url('/mod/assign/view.php', $urlparams);
                    $url = $url->__toString();
                } else if (isset($linkarray['file']) && $linkarray['file']->get_filearea() != 'introattachment') {
                    // Handle file submissions.
                    // Catch GET 'sendfile'.
                    $trigger = optional_param('sendfile', 0, PARAM_TEXT);
                    $fileid = $linkarray['file']->get_id();
                    if ($trigger === $fileid) {
                        file::send_unsent_files([$linkarray['file']], $linkarray['cmid']);
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
                    // Neither content or valid file, return empty output.
                    return $output;
                }
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
     * Display plagiarism document frame
     *
     * @param boolean  $cantriggeranalysis
     * @param boolean  $isstudentanalyse
     * @param string   $cmpfileid
     * @param boolean  $canviewreport
     * @param boolean  $isteacher
     * @param string   $url
     * @return string  Return document frame HTML string.
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

        $compilatio = new api();

        if (!empty($cmpfileid)) {
            $cmpfile = $DB->get_record('plagiarism_compilatio_files', ['id' => $cmpfileid]);
        }

        $status = $cmpfile->status ?? null;

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

                // Display fake report button if under maintenance.
                if ($compilatio->is_in_maintenance()) {
                    $documentframe =
                        "<div
                            class='cmp-btn cmp-btn-doc cmp-btn-primary disabled'
                            title='" .self::formatstring('disabled_in_maintenance') .
                        "'>"
                            . icons::report() . self::formatstring('report', 'core') .
                        "</div>";
                } else {
                    $documentframe =
                        "<a href='{$href}' target='_blank' class='cmp-btn cmp-btn-doc cmp-btn-primary'>"
                            . icons::report() . self::formatstring('report', 'core') .
                        "</a>";
                }
            }

            $score = self::get_score($cmpfile, $config, $isteacher);

        } else if ($status == 'sent') {
            if (($config->analysistype ?? null) == 'planned') {
                $documentframe =
                    "<div
                        title='"
                            . self::formatstring(
                                'title_planned',
                                'plagiarism_compilatio',
                                userdate($config->analysistime)
                            ) . "'
                        class='cmp-color-secondary'>
                        <i class='cmp-icon-lg mx-2 fa fa-clock-o'></i>"
                        . self::formatstring('btn_planned') .
                    "</div>";
                $bgcolor = 'primary';
            } else if ($cantriggeranalysis || ($isstudentanalyse && !$isteacher)) {
                $documentframe =
                    "<div
                        title='" . ($compilatio->is_in_maintenance() ?
                            self::formatstring('disabled_in_maintenance') :
                            self::formatstring('title_sent')) . "'
                        class='cmp-btn cmp-btn-doc cmp-btn-primary cmp-start-btn'
                    >
                        <i class='cmp-icon-lg mr-1 fa fa-play-circle'></i>"
                        . self::formatstring('btn_sent') .
                    "</div>";
            } else if ($isstudentanalyse && $isteacher) {
                $documentframe = '';
            } else {
                return '';
            }

        } else if ($status == "queue" || $status == "analysing") {
            $documentframe =
                "<div title='" . self::formatstring('title_' . $status) . "' class='cmp-color-secondary cmp-action-btn'>
                    <i class='cmp-icon-lg mx-2 fa fa-spinner fa-spin'></i>"
                    . self::formatstring('btn_' . $status) .
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
                "<div title='"
                    . self::formatstring(
                        'title_' . $status,
                        'plagiarism_compilatio',
                        $value ?? null
                    ) . "' class='cmp-color-error mx-2 text-nowrap'>
                    <i class='mx-2 fa fa-exclamation-triangle'></i>" . self::formatstring('btn_' . $status) . "</div>";
            $bgcolor = 'error';
        } else if (isset($url) && ($cantriggeranalysis || ($isstudentanalyse && !$isteacher))) {

            // Display fake unset button if under maintenance.
            if ($compilatio->is_in_maintenance()) {
                $documentframe =
                    "<div
                        class='cmp-btn cmp-btn-doc cmp-btn-primary disabled'
                        title='" . self::formatstring('disabled_in_maintenance') .
                    "'>"
                        . "<i class='mr-2 fa fa-paper-plane'></i>"
                        . self::formatstring('btn_unsent') .
                    "</div>";
            } else {
                $documentframe =
                    "<a
                        href='" . $url . "'
                        target='_self'
                        title='" . self::formatstring('title_unsent') . "'
                        class='cmp-btn cmp-btn-doc cmp-btn-primary'
                    >
                        <i class='mr-2 fa fa-paper-plane'></i>"
                        . self::formatstring('btn_unsent') .
                    "</a>";
            }
        } else {
            return '';
        }

        $info = '';
        if ($isstudentanalyse) {
            if ($isteacher) {
                $info = "<div>" . self::formatstring('student_analyse') . "</div>";
            } else {
                $info = "<div>" . self::formatstring('student_help') . "</div>";
            }
        }

        // Add de/indexing feature for teachers.
        $indexed = null;
        if (!empty($cmpfile->externalid) && $cantriggeranalysis && !$isstudentanalyse) {
            // Plugin v2 docs management.
            if (null === $cmpfile->indexed) {
                $compilatio = new api($config->userid);
                $document = $compilatio->get_document($cmpfile->externalid);
                $cmpfile->indexed = $document->indexed;
                $DB->update_record('plagiarism_compilatio_files', $cmpfile);
            }

            $indexed = $cmpfile->indexed ? true : false;
        }

        $documentid = $cmpfile->externalid ?? '';
        $output = $info . '
            <div class="cmp-area cmp-border-' . $bgcolor . '" data-documentid="' . $documentid . '">
                <img class="cmp-small-logo" src="' . new moodle_url("/plagiarism/compilatio/pix/c.svg") . '">
                ' . self::get_indexing_state($indexed) . $score . $documentframe . '
            </div>';
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

        $compialtio = new api();

        if (isset($indexingstate)) {
            if ($indexingstate === true) {
                $class = 'cmp-library-in fa-check-circle';
                $title = self::formatstring('indexed_document');
            } else if ($indexingstate === false) {
                $class = 'cmp-library-out fa-times-circle';
                $title = self::formatstring('not_indexed_document');
            }

            if ($compialtio->is_in_maintenance()) {
                $title = self::formatstring('disabled_in_maintenance');
            }

            $html = "<div class='cmp-library' title='" . $title . "'>
                " . icons::library() . "
                <i class='" . $class . " fa'></i>
            </div>";
        }

        return $html;
    }

    /**
     * Get score area with icons HTML
     *
     * @param  object  $cmpfile
     * @param  object  $config
     * @param  boolean $isteacher
     * @param  boolean $nowrap
     * @return string  Return score area HTML string
     */
    public static function get_score($cmpfile, $config, $isteacher, $nowrap = false) {

        $compilatio = new api();

        $color = $cmpfile->globalscore <= ($config->warningthreshold ?? 10)
            ? 'green'
            : ($cmpfile->globalscore <= ($config->criticalthreshold ?? 25)
                ? 'orange'
                : 'red');

        $ignoredscores = empty($cmpfile->ignoredscores) ? [] : explode(',', $cmpfile->ignoredscores);

        $title = self::formatstring('title_score', 'plagiarism_compilatio', $cmpfile->globalscore);
        $title .= $isteacher ?
            ( $compilatio->is_in_maintenance() ?
                ' ' . self::formatstring('disabled_in_maintenance')
                : ' ' . self::formatstring('title_score_teacher'))
            : '';

        $html = "<span title='{$title}' class='cmp-similarity cmp-color-{$color} d-flex align-items-center justify-content-center'>
                    <i style='display: none;' class='fa fa-refresh'></i><span>{$cmpfile->globalscore}<small>%</small></span>
                </span>";

        $scores = ['simscore', 'utlscore'];
        $recipe = get_config('plagiarism_compilatio', 'recipe');

        $recipe === 'anasim-premium' ? array_push($scores, 'aiscore') : '';

        $icons = '';
        foreach ($scores as $score) {
            if (!isset($cmpfile->$score)) {
                continue;
            }

            if (in_array($score, $ignoredscores)) {
                $icon = 'ignored' . $score;
                $icons .= icons::$icon();
            } else {
                $icons .= icons::$score($cmpfile->$score > 0 ? $color : null);
            }
        }

        $tooltip = "<b>{$cmpfile->globalscore}" . self::formatstring('tooltip_detailed_scores') . "</b><br>";
        $ignoredtooltip = "<b>" . self::formatstring('excluded_from_score') . ' </b><br>';

        foreach ($scores as $score) {
            $message = isset($cmpfile->$score) ? $cmpfile->$score . '%' : self::formatstring('unmeasured');
            $message = self::formatstring($score) . " : <b>{$message}</b><br>";

            in_array($score, $ignoredscores) ? $ignoredtooltip .= $message : $tooltip .= $message;
        }

        if ($recipe !== 'anasim-premium') {
            $tooltip .= self::formatstring('aiscore') . " : <b>" . self::formatstring('ai_score_not_included') . "</b><br>";
        }

        if (!empty($ignoredscores)) {
            $tooltip .= $ignoredtooltip;
        }

        $html .= "<span id='cmp-score-icons' class='" . ($nowrap === true ? "flex-nowrap" : "d-flex") .
            "' data-toggle='tooltip' data-html='true' title='{$tooltip}'>
                        {$icons}
                </span>";

        return $html;
    }

    /**
     * Format translation string if needed
     *
     * @param  string $stringid  String identifier
     * @param  string $component moodle component
     * @param  string $a         optional string to include in translation
     * @return string Formated string
     */
    private static function formatstring(string $stringid, string $component = 'plagiarism_compilatio', string $a = null) {
        $str = get_string($stringid, $component, $a);
        if (preg_match("/&#[0-9]+;|&[a-z]+;/", $str)) {
            return $str;
        }
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
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
            $quba = \question_engine::load_questions_usage_by_activity($linkarray['area']);

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
                        $linkarray['cmp_filename'] = "quiz-" . $courseid . "-" . $linkarray['cmid']
                            . "-" . $attemptid . "-Q" . $attempt->get_question_id() . ".htm";
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

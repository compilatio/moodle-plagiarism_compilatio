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

        // Get submiter userid.
        $userid = $linkarray['userid']; // In Workshops and forums.
        if ($cm->modname == 'assign' && isset($linkarray['file'])) { // In assigns.
            $userid = $DB->get_field('assign_submission', 'userid', ['id' => $linkarray['file']->get_itemid()]);
        }

        if (!empty($linkarray['content'])) {
            $identifier = sha1($linkarray['content']);
        } else if (!empty($linkarray['file'])) {
            $filename = $linkarray['file']->get_filename();
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
        $cmpfile = $DB->get_record('plagiarism_compilatio_file',
            ['cm' => $linkarray['cmid'], 'userid' => $userid, 'identifier' => $identifier]);

        if (empty($cmpfile)) { // Try to get record without userid in forums.
            $sql = 'SELECT * FROM {plagiarism_compilatio_file} WHERE cm = ? AND identifier = ?';
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
                    'page' => optional_param('page', null, PARAM_INT)
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
            $filename,
            $domid
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
        $url,
        $filename
    ) {

        global $DB, $CFG;

        $cmpfile = $DB->get_record('plagiarism_compilatio_file', ['id' => $cmpfileid]);

        $status = $cmpfile->status ?? null;

        // ADTD v2 document management.
        $status = $status == 'to_analyze' ? 'queue' : $status;

        $config = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmpfile->cm ?? null]);

        $documentframe = $score = '';
        $bgcolor = 'primary';
        if ($status == 'scored') {
            if ($canviewreport) {
                $href = "{$CFG->httpswwwroot}/plagiarism/compilatio/redirect_report.php?docid={$cmpfile->externalid}&cmid={$cmpfile->cm}&type={$config->reporttype}";

                // ADTD v2 document management.
                if (isset($cmpfile->reporturl)) {
                    $href = $cmpfile->reporturl;
                }

                $documentframe =
                    "<a href='{$href}' target='_blank' class='cmp-btn cmp-btn-doc cmp-btn-primary cursor-pointer'>
                        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 67' width='20' class='mr-2 icon-inline'>
                            <path fill='#494c4e' d='M71.61,34.39h0A3.6,3.6,0,1,1,68,30.79,3.59,3.59,0,0,1,71.61,34.39ZM91.14.15a9,9,0,0,0-7.91,13.34L72,26.31a8.91,8.91,0,0,0-4-.94,9,9,0,0,0-8.44,5.83L43.11,27.9a9,9,0,1,0-16.64,6.59L13.18,49.44a8.88,8.88,0,0,0-4-.95,9,9,0,1,0,7.92,4.71l13.29-15a8.92,8.92,0,0,0,4,1,9,9,0,0,0,8.43-5.83l16.47,3.3A9,9,0,0,0,77,34.39a8.93,8.93,0,0,0-1.11-4.33L87.14,17.24a9,9,0,1,0,4-17.09Zm-82,61a3.6,3.6,0,1,1,3.6-3.59A3.59,3.59,0,0,1,9.16,61.1ZM34.39,33.78A3.6,3.6,0,1,1,38,30.18,3.6,3.6,0,0,1,34.39,33.78Zm56.74-21a3.6,3.6,0,1,1,3.6-3.6A3.6,3.6,0,0,1,91.13,12.76Z'></path>
                        </svg>"
                        . get_string('report', 'core') .
                    "</a>";
            }

            $score = self::get_score($cmpfile->similarityscore, $config, $isteacher);

        } else if ($status == 'sent') {
            if (($config->analysistype ?? null) == 'planned') {
                $documentframe =
                    "<div title='" . get_string('title_planned', 'plagiarism_compilatio', userdate($config->analysistime)) . "' class='cmp-btn-secondary'>
                        <i class='cmp-icon-lg mx-2 fa fa-clock-o'></i>"
                        . get_string('btn_planned', 'plagiarism_compilatio') .
                    "</div>";
                $bgcolor = 'primary';
            } else if ($cantriggeranalysis || ($isstudentanalyse && !$isteacher)) {
                $documentframe =
                    "<div title='" . get_string('title_sent', 'plagiarism_compilatio') . "' class='cmp-btn cmp-btn-doc cmp-btn-primary cmp-start-btn cursor-pointer'>
                        <i class='cmp-icon-lg mr-2 fa fa-play-circle'></i>"
                        . get_string('btn_sent', "plagiarism_compilatio") .
                    "</div>";
            } else if ($isstudentanalyse && $isteacher) {
                $documentframe = '';
            } else {
                return '';
            }

        } else if ($status == "queue" || $status == "analyzing") {
            $documentframe =
                "<div title='" . get_string('title_' . $status, "plagiarism_compilatio") . "' class='cmp-btn-secondary'>
                    <i class='cmp-icon-lg mx-2 fa fa-spinner fa-spin'></i>"
                    . get_string('btn_' . $status, "plagiarism_compilatio") .
                "</div>";
            $bgcolor = 'primary';
        } else if (strpos($status, "error") === 0) {
            if ($status == "error_too_large") {
                $value = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
            } else if ($status == "error_too_long") {
                $value = get_config('plagiarism_compilatio', 'max_word');
            } else if ($status == "error_too_short") {
                $value = get_config('plagiarism_compilatio', 'min_word');
            }

            $documentframe =
                "<div title='" . get_string("title_" . $status, "plagiarism_compilatio", $value ?? null) . "' class='cmp-btn-error'>
                    <i class='mx-2 fa fa-exclamation-triangle'></i>"
                    . get_string('btn_' . $status, "plagiarism_compilatio") .
                "</div>";
            $bgcolor = 'error';
        } else if (isset($url) && ($cantriggeranalysis || ($isstudentanalyse && !$isteacher))) {
            $documentframe =
                "<a href='" . $url . "' target='_self' title='" . get_string('title_unsent', "plagiarism_compilatio") . "' class='cmp-btn cmp-btn-doc cmp-btn-primary cursor-pointer'>
                    <i class='cmp-icon-lg mr-2 fa fa-play-circle'></i>"
                    . get_string('btn_unsent', "plagiarism_compilatio") .
                "</a>";
        } else {
            return '';
        }

        $info = '';
        if ($isstudentanalyse) {
            if ($isteacher) {
                $info = "<div>" . get_string('student_analyze', 'plagiarism_compilatio') . "</div>";
            } else {
                $info = "<div>" . get_string('student_help', 'plagiarism_compilatio') . "</div>";
            }
        }

        // Add de/indexing feature for teachers.
        $indexed = null;
        if (!empty($cmpfile->externalid) && $cantriggeranalysis && !$isstudentanalyse) {
            // ADTD v2 document management.
            if (null === $cmpfile->indexed) {
                $compilatio = new CompilatioAPI($config->userid);
                $document = $compilatio->get_document($cmpfile->externalid);
                $cmpfile->indexed = $document->indexed;
                $DB->update_record('plagiarism_compilatio_file', $cmpfile);
            }

            $indexed = $cmpfile->indexed ? true : false;
        }

        $output = $info . "
            <div class='cmp-area cmp-border-" . $bgcolor . "'>
                <img class='cmp-small-logo' src='" . new moodle_url("/plagiarism/compilatio/pix/c-net.svg") . "'>
                " . self::get_indexing_state($indexed) . $score . $documentframe . "
            </div>";

        // Now check for differing filename and display info related to it.
        if (isset($filename, $cmpfile->filename) && $filename !== $cmpfile->filename) {
            $output .= "<span class='cmp-prevsubmitted'>(" . get_string('previouslysubmitted', 'plagiarism_compilatio') . ': ' . $cmpfile->filename . ")</span>";
        }

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
                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' fill-opacity='50%'>
                    <path d='M21.43 8.438c-.088-.701-.101-1.909.52-2.314.011-.007.02-.018.03-.027.507-.17.859-.41.583-.731L15.07 3 2.941 4.768s-1.39.208-1.265 2.47c.067 1.231.436 1.836.758 2.133l-.996.315c-.276.321.075.56.583.73.01.01.018.02.03.028.62.405.608 1.613.519 2.314-2.23.664-1.43.88-1.43.88l.49.124c-.344.326-.686.944-.622 2.116.124 2.262 1.265 2.418 1.265 2.418L10.21 21l11.981-3.042s.801-.216-1.43-.88c-.09-.7-.102-1.907.52-2.314.012-.007.02-.018.03-.027.508-.17.859-.41.583-.73l-.521-.166c.347-.22.869-.793.95-2.283.057-1.025-.198-1.626-.493-1.98l1.03-.26s.8-.216-1.43-.88zm-10.021-.03l2.014-.433 6.81-1.467 1.014-.219c-.324.622-.31 1.473-.257 2.02.012.124.025.237.039.323l-1.11.29-8.595 2.24.085-2.753zM2.754 10.61l1.014.218 6.54 1.41.57.122 1.714.37.084 2.752-8.833-2.303-.87-.227c.012-.086.026-.199.038-.323.053-.546.067-1.397-.257-2.02zM2.36 7.129c-.013-.602.09-1.037.296-1.258a.526.526 0 01.394-.17c.056 0 .097.008.1.008l5.226 1.786 2.608.89-.086 2.773-7.315-2.15-.387-.113a.224.224 0 00-.048-.008c-.03-.002-.753-.072-.788-1.758zm7.87 12.67l-7.701-2.263a.218.218 0 00-.049-.008c-.03-.002-.754-.072-.789-1.758-.012-.603.09-1.037.297-1.259a.527.527 0 01.393-.17c.057 0 .097.008.1.008l7.834 2.677-.085 2.773zm10.091-2.85c.013.124.026.237.04.323l-9.705 2.53.084-2.753 2.075-.447.307.078 1.148-.391 5.294-1.14 1.015-.22c-.325.622-.311 1.474-.258 2.02zm.535-3.742a.178.178 0 00-.052.009l-.732.215-6.969 2.048-.085-2.773 2.286-.781 5.537-1.893s.291-.068.504.16c.207.22.31.656.297 1.257-.036 1.686-.76 1.756-.786 1.758z'></path>
                </svg>
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
    public static function get_score($score, $config, $isteacher) {
        if ($score <= $config->warningthreshold ?? 10) {
            $color = 'green';
        } else if ($score <= $config->criticalthreshold ?? 25) {
            $color = 'orange';
        } else {
            $color = 'red';
        }

        $title = get_string('title_score', 'plagiarism_compilatio', $score);
        $title .= $isteacher ? ' ' . get_string('title_score_teacher', 'plagiarism_compilatio') : '';

        return "<span title='{$title}' class='cmp-similarity cmp-color-{$color}'>
                    <i class='fa fa-circle'></i> {$score}<small>%</small>
                </span>";
    }

    /**
     * Complete linkarray informations for quizzes
     *
     * @param array $linkarray
     * @return array Return linkarray
     */
    private static function manage_quiz($linkarray) {
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
                return null;
            }
        }
        return $linkarray;
    }
}

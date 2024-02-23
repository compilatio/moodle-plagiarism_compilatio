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
 * statistics.php - Contains statistics methods.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompilatioStatistics class
 */

class CompilatioStatistics {
    /**
     * Get global plagiarism statistics
     *
     * @param bool   $html display HTML if true, text otherwise
     * @return array       containing associative arrays for the statistics
     */
    public static function get_global_statistics($html = true) {

        global $DB;

        $sql = "SELECT cm,
                course.id,
                course.fullname course,
                modules.name module_type,
                CONCAT(COALESCE(assign.name, ''), COALESCE(forum.name, ''), COALESCE(workshop.name, ''),
                COALESCE(quiz.name, '')) module_name,
                AVG(globalscore) avg,
                MIN(globalscore) min,
                MAX(globalscore) max,
                COUNT(DISTINCT plagiarism_compilatio_file.id) count
            FROM {plagiarism_compilatio_file} plagiarism_compilatio_file
            JOIN {course_modules} course_modules
                ON plagiarism_compilatio_file.cm = course_modules.id
            JOIN {modules} modules ON modules.id = course_modules.module
            LEFT JOIN {assign} assign ON course_modules.instance = assign.id AND modules.name = 'assign'
            LEFT JOIN {forum} forum ON course_modules.instance = forum.id AND modules.name = 'forum'
            LEFT JOIN {workshop} workshop ON course_modules.instance = workshop.id AND modules.name = 'workshop'
            LEFT JOIN {quiz} quiz ON course_modules.instance = quiz.id AND modules.name = 'quiz'
            JOIN {course} course ON course_modules.course= course.id
            WHERE status='scored'
            GROUP BY cm,
                course.id,
                course.fullname,
                assign.name,
                forum.name,
                quiz.name,
                workshop.name,
                modules.name
            ORDER BY course.fullname, assign.name";

        $rows = $DB->get_records_sql($sql);

        $results = [];
        foreach ($rows as $row) {
            $query = "SELECT usr.id userid, usr.firstname firstname, usr.lastname lastname
                FROM {course} course
                JOIN {context} context ON context.instanceid= course.id
                JOIN {role_assignments} role_assignments ON role_assignments.contextid= context.id
                JOIN {user} usr ON role_assignments.userid= usr.id
                WHERE context.contextlevel=50 AND role_assignments.roleid=3 AND course.id=" . $row->id;

            $teachers = $DB->get_records_sql($query);
            $courseurl = new moodle_url('/course/view.php', ['id' => $row->id]);
            $activityurl = new moodle_url('/mod/' . $row->module_type . '/view.php', ['id' => $row->cm, 'action' => 'grading']);

            $result = [];
            if ($html) {
                $result['course'] = "<a href='$courseurl'>$row->course</a>";
                $result['activity'] = "<a href='$activityurl'>$row->module_name</a>";

            } else {
                $result['courseid'] = $row->id;
                $result['course'] = $row->course;
                $result['activityid'] = $row->cm;
                $result['activity'] = $row->module_name;
            }

            $result['analyzed_documents_count'] = $row->count;
            $result['minimum_rate'] = $row->min;
            $result['maximum_rate'] = $row->max;
            $result['average_rate'] = round($row->avg, 2);

            $result['teacher'] = '';
            $teacherid = [];
            $teachername = [];
            foreach ($teachers as $teacher) {
                $userurl = new moodle_url('/user/view.php', ['id' => $teacher->userid]);
                if ($html) {
                    $result['teacher'] .= "- <a href='$userurl'>$teacher->lastname $teacher->firstname</a></br>";

                } else {
                    array_push($teacherid, $teacher->userid);
                    array_push($teachername, $teacher->lastname . ' ' . $teacher->firstname);
                }
            }
            if (!$html) {
                $result["teacherid"] = implode(', ', $teacherid);
                $result["teacher"] = implode(', ', $teachername);
            }

            $sql = "SELECT status, COUNT(DISTINCT id) AS count
                FROM {plagiarism_compilatio_file}
                WHERE cm = ? AND status LIKE 'error%'
                GROUP BY status";

            $countstatus = $DB->get_records_sql($sql, [$row->cm]);

            if ($html) {
                $result['errors'] = '';
                foreach ($countstatus as $stat) {
                    $result['errors'] .= "- {$stat->count} " . get_string("short_{$stat->status}", 'plagiarism_compilatio').'</br>';
                }
            } else {
                foreach ($countstatus as $stat) {
                    $result[$stat->status] = $stat->count;
                }
            }

            $results[] = $result;
        }

        return $results;
    }


    /**
     * Get statistics for the assignment $cmid
     *
     * @param  string $cmid Course module ID
     * @return string       HTML containing the statistics
     */
    public static function get_statistics($cmid) {

        global $DB, $PAGE;

        $sql = "SELECT status, COUNT(DISTINCT id) AS count FROM {plagiarism_compilatio_file}  WHERE cm = ? GROUP BY status";
        $countbystatus = $DB->get_records_sql($sql, [$cmid]);

        $output = "
            <div class='col'>
                <h4 class='text-primary'>" . get_string('progress', 'plagiarism_compilatio') . "</h4>
                <div class='position-relative cmp-box my-3 p-3'>
                    <h5 class='fw-bold cmp-color-green'>"
                      . get_string('analysed_docs', 'plagiarism_compilatio', $countbystatus['scored']->count ?? 0) .
                    "</h5>
                </div>

                <div class='cmp-box my-3'>
                    <h5 class='p-3 text-primary'>"
                      . get_string('analysing_docs', 'plagiarism_compilatio', $countbystatus['analysing']->count ?? 0) .
                    "</h5>
                </div>

                <div class='cmp-box my-3'>
                    <h5 class='p-3 text-primary'>"
                      . get_string('queuing_docs', 'plagiarism_compilatio', $countbystatus['queue']->count ?? 0) .
                    "</h5>
                </div>
            </div>";

        if (isset($countbystatus['scored']->count) && $countbystatus['scored']->count > 0) {
            $plagiarismvalues = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
            $warningthreshold = $plagiarismvalues->warningthreshold ?? 10;
            $criticalthreshold = $plagiarismvalues->criticalthreshold ?? 25;

            $from = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_file} pcf WHERE pcf.cm=?";

            $countgreen = $DB->count_records_sql(
                $from . " AND status = 'scored' AND globalscore <= $warningthreshold",
                [$cmid]
            );
            $countorange = $DB->count_records_sql(
                $from . " AND status = 'scored' AND globalscore > $warningthreshold AND globalscore <= $criticalthreshold",
                [$cmid]
            );
            $countred = $DB->count_records_sql(
                $from . " AND status = 'scored' AND globalscore > $criticalthreshold",
                [$cmid]
            );

            $scorestats = $DB->get_record_sql(
                "SELECT ROUND(AVG(globalscore)) avg, MIN(globalscore) min, MAX(globalscore) max
                    FROM {plagiarism_compilatio_file} pcf
                    WHERE pcf.cm=? AND status='scored'",
                [$cmid]
            );

            $yes = '';
            $elements = ['min', 'avg', 'max'];
            foreach ($elements as $elem) {
                if ($scorestats->$elem <= $warningthreshold) {
                    $color = 'green';
                } else if ($scorestats->$elem <= $criticalthreshold) {
                    $color = 'orange';
                } else {
                    $color = 'red';
                }
                $yes .= "<div class='col-4'>
                        <div class='pt-1 pb-2 fw-bold cmp-color-secondary'>"
                            . get_string('stats_' . $elem, 'plagiarism_compilatio') .
                        "</div>
                        <h3 class='cmp-color-" . $color . "'>" . $scorestats->$elem . " <small>%</small></h3>
                    </div>";
            }

            $output .= "
                <div class='col'>
                    <h4 class='text-primary'>" . get_string('results', 'plagiarism_compilatio') . "</h4>
                    <div class='cmp-box my-3 px-3 pt-3 pb-2'>
                        <h5 class='text-primary'>" . get_string('stats_score', 'plagiarism_compilatio') . "</h5>
                        <div class='row'>{$yes}</div>
                    </div>

                    <div class='cmp-box my-3 p-3'>
                        <h5 class='text-primary'>" . get_string('stats_threshold', 'plagiarism_compilatio') . "</h5>
                        <div class='row mt-3 fw-bold cmp-color-secondary'>
                            <div class='col-4'>
                                <h3 class='fw-bold cmp-color-green'>" . $countgreen . "</h3>
                                <small>< " . $warningthreshold . " %</small>
                            </div>
                            <div class='col-4'>
                                <h3 class='fw-bold cmp-color-orange'>" . $countorange . "</h3>
                                <small>" . $warningthreshold . " % - " . $criticalthreshold . " %</small>
                            </div>
                            <div class='col-4'>
                                <h3 class='fw-bold cmp-color-red'>" . $countred . "</h3>
                                <small>> " . $criticalthreshold . " %</small>
                            </div>
                        </div>
                    </div>
                </div>";
        }

        $errors = '';
        foreach ($countbystatus as $status => $stat) {
            if (strpos($status, 'error') === 0) {
                if ($status == 'error_too_large') {
                    $stringvariable = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
                } else if ($status == 'error_too_long') {
                    $stringvariable = get_config('plagiarism_compilatio', 'max_word');
                } else if ($status == 'error_too_short') {
                    $stringvariable = get_config('plagiarism_compilatio', 'min_word');
                }

                $shortstring = get_string('short_' . $status, 'plagiarism_compilatio')
                    ?? get_string('stats_error_unknown', 'plagiarism_compilatio');
                $detailedstring = get_string('detailed_' . $status, 'plagiarism_compilatio', $stringvariable ?? null) ?? '';

                $errors .= "<div class='position-relative cmp-box my-3 p-3'>
                        <h5 class='cmp-color-red'>{$stat->count} {$shortstring}</h5>
                        <small>{$detailedstring}</small>
                    </div>";
            }
        }
        if (!empty($errors)) {
            $output .= "
                <div class='col'>
                    <h4 class='text-primary'>" . get_string('errors', 'plagiarism_compilatio') . "</h4>
                    {$errors}
                </div>";
        }

        return $output;
    }

    /**
     * Display an array as a list, using moodle translations and parameters
     * Index 0 for translation index and index 1 for parameter
     *
     * @param  array $listitems List items
     * @return string           Return the stat string
     */
    public static function display_list_stats($listitems) {

        $string = "<ul>";
        foreach ($listitems as $listitem) {
            $string .= "<li>" . get_string($listitem[0], 'plagiarism_compilatio', $listitem[1]) . "</li>";
        }
        $string .= "</ul>";
        return $string;
    }

    /**
     * Lists files of an assignment according to the status code
     *
     * @param  string $cmid       Course module ID
     * @param  int    $status     Status
     * @return array              containing the student & the file
     */
    public static function get_files_by_status_code($cmid, $status) {

        global $DB;

        $sql = "SELECT DISTINCT pcf.id, pcf.filename, pcf.userid
            FROM {plagiarism_compilatio_file} pcf
            WHERE pcf.cm=? AND status = ?";

        $files = $DB->get_records_sql($sql, [$cmid, $status]);

        if (!empty($files)) {
            // Don't display user name for anonymous assign.
            $sql = "SELECT blindmarking, assign.id FROM {course_modules} cm
                JOIN {assign} assign ON cm.instance= assign.id
                WHERE cm.id = $cmid";
            $anonymousassign = $DB->get_record_sql($sql);

            if (!empty($anonymousassign) && $anonymousassign->blindmarking) {
                foreach ($files as $file) {
                    $anonymousid = $DB->get_field('assign_user_mapping', 'id',
                        ['assignment' => $anonymousassign->id, 'userid' => $file->userid]);
                    $file->user = get_string('hiddenuser', 'assign') . ' ' . $anonymousid;
                }

                return array_map(
                    function ($file) {
                        return $file->user . ' : ' . $file->filename;
                    }, $files);
            } else {
                foreach ($files as $file) {
                    $user = $DB->get_record('user', ['id' => $file->userid]);
                    $file->lastname = $user->lastname;
                    $file->firstname = $user->firstname;
                }

                return array_map(
                    function ($file) {
                        return $file->lastname . ' ' . $file->firstname . ' : ' . $file->filename;
                    }, $files);
            }
        } else {
            return [];
        }
    }

    /**
     * Get statistics for the student selected on the dropdown selector
     *
     * @param  string $studentid user
     * @param  string $cmid Course module ID
     * @return string       HTML containing the statistics for this student
     */
    public static function get_statistics_by_student($studentid, $cmid) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $output = "";

        $sql = "SELECT {quiz_attempts}.id
                FROM {quiz_attempts}
                    INNER JOIN {quiz} ON {quiz}.id = {quiz_attempts}.quiz
                    INNER JOIN {course_modules} ON {course_modules}.instance = {quiz}.id
                WHERE {course_modules}.id = ? AND {quiz_attempts}.userid = ?
                ORDER BY {quiz_attempts}.attempt DESC
                LIMIT 1";

        $attemptid = $DB->get_field_sql($sql, [$cmid, $studentid]);

        $attempt = $CFG->version < 2023100900 ? \quiz_attempt::create($attemptid) : \mod_quiz\quiz_attempt::create($attemptid);

        $counttotalattemptwords = 0;
        $globalattemptscore = 0;
        $totalfilesanalyzed  = 0;

        $config = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

        $output .= "<div class='cmp-table-height'>
            <table class='table mb-0 align-middle rounded-lg cmp-bckgrnd-grey table-hover'>
            <thead>
                <tr>
                    <th class='text-center align-middle cmp-border-none'>" . get_string('question', 'plagiarism_compilatio') . "</th>
                    <th class='text-center align-middle cmp-border-none'>" . get_string('response_type', 'plagiarism_compilatio') . "</th>
                    <th class='text-center align-middle cmp-border-none text-nowrap'>" . get_string('suspect_words_quiz_on_total', 'plagiarism_compilatio') . "</th>
                    <th class='text-center align-middle cmp-border-none'>" . get_string('score', 'plagiarism_compilatio') . "</th>
                    <th class='text-center align-middle cmp-border-none'></th>
                </tr>
            </thead>
            <tbody class='cmp-bckgrnd-white'>";

        $context = context_module::instance($cmid);

        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmid]);
        $compilatio = new CompilatioAPI($userid);

        $nbmotsmin = get_config('plagiarism_compilatio', 'min_word');

        foreach ($attempt->get_slots() as $slot) {

            $answer = $attempt->get_question_attempt($slot);
            $content = $answer->get_response_summary();

            $cmpfiles = [];

            $wordcount = $content !== null
                ? str_word_count(mb_convert_encoding(strip_tags($content), 'ISO-8859-1', 'UTF-8'))
                : 0;
            if ($wordcount >= $nbmotsmin) {
                $courseid = $DB->get_field('course_modules', 'course', array('id' => $cmid));
                $filename = "quiz-" . $courseid . "-" . $cmid . "-" . $attemptid . "-Q" . $answer->get_question_id() . ".htm";

                $cmpfile = $DB->get_record('plagiarism_compilatio_file', ['cm' => $cmid, 'userid' => $studentid, 'identifier' => sha1($filename)]);
                if (!empty($cmpfile)) {
                    $cmpfile->wordcount = $wordcount;
                    $cmpfiles[] = $cmpfile;
                }
            }

            $files = $answer->get_last_qt_files('attachments', $context->id);
            foreach ($files as $file) {
                $cmpfile = $DB->get_record('plagiarism_compilatio_file', ['cm' => $cmid, 'userid' => $studentid, 'identifier' => $file->get_contenthash()]);

                if (!empty($cmpfile)) {
                    $cmpfiles[] = $cmpfile;
                    $document = $compilatio->get_document($cmpfile->externalid);
                    $cmpfile->wordcount = $document->words_count ?? 0;
                }
            }

            $cmpfilescount = count($cmpfiles);

            foreach ($cmpfiles as $index => $cmpfile) {
                if ($cmpfile->status == 'scored') {
                    $counttotalattemptwords += $cmpfile->wordcount;
                    $globalattemptscore += $cmpfile->globalscore;
                    $suspectwordsquestion = round($cmpfile->globalscore * $cmpfile->wordcount / 100);
                    $totalfilesanalyzed++;
                    $output .= self::get_table_row($cmpfile, $index, $cmpfilescount, $slot, $config, $suspectwordsquestion, $cmpfile->wordcount);
                } else {
                    $output .= self::get_table_row($cmpfile, $index, $cmpfilescount, $slot);
                    $suspectwordsquestion = 'xx';
                }

                $questiondata[] = [
                    'question_number' => $slot,
                    'suspect_words' => $suspectwordsquestion,
                    'cmpfile' => $cmpfile,
                ];
            }
        }

        $output .= "</tbody>";

        $output .= "<tfoot class='table-group-divider cmp-bckgrnd-grey'><tr>";

        if ($totalfilesanalyzed > 0) {
            $output .= "<th class='container text-center align-middle' style='border-bottom-left-radius: 10px;'>" . get_string('total', 'plagiarism_compilatio') . "</th><td></td>";

            $globalattemptscore = round($globalattemptscore / $totalfilesanalyzed);

            $output .= "<td class='align-middle font-weight-light cmp-whitespace-nowrap'>"
                    . round($globalattemptscore * $counttotalattemptwords / 100)
                    . ' ' . get_string('word', 'plagiarism_compilatio') . ' /<br> '
                    . $counttotalattemptwords . ' ' . get_string('word', 'plagiarism_compilatio') .
                "</td>";

            $color = $globalattemptscore <= $config->warningthreshold ?? 10
                ? 'green'
                : ($globalattemptscore <= $config->criticalthreshold ?? 25
                ? 'orange'
                : 'red');

            $output .= "
                <td class='align-middle'>
                    <span class='cmp-color-{$color} font-weight-bold' style='font-size: medium;'>"
                        . $globalattemptscore  . "<small>%</small> <i class='fa fa-circle'></i>
                    </span>
                </td>";
            $output .= "<td style='border-bottom-right-radius: 10px;'></td>";
        } else {
            $output .= "<td class='font-italic font-weight-light align-middle' colspan='4' style='border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;'>"
                    . get_string('no_document_analysed', 'plagiarism_compilatio') .
                "</td>";
        }

        $output .= "</tr></tfoot></table></div>";

        return [
            'output' => $output,
            'question_data' => $questiondata ?? [],
        ];
    }

    public static function get_question_data($cmid, $user) {
        return self::get_statistics_by_student($user, $cmid)['question_data'];
    }

    /**
     * Get statistic's rows for the student selected on the dropdown selector
     *
     * @param  string $cmpfile cmpfile
     * @param  int $index index
     * @param  int $count number files in response
     * @param  int $slot question number
     * @param  string $config config
     * @param  int $suspectwordsquestion suspect words in response of this question
     * @param  int $wordcount wordcount
     * @return string       HTML containing the statistics for this student
     */

    public static function get_table_row($cmpfile, $index, $count, $slot, $config = null, $suspectwordsquestion = null, $wordcount = null) {
        global $DB, $CFG;

        $output = "<tr class='font-weight-light'>";

        $output .= $index == 0
            ? "<td rowspan='" . $count . "' class='text-center align-middle'>" . get_string('question', 'plagiarism_compilatio') . ' ' . $slot . "</td>"
            : '';

        $output .= "<td class='text-center align-middle'>";
        $output .= preg_match('~.htm$~', $cmpfile->filename)
                ? get_string('text', 'plagiarism_compilatio')
                : get_string('file', 'plagiarism_compilatio') .'<br><small>'. $cmpfile->filename .'</small>';
        $output .= "</td>";

        if ($cmpfile->status == 'scored') {
            $output .= "<td class='text-center align-middle text-nowrap'>"
                . $suspectwordsquestion . ' ' . get_string('word', 'plagiarism_compilatio') . ' / <br>' . $wordcount . ' ' . get_string('word', 'plagiarism_compilatio') . " </td>";
            $output .= "<td class='text-center text-nowrap align-middle'>". CompilatioDocumentFrame::get_score($cmpfile, $config, true, true) . "</td>";

            $output .= "<td class='align-middle'>";

            $params = [
                'docid' => $cmpfile->externalid,
                'cmid' => $cmpfile->cm,
                'type' => $config->reporttype,
            ];

            $href = "{$CFG->httpswwwroot}/plagiarism/compilatio/redirect_report.php?" . http_build_query($params);

            if (isset($cmpfile->reporturl)) {
                $href = $cmpfile->reporturl;
            }

            $output .=
                "<a href='{$href}' target='_blank' class='cmp-no-decoration'>
                    <span class='text-primary text-nowrap font-weight-bold cmp-links-color'>" . get_string('access_report', 'plagiarism_compilatio') . "</span>
                </a>";
        } else if (strpos($cmpfile->status, "error") === 0) {
            $output .= "<td colspan='3' class='align-middle'><span class='font-italic'>" . get_string('btn_' . $cmpfile->status, "plagiarism_compilatio") . " </span></td>";
        } else {
            $output .= "<td colspan='3' class='align-middle'><span class='font-italic'>" . get_string("not_analysed", 'plagiarism_compilatio') . " </span></td>";
        }

        return $output .= "</td></tr>";
    }

    /**
     * Get statistics of students for the assignment $cmid
     *
     * @param  string $cmid Course module ID
     * @return string       HTML containing the statistics per student
     */
    public static function get_quiz_students_statistics($cmid) {

        global $DB, $PAGE, $CFG;
        $compilatio = new CompilatioAPI();

        $sql = "SELECT DISTINCT {user}.id, {user}.lastname, {user}.firstname
            FROM {user}
            INNER JOIN {quiz_attempts} on {quiz_attempts}.userid = {user}.id
            INNER JOIN {quiz} ON {quiz}.id = {quiz_attempts}.quiz
            INNER JOIN {course} ON {course}.id = {quiz}.course
            INNER JOIN {course_modules} on {course_modules}.course = {course}.id
            WHERE {course_modules}.id = ?
                AND {quiz_attempts}.state = 'finished'
                AND {course_modules}.instance = {quiz}.id";

        $studentattemptsubmitted = $DB->get_records_sql($sql, [$cmid]);

        $url = $PAGE->url;
        $url->param('cmp_csv_export_per_student', true);
        $exportbutton = "<a title='" . get_string("export_csv_per_student", "plagiarism_compilatio") . "' class='cmp-icon pr-3' style='position: absolute; right: 0; top: 0;' href='". $url ."'>
                <i class='fa fa-download'></i>
            </a>";

        $export = optional_param('cmp_csv_export_per_student', '', PARAM_BOOL);
        if ($export) {
            CompilatioCsv::generate_cm_csv_per_student($cmid, $studentattemptsubmitted);
        }

        $output = "
            <div class='cmp-relative-position'>
                <h4 class='text-primary font-weight-normal'>" . get_string('results_by_student', 'plagiarism_compilatio') . "</h4>";

        if (!empty($studentattemptsubmitted)) {
            $output .= "<i id='previous-student' title='" . get_string('previous_student', 'plagiarism_compilatio') ."' class='cmp-icon-lg fa fa-chevron-left'></i>
                <select class='custom-select' id='student-select'>
                <option>". get_string('select_a_student', 'plagiarism_compilatio') . "</option>";
            foreach ($studentattemptsubmitted as $user) {
                $userlist[] = $user->id;
                $output .= '<option value="' . $user->id . '">' . $user->lastname . ' ' . $user->firstname . '</option>';
            }
            $output .= "</select>
                <i id='next-student' title='" . get_string('next_student', 'plagiarism_compilatio') . "' class='cmp-icon-lg fa fa-chevron-right'></i>
                <div class='p-3 row'>
                    <div id='statistics-container'></div>
                </div>";
        } else {
            $output .= "<h5>" . get_string('no_students_finished_quiz', 'plagiarism_compilatio') . "</h5>";
        }

        $output .= $exportbutton . "</div>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'getSelectedStudent', [$CFG->httpswwwroot, $cmid]);
        return $output;
    }
}

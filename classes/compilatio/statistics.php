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
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompilatioStatistics class
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
                course.fullname 'course',
                modules.name 'module_type',
                CONCAT(COALESCE(assign.name, ''), COALESCE(forum.name, ''), COALESCE(workshop.name, ''),
                COALESCE(quiz.name, '')) 'module_name',
                AVG(similarityscore) 'avg',
                MIN(similarityscore) 'min',
                MAX(similarityscore) 'max',
                COUNT(DISTINCT plagiarism_compilatio_file.id) 'count'
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
            $query = "SELECT usr.id 'userid', usr.firstname 'firstname', usr.lastname 'lastname'
                FROM {course} course
                JOIN {context} context ON context.instanceid= course.id
                JOIN {role_assignments} role_assignments ON role_assignments.contextid= context.id
                JOIN {user} usr ON role_assignments.userid= usr.id
                WHERE context.contextlevel=50 AND role_assignments.roleid=3 AND course.id=" . $row->id;

            $teachers = $DB->get_records_sql($query);
            $courseurl = new moodle_url('/course/view.php', ['id' => $row->id]);
            $assignurl = new moodle_url('/mod/' . $row->module_type . '/view.php', ['id' => $row->cm, 'action' => 'grading']);

            $result = [];
            if ($html) {
                $result['course'] = "<a href='$courseurl'>$row->course</a>";
                $result['assign'] = "<a href='$assignurl'>$row->module_name</a>";

            } else {
                $result['courseid'] = $row->id;
                $result['course'] = $row->course;
                $result['assignid'] = $row->cm;
                $result['assign'] = $row->module_name;
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

        $plagiarismvalues = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
        $warningthreshold = $plagiarismvalues->warningthreshold ?? 10;
        $criticalthreshold = $plagiarismvalues->criticalthreshold ?? 25;

        $from = "SELECT COUNT(DISTINCT pcf.id) FROM {plagiarism_compilatio_file} pcf WHERE pcf.cm=?";

        $documentscount = $DB->count_records_sql($from, [$cmid]);

        $countgreen = $DB->count_records_sql(
            $from . " AND status = 'scored' AND similarityscore <= $warningthreshold",
            [$cmid]
        );
        $countorange = $DB->count_records_sql(
            $from . " AND status = 'scored' AND similarityscore > $warningthreshold AND similarityscore <= $criticalthreshold",
            [$cmid]
        );
        $countred = $DB->count_records_sql(
            $from . " AND status = 'scored' AND similarityscore > $criticalthreshold",
            [$cmid]
        );

        $scorestats = $DB->get_record_sql(
            "SELECT ROUND(AVG(similarityscore)) avg, MIN(similarityscore) min, MAX(similarityscore) max
                FROM {plagiarism_compilatio_file} pcf
                WHERE pcf.cm=? AND status='scored'",
            [$cmid]
        );

        $sql = "SELECT status, COUNT(DISTINCT id) AS count FROM {plagiarism_compilatio_file}  WHERE cm = ? GROUP BY status";
        $countbystatus = $DB->get_records_sql($sql, [$cmid]);

        $output = "
            <div class='col'>
                <h4 class='cmp-color-primary'>" . get_string('progress', 'plagiarism_compilatio') . "</h4>
                <div class='position-relative cmp-box mx-2 my-3 p-3'>
                    <h5 class='fw-bold cmp-color-green'>"
                      . get_string('documents_analyzed', 'plagiarism_compilatio', $countbystatus['scored']->count ?? 0) .
                    "</h5>
                </div>

                <div class='cmp-box mx-2 my-3'>
                    <h5 class='p-3 cmp-color-primary'>"
                      . get_string('documents_analyzing', 'plagiarism_compilatio', $countbystatus['analyzing']->count ?? 0) .
                    "</h5>
                </div>

                <div class='cmp-box mx-2 my-3'>
                    <h5 class='p-3 cmp-color-primary'>"
                      . get_string('documents_in_queue', 'plagiarism_compilatio', $countbystatus['queue']->count ?? 0) .
                    "</h5>
                </div>
            </div>";

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
                    <h3 class='cmp-color-" . $color . "'>" . $scorestats->$elem . " %</h3>
                </div>";
        }

        $output .= "
            <div class='col'>
                <h4 class='cmp-color-primary'>" . get_string('results', 'plagiarism_compilatio') . "</h4>
                <div class='cmp-box mx-2 my-3 px-3 pt-3 pb-2'>
                    <h5 class='cmp-color-primary'>" . get_string('stats_score', 'plagiarism_compilatio') . "</h5>
                    <div class='row'>{$yes}</div>
                </div>

                <div class='cmp-box mx-2 my-3 p-3'>
                    <h5 class='cmp-color-primary'>" . get_string('stats_threshold', 'plagiarism_compilatio') . "</h5>
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

                $errors .= "<div class='position-relative cmp-box mx-2 my-3 p-3'>
                        <h5 class='cmp-color-red'>{$stat->count} {$shortstring}</h5>
                        <small>{$detailedstring}</small>
                    </div>";
            }
        }
        if (!empty($errors)) {
            $output .= "
                <div class='col'>
                    <h4 class='cmp-color-primary'>" . get_string('errors', 'plagiarism_compilatio') . "</h4>
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
}

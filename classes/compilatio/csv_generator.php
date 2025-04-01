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
 * csv_generator.php - Contains methods to generate CSV files.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use plagiarism_compilatio\output\statistics;

/**
 * Class to generate csv file
 */
class csv_generator {

    /**
     * Get header
     *
     * @param  string $filename File name
     * @param  string $content  CSV content
     * @return void
     */
    protected static function get_header($filename, $content) {
        $filename = preg_replace('/[\r\n]/', '', $filename);

        header('HTTP/1.1 200 OK');
        header('Date: ' . date('D M j G:i:s T Y'));
        header('Last-Modified: ' . date('D M j G:i:s T Y'));
        header('Content-Disposition: attachment;filename=' . $filename);
        if (is_callable("mb_convert_encoding")) {
            header('Content-Type: application/vnd.ms-excel');
            // Display with the right encoding for Excel PC & Excel Mac.
            print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $content, 'UTF-16LE', 'UTF-8');
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            echo "\xEF\xBB\xBF";
            echo $content;
        }
    }

    /**
     * Generates CSV file for an course module
     *
     * @param string $cmid course module id of the assignment to export
     * @param string $module type of course module
     * @return  void
     */
    public static function generate_cm_csv($cmid, $module) {
        global $DB;

        $sql = "
            SELECT DISTINCT pcf.id, pcf.filename, usr.firstname, usr.lastname,
                pcf.status, pcf.globalscore, pcf.timesubmitted
            FROM {plagiarism_compilatio_files} pcf
            JOIN {user} usr ON pcf.userid= usr.id
            WHERE pcf.cm=?";

        $files = $DB->get_records_sql($sql, [$cmid]);

        $cmpcm = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

        // Get the name of the activity in order to generate header line and the filename.
        $sql = "
            SELECT activity.name
            FROM {course_modules} cm
            JOIN {" . $module . "} activity ON cm.course = activity.course
                AND cm.instance = activity.id
            WHERE cm.id =?";

        $name = $DB->get_field_sql($sql, [$cmid]);

        $date = userdate(time());
        // Sanitize date for CSV.
        $date = str_replace(",", "", $date);
        // Create CSV first line.
        $head = '"' . $name . " - " . $date . "\",\n";
        // Sanitize filename.
        $name = preg_replace("/[^a-z0-9\.]/", "", strtolower($name));

        $filename = "compilatio_moodle_" . $name . "_" . date("Y_m_d") . ".csv";
        // Add the first line to the content : "{Name of the module} - {date}".
        $csv = $head;

        foreach ($files as $file) {

            $line = [];
            $line["lastname"]      = $file->lastname;
            $line["firstname"]     = $file->firstname;
            $line["filename"]      = $file->filename;
            $line["timesubmitted"] = date("d/m/y H:i:s", $file->timesubmitted);

            if ($file->status == "scored") {
                $line["stats_score"] = $file->globalscore;
            } else if ($file->status == "sent") {
                if ($cmpcm->analysistype == 'manual') {
                    $line["stats_score"] = get_string("manual_analysis", "plagiarism_compilatio");
                } else if ($cmpcm->analysistype == 'planned') {
                    $date = userdate($cmpcm->analysistime);
                    $line["stats_score"] = get_string("title_planned", "plagiarism_compilatio", $date);
                }
            } else {
                $line["stats_score"] = get_string("title_" . $file->status, "plagiarism_compilatio");
            }

            if ($csv === $head) {
                // Translate headers, using the key of the array as the key for translation :.
                $headers = array_keys($line);
                $headerstranslated = array_map(function($item) {
                    return get_string($item, "plagiarism_compilatio");
                }, $headers);
                $csv .= '"' . implode('","', $headerstranslated) . "\"\n";
            }

            $csv .= '"' . implode('","', $line) . "\"\n";
        }

        self::get_header($filename, $csv);

        exit(0);
    }

    /**
     * Export statistics for an activity as a csv file
     *
     * @param  int   $cmid               Activity number
     * @param  array $userssubmittedtest Users
     * @return void
     */
    public static function generate_cm_csv_per_student($cmid, $userssubmittedtest) {
        global $DB;

        // Get the name of the activity in order to generate header line and the filename.
        $sql = "
            SELECT activity.name
            FROM {course_modules} cm
            JOIN {quiz} activity ON cm.course = activity.course
                AND cm.instance = activity.id
            WHERE cm.id =?";

        $name = $DB->get_field_sql($sql, [$cmid]);

        $date = userdate(time());
        // Sanitize date for CSV.
        $date = str_replace(",", "", $date);
        // Create CSV first line.
        $head = '"' . $name . " - " . $date . "\",\n";
        // Sanitize filename.
        $name = preg_replace("/[^a-z0-9\.]/", "", strtolower($name));

        $filename = "compilatio_moodle_" . $name . "_statistics_per_students_" . date("Y_m_d") . ".csv";
        // Add the first line to the content : "{Name of the module} - {date}".
        $csv = $head;
        $line = [];
        $line["student"] = get_string('student', "plagiarism_compilatio");
        $line["question"] = get_string('question', "core");
        $line["suspectwords/totalwords"] = get_string('suspect_words/total_words', "plagiarism_compilatio");
        $line["tot"] = get_string('total', 'plagiarism_compilatio') . ' (%)';
        $line["sim"] = get_string('simscore', 'plagiarism_compilatio') . ' (%)';
        $line["utl"] = get_string('utlscore', 'plagiarism_compilatio') . ' (%)';
        $line["IA"] = get_string('aiscore', 'plagiarism_compilatio') . ' (%)';

        $csv .= '"' . implode('","', $line) . "\"\n";
        foreach ($userssubmittedtest as $user) {
            $datas = statistics::get_question_data($cmid, $user->id);
            foreach ($datas as $question) {
                $line = [];
                $line["name"] = $user->lastname . ' ' . $user->firstname;
                $line["question"] = 'Q' . $question['question_number'];
                $line["suspect/totalwords"] = $question['suspect_words'] . '/' . $question['cmpfile']->wordcount;

                $scores = ['globalscore', 'simscore', 'utlscore', 'aiscore'];

                foreach ($scores as $score) {
                    $line[get_string($score, 'plagiarism_compilatio')] =
                        $question['cmpfile']->status == 'scored' ?
                            (isset($question['cmpfile']->$score) ?
                                $question['cmpfile']->$score :
                                get_string('unmeasured', 'plagiarism_compilatio')
                            ) :
                            get_string('not_analysed', "plagiarism_compilatio");
                }

                $csv .= '"' . implode('","', $line) . "\"\n";
            }
        }

        self::get_header($filename, $csv);

        exit(0);
    }

    /**
     * Export global statistics as a csv file
     *
     * @return void
     */
    public static function generate_global_csv() {
        $rows = statistics::get_global_statistics(false);

        $filename = "compilatio_moodle_" . date("Y_m_d") . ".csv";

        $csv = "";

        $csv .= '"' . implode('","', array_keys((array) reset($rows))) . "\"\n";
        foreach ($rows as $row) {
            $csv .= '"' . implode('","', $row) . "\"\n";
        }

        self::get_header($filename, $csv);

        exit(0);
    }

    /**
     * Export raw statistics as a csv file
     *
     * @return void
     */
    public static function generate_global_raw_csv() {
        global $DB;

        $dbconfig = $DB->export_dbconfig();

        $todate = $dbconfig->dbtype == 'pgsql' ? 'to_timestamp' : 'FROM_UNIXTIME';

        $sql = "SELECT pcf.id id,
                course.id course_id,
                course.fullname course_name,
                cm module_id,
                CONCAT(COALESCE(assign.name, ''), COALESCE(forum.name, ''), COALESCE(workshop.name, ''),
                COALESCE(quiz.name, '')) module_name,
                student.id student_id,
                student.firstname student_firstname,
                student.lastname student_lastname,
                student.email student_email,
                pcf.id file_id,
                pcf.filename file_name,
                pcf.status file_status,
                pcf.globalscore file_score,
                {$todate} (pcf.timesubmitted) file_submitted_on
            FROM {plagiarism_compilatio_files} pcf
            JOIN {user} student ON pcf.userid=student.id
            JOIN {course_modules} cm ON pcf.cm = cm.id
            JOIN {modules} modules ON modules.id = cm.module
            LEFT JOIN {assign} assign ON cm.instance = assign.id AND modules.name = 'assign'
            LEFT JOIN {forum} forum ON cm.instance = forum.id AND modules.name = 'forum'
            LEFT JOIN {workshop} workshop ON cm.instance = workshop.id AND modules.name = 'workshop'
            LEFT JOIN {quiz} quiz ON cm.instance = quiz.id AND modules.name = 'quiz'
            JOIN {course} course ON cm.course= course.id
            ORDER BY cm DESC";

        $rows = $DB->get_records_sql($sql);

        $courseid = "";
        foreach ($rows as $row) {
            if ($row->course_id != $courseid) {
                $query = "SELECT teacher.id teacher_id,
                        teacher.firstname teacher_firstname,
                        teacher.lastname teacher_lastname,
                        teacher.email teacher_email
                    FROM {course} course
                    JOIN {context} context ON context.instanceid= course.id
                    JOIN {role_assignments} role_assignments ON role_assignments.contextid= context.id
                    JOIN {user} teacher ON role_assignments.userid= teacher.id
                    WHERE context.contextlevel=50
                        AND role_assignments.roleid=3
                        AND course.id=" . $row->course_id;
                $courseid = $row->course_id;
                $teachers = $DB->get_records_sql($query);
            }
            $teacherid = [];
            $teacherfirstname = [];
            $teacherlastname = [];
            $teacheremail = [];

            foreach ($teachers as $teacher) {
                array_push($teacherid, $teacher->teacher_id);
                array_push($teacherfirstname, $teacher->teacher_firstname);
                array_push($teacherlastname, $teacher->teacher_lastname);
                array_push($teacheremail, $teacher->teacher_email);
            }
            $row->teacher_id = implode(', ', $teacherid);
            $row->teacher_firstname = implode(', ', $teacherfirstname);
            $row->teacher_lastname = implode(', ', $teacherlastname);
            $row->teacher_email = implode(', ', $teacheremail);
        }

        $filename = "compilatio_moodle_" . date("Y_m_d") . ".csv";

        $csv = "";

        $header = (array) reset($rows);
        unset($header["id"]);
        $csv .= '"' . implode('","', array_keys($header)) . "\"\n";
        foreach ($rows as $row) {
            $row = (array) $row;
            unset($row["id"]);
            if ($row["file_status"] !== "scored") {
                $row["file_score"] = "";
            }
            $csv .= '"' . implode('","', $row) . "\"\n";
        }

        self::get_header($filename, $csv);

        exit(0);
    }

    /**
     * Export database data as csv files
     *
     * @return void
     */
    public static function generate_database_data_csv() {
        global $DB;

        $compilatiotables = ["cm_cfg", "files", "user"];

        $zip = new \ZipArchive();
        $zipfilename = tempnam(sys_get_temp_dir(), 'compilatio_') . '.zip';
        $zip->open($zipfilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($compilatiotables as $table) {
            $sql = "SELECT * FROM {plagiarism_compilatio_" . $table . "}";
            $rows = $DB->get_records_sql($sql);

            $filename = "compilatio_moodle_" . $table . "_" . date("Y_m_d") . ".csv";

            $csv = "";
            if (!empty($rows)) {
                $header = (array) reset($rows);
                $csv .= '"' . implode('","', array_keys($header)) . "\"\n";
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $csv .= '"' . implode('","', $row) . "\"\n";
                }
            }

            $zip->addFromString($filename, $csv);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=compilatio_data_' . date('Y_m_d') . '.zip');
        header('Content-Length: ' . filesize($zipfilename));
        readfile($zipfilename);

        unlink($zipfilename);

        exit(0);
    }
}

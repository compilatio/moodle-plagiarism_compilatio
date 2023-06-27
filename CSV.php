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
 * CSV.php - Generates a global CSV file about plagiarism in this installation
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('MOODLE_INTERNAL', true);
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
global $DB;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$url = new moodle_url('/plagiarism/compilatio/CSV.php');

$PAGE->set_url($url);


$rawcsv = optional_param('raw', 1, PARAM_BOOL);

if ($rawcsv) {
    $dbconfig = $DB->export_dbconfig();
    if ($dbconfig->dbtype == 'pgsql') {
        $todate = 'to_timestamp';
    } else {
        $todate = 'FROM_UNIXTIME';
    }
    $sql = '
        SELECT pcf.id "id",
            course.id "course_id",
            course.fullname "course_name",
            cm "module_id",
            CONCAT(COALESCE(assign.name, \'\'), COALESCE(forum.name, \'\'), COALESCE(workshop.name, \'\'),
            COALESCE(quiz.name, \'\')) "module_name",
            student.id "student_id",
            student.firstname "student_firstname",
            student.lastname "student_lastname",
            student.email "student_email",
            pcf.id "file_id",
            pcf.filename "file_name",
            pcf.status "file_status",
            pcf.globalscore "file_score",
            ' . $todate . '(pcf.timesubmitted) "file_submitted_on"
        FROM {plagiarism_compilatio_file} pcf
        JOIN {user} student ON pcf.userid=student.id
        JOIN {course_modules} cm ON pcf.cm = cm.id
        LEFT JOIN {assign} assign ON cm.instance= assign.id AND cm.module= 1
        LEFT JOIN {forum} forum ON cm.instance= forum.id AND cm.module= 9
        LEFT JOIN {workshop} workshop ON cm.instance= workshop.id AND cm.module= 23
        LEFT JOIN {quiz} quiz ON cm.instance= quiz.id AND cm.module= 17
        JOIN {course} course ON cm.course= course.id
        ORDER BY cm DESC';

    $rows = $DB->get_records_sql($sql);

    $courseid = "";
    foreach ($rows as $row) {
        if ($row->course_id != $courseid) {
            $query = '
                SELECT teacher.id "teacher_id",
                    teacher.firstname "teacher_firstname",
                    teacher.lastname "teacher_lastname",
                    teacher.email "teacher_email"
                FROM {course} course
                JOIN {context} context ON context.instanceid= course.id
                JOIN {role_assignments} role_assignments ON role_assignments.contextid= context.id
                JOIN {user} teacher ON role_assignments.userid= teacher.id
                WHERE context.contextlevel=50
                    AND role_assignments.roleid=3
                    AND course.id='. $row->course_id;
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

    header('HTTP/1.1 200 OK');
    header('Date: ' . date('D M j G:i:s T Y'));
    header('Last-Modified: ' . date('D M j G:i:s T Y'));
    header('Content-Disposition: attachment;filename=' . $filename);
    header('Content-Type: application/vnd.ms-excel');

    $return = "";

    $header = (array) reset($rows);
    unset($header["id"]);
    $return .= '"' . implode('","', array_keys($header)) . "\"\n";
    foreach ($rows as $row) {
        $row = (array) $row;
        unset($row["id"]);
        if ($row["file_status"] !== "scored") {
            $row["file_score"] = "";
        }
        $return .= '"' . implode('","', $row) . "\"\n";
    }

    print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $return, 'UTF-16LE', 'UTF-8');
} else {
    $rows = CompilatioStatistics::get_global_statistics(false);

    $filename = "compilatio_moodle_" . date("Y_m_d") . ".csv";

    header('HTTP/1.1 200 OK');
    header('Date: ' . date('D M j G:i:s T Y'));
    header('Last-Modified: ' . date('D M j G:i:s T Y'));
    header('Content-Disposition: attachment;filename=' . $filename);
    header('Content-Type: application/vnd.ms-excel');

    $return = "";

    $return .= '"' . implode('","', array_keys((array) reset($rows))) . "\"\n";
    foreach ($rows as $row) {
        $return .= '"' . implode('","', $row) . "\"\n";
    }

    print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $return, 'UTF-16LE', 'UTF-8');
}

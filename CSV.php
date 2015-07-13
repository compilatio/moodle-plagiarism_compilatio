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
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2012 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');


global $DB;

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$url = new moodle_url('/plagiarism/compilatio/CSV.php');

$PAGE->set_url($url);


$raw_csv = optional_param('raw', 1, PARAM_BOOL);

if ($raw_csv) {
    $sql = 'SELECT plagiarism_compilatio_files.id "id",
        
        course.id "course_id",
        course.fullname "course_name",
        teacher.id "teacher_id",
        teacher.firstname "teacher_firstname",
        teacher.lastname "teacher_lastname",
        teacher.email "teacher_email",
        cm "module_id",
        assign.name "module_name",
        student.id "student_id",
        student.firstname "student_firstname",
        student.lastname "student_lastname",
        student.email "student_email",
        plagiarism_compilatio_files.id "file_id",
        plagiarism_compilatio_files.filename "file_name",
        plagiarism_compilatio_files.statuscode "file_status",
        plagiarism_compilatio_files.similarityscore "file_similarityscore",
        plagiarism_compilatio_files.timesubmitted "file_submitted_on"



       FROM {plagiarism_compilatio_files} plagiarism_compilatio_files
       JOIN {user} student ON plagiarism_compilatio_files.userid=student.id
       JOIN {course_modules} course_modules ON plagiarism_compilatio_files.cm = course_modules.id
       JOIN {assign} assign ON course_modules.instance= assign.id
       JOIN {course} course ON course_modules.course= course.id
       JOIN {event} event ON assign.id=event.instance
       JOIN {user} teacher ON event.userid=teacher.id
       ORDER BY cm';

    $rows = $DB->get_records_sql($sql);

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
        $return .= '"' . implode('","', cleanRow($row)) . "\"\n";
    }

    print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $return, 'UTF-16LE', 'UTF-8');
}else{
    $rows = compilatio_get_global_statistics(false);
    
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

//Format the data for CSV export : Replacing statuscode with readable status.
function cleanRow($row) {
    $data = (array) $row;
    unset($data["id"]);

    if ($data["file_status"] !== "Analyzed") {
        $data["file_similarityscore"] = "";
        switch ($data["file_status"]) {
            case "202":
                $data["file_status"] = "Accepted";
                break;
            case "203":
                $data["file_status"] = "Analysing";
                break;
            case "415":
                $data["file_status"] = "Unsupported";
                break;
            case "416":
                $data["file_status"] = "Unextractable";
                break;
            case "413":
                $data["file_status"] = "Too large";
                break;
        }
    }
    return $data;
}

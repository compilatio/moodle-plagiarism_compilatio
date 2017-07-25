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

define('MOODLE_INTERNAL', true);
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
global $DB;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$url = new moodle_url('/plagiarism/compilatio/CSV.php');

$PAGE->set_url($url);


$rawcsv = optional_param('raw', 1, PARAM_BOOL);

if ($rawcsv) {
    $sql = '
        SELECT pcf.id "id",
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
            pcf.id "file_id",
            pcf.filename "file_name",
            pcf.statuscode "file_status",
            pcf.similarityscore "file_similarityscore",
            pcf.timesubmitted "file_submitted_on"
        FROM {plagiarism_compilatio_files} pcf
        JOIN {user} student ON pcf.userid=student.id
        JOIN {course_modules} cm ON pcf.cm = cm.id
        JOIN {assign} assign ON cm.instance= assign.id
        JOIN {course} course ON cm.course= course.id
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
        $return .= '"' . implode('","', clean_row($row)) . "\"\n";
    }

    print chr(255) . chr(254) . mb_convert_encoding("sep=,\n" . $return, 'UTF-16LE', 'UTF-8');
} else {
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

/**
 * Format the data for CSV export : Replacing statuscode with readable status
 * @param  array $row Row
 * @return array      Cleaned row
 */
function clean_row($row) {
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

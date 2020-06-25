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
 * csv_helper.php - Contains Plagiarism plugin helper methods for generate CSV files.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Helper class for generate csv file
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_helper
{

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
     * Generates CSV file for an assignment
     *
     * @param string $cmid course module id of the assignment to export
     * @return  void
     */
    public static function generate_assign_csv($cmid) {

        global $DB;

        $sql = "
            SELECT DISTINCT
                pcf.id,
                files.filename,
                usr.firstname,
                usr.lastname,
                pcf.statuscode,
                pcf.similarityscore,
                pcf.timesubmitted
            FROM {course_modules} cm
            JOIN {assign_submission} ass ON ass.assignment = cm.instance
            JOIN {files} files ON files.itemid = ass.id
            JOIN {plagiarism_compilatio_files} pcf ON pcf.identifier = files.contenthash
            JOIN {user} usr ON pcf.userid= usr.id
            WHERE cm.id=? AND pcf.cm=? AND files.filearea='submission_files'";

        $files = $DB->get_records_sql($sql, array($cmid, $cmid));

        $moduleconfig = $DB->get_records_menu('plagiarism_compilatio_config', array('cm' => $cmid), '', 'name, value');
        $analysistype = $moduleconfig["compilatio_analysistype"];

        // Get the name of the activity in order to generate header line and the filename.
        $sql = "
            SELECT assign.name
            FROM {course_modules} cm
            JOIN {assign} assign ON cm.course = assign.course
            AND cm.instance = assign.id
            WHERE cm.id =?";

        $name = "";
        $record = $DB->get_record_sql($sql, array($cmid));
        if ($record != null) {
            $name = $record->name;
        }

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

            $line = array();
            $line["lastname"]      = $file->lastname;
            $line["firstname"]     = $file->firstname;
            $line["filename"]      = $file->filename;
            $line["timesubmitted"] = date("d/m/y H:i:s", $file->timesubmitted);

            switch ($file->statuscode) {
                case COMPILATIO_STATUSCODE_COMPLETE:
                    $line["similarities"] = $file->similarityscore;
                    break;
                case COMPILATIO_STATUSCODE_UNEXTRACTABLE:
                    $line["similarities"] = get_string("unextractable", "plagiarism_compilatio");
                    break;
                case COMPILATIO_STATUSCODE_UNSUPPORTED:
                    $line["similarities"] = get_string("unsupported", "plagiarism_compilatio");
                    break;
                case COMPILATIO_STATUSCODE_ANALYSING:
                    $line["similarities"] = get_string("analysing", "plagiarism_compilatio");
                    break;
                case COMPILATIO_STATUSCODE_IN_QUEUE:
                    $line["similarities"] = get_string("queued", "plagiarism_compilatio");
                    break;
                default:
                    if ($analysistype == COMPILATIO_ANALYSISTYPE_MANUAL) {
                        $line["similarities"] = get_string("manual_analysis", "plagiarism_compilatio");
                    } else if ($analysistype == COMPILATIO_ANALYSISTYPE_PROG) {
                        $line["similarities"] = get_string("waitingforanalysis",
                                                           "plagiarism_compilatio",
                                                           userdate($moduleconfig['compilatio_timeanalyse']));
                    } else {
                        $line["similarities"] = "";
                    }
                    break;
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

}

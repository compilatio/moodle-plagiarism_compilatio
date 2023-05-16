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
 * csv.php - Contains methods to generate CSV files.
 *
 * @package    plagiarism_cmp
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class to generate csv file
 */
class CompilatioCsv {

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
                pcf.status, pcf.similarityscore, pcf.timesubmitted
            FROM {plagiarism_cmp_files} pcf
            JOIN {user} usr ON pcf.userid= usr.id
            WHERE pcf.cm=?";

        $files = $DB->get_records_sql($sql, [$cmid]);

        $cmpcm = $DB->get_record('plagiarism_cmp_module', ['cmid' => $cmid]);
        $analysistype = $cmpcm["analysistype"];

        // Get the name of the activity in order to generate header line and the filename.
        $sql = "
            SELECT activity.name
            FROM {course_modules} cm
            JOIN {" . $module . "} activity ON cm.course = activity.course
                AND cm.instance = activity.id
            WHERE cm.id =?";

        $name = "";
        $record = $DB->get_record_sql($sql, [$cmid]);
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

            $line = [];
            $line["lastname"]      = $file->lastname;
            $line["firstname"]     = $file->firstname;
            $line["filename"]      = $file->filename;
            $line["timesubmitted"] = date("d/m/y H:i:s", $file->timesubmitted);

            if ($file->status == "scored") {
                $line["similarities_rate"] = $file->similarityscore;
            } else if ($file->status == "sent") {
                if ($analysistype == 'manual') {
                    $line["similarities_rate"] = get_string("manual_analysis", "plagiarism_cmp");
                } else if ($analysistype == 'planned') {
                    $date = userdate($cmpcm->analysistime);
                    $line["similarities_rate"] = get_string("title_planned", "plagiarism_cmp", $date);
                }
            } else {
                $line["similarities_rate"] = get_string("title_" . $file->status, "plagiarism_cmp");
            }

            if ($csv === $head) {
                // Translate headers, using the key of the array as the key for translation :.
                $headers = array_keys($line);
                $headerstranslated = array_map(function($item) {
                    return get_string($item, "plagiarism_cmp");
                }, $headers);
                $csv .= '"' . implode('","', $headerstranslated) . "\"\n";
            }

            $csv .= '"' . implode('","', $line) . "\"\n";
        }

        self::get_header($filename, $csv);

        exit(0);

    }

}

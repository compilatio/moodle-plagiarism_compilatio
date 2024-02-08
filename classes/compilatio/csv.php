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
 * @package    plagiarism_compilatio
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
                pcf.status, pcf.globalscore, pcf.timesubmitted
            FROM {plagiarism_compilatio_file} pcf
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

    public static function generate_cm_csv_per_student($cmid, $userssumbitedtest) {
        global $DB;

        $cmpcm = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);

        // Get the name of the activity in order to generate header line and the filename.
        $sql = "
            SELECT activity.name
            FROM {course_modules} cm
            JOIN {" . 'quiz' . "} activity ON cm.course = activity.course
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

        $filename = "compilatio_moodle_" . $name . "_statistics_per_students_" . date("Y_m_d") . ".csv";
        // Add the first line to the content : "{Name of the module} - {date}".
        $csv = $head;
        $line = [];
        $line["eleve"] = get_string('student', "plagiarism_compilatio");
        $line["question"] = get_string('question', "plagiarism_compilatio");
        $line["suspectwords/totalwords"] = get_string('total_words_quiz_on_suspect', "plagiarism_compilatio");
        $line["tot"] = '%tot';
        $line["sim"] = '%sim';
        $line["IA"] = '%IA';
        $line["utl"] = '%UTL';

        $csv .= '"' . implode('","', $line) . "\"\n";
        $c = 0;
        foreach ($userssumbitedtest as $user) {
            $datas = CompilatioStatistics::get_question_data($cmid, $user);
            foreach ($datas as $question) {
                $line = [];
                $line["name"] = $user->lastname . ' ' . $user->firstname;
                $line["question"] = 'Q' . $question['question_number'];
                $line["suspect/totalwords"] = $question['suspect_words'] . '/' . $question['cmpfile']->wordcount;
                $line["%tot"] = $question['cmpfile']->globalscore != null ? $question['cmpfile']->globalscore : get_string('not_analysed', "plagiarism_compilatio");
                $line["%sim"] = $question['cmpfile']->similarityscore != null ? $question['cmpfile']->similarityscore : get_string('not_analysed', "plagiarism_compilatio");
                $line["*IA"] = $question['cmpfile']->utlscore != null ? $question['cmpfile']->utlscore : get_string('not_analysed', "plagiarism_compilatio");
                $line["%UTL"] = $question['cmpfile']->aiscore != null ? $question['cmpfile']->aiscore : get_string('not_analysed', "plagiarism_compilatio");

                $csv .= '"' . implode('","', $line) . "\"\n";
            }
        }

        self::get_header($filename, $csv);

        exit(0);
    }
}

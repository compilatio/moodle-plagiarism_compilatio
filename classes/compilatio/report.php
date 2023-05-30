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
 * report - Contains methods to generate CSV files.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class to get PDF report
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CompilatioReport {
    /**
     * Generates CSV file for an course module
     *
     * @param string $cmid course module id of the assignment to export
     * @param string $module type of course module
     * @return  void
     */
    public static function download_report($cmid, $docid, $reporttype = 'detailed') {

        global $DB;

        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmid]);
        $compilatio = new CompilatioAPI($userid);

        $doc = $compilatio->get_document($docid);

        $lang = substr(current_language(), 0, 2);
        $lang = in_array($lang, ['fr', 'en', 'it', 'es', 'de', 'pt']) ? $lang : 'fr';
    
        if (isset($doc->analyses->anasim->id)) {
            $filepath = $compilatio->get_pdf_report($doc->analyses->anasim->id, $lang, $reporttype);
    
            if (is_file($filepath)) {
                header('HTTP/1.1 200 OK');
                header('Date: ' . date('D M j G:i:s T Y'));
                header('Last-Modified: ' . date('D M j G:i:s T Y'));
                header('Content-Disposition: attachment;filename=' . basename($filepath));
                header('Content-Type: application/pdf');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit(0);
            }
        }
    }
}

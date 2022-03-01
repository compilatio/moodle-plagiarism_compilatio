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
 * analyses.php - Contains methods to communicate with Compilatio REST API.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2020 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
 * CompilatioAnalyses class
 * @copyright  2020 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\CompilatioService;

class CompilatioAnalyses {
    /**
     * Start an analyse
     *
     * @param  object $plagiarismfile     File
     * @return mixed                      Return true if succeed, the analyse object
     */
    public static function start_analysis($plagiarismfile) {

        global $DB, $OUTPUT;

        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

        $analyse = $compilatio->start_analyse($plagiarismfile->externalid);

        if ($analyse === true) {
            $plagiarismfile->status = "queue";
            $plagiarismfile->timesubmitted = time();

        } else if (strpos($analyse, 'No document found with id') !== false) {
            $plagiarismfile->status = "error_not_found";

        } else if (strpos($analyse, 'Document doesn\'t exceed minimum word limit') !== false) {
            $plagiarismfile->status = "error_too_short";

        } else if (strpos($analyse, 'Document exceed maximum word limit') !== false) {
            $plagiarismfile->status = "error_too_long";

        } else if (strpos($analyse, 'is not extracted, wait few seconds and retry.') !== false) {
            // Do nothing, wait for document extraction.
            return;
        } else if ($analyse == 'Error need terms of service validation') {
            return;
        } else {
            echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio') . $analyse);
            return $analyse;
        }
        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);

        return $analyse;
    }

    /**
     * Check an analysis
     *
     * @param  object $plagiarismfile    File
     * @param  bool   $manuallytriggered Manually triggered
     * @return void
     */
    public static function check_analysis($plagiarismfile, $manuallytriggered = false) {

        global $DB;

        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

        $doc = $compilatio->get_document($plagiarismfile->externalid);

        if ($doc == 'Not Found') {
            $plagiarismfile->status = "error_not_found";
        }

        if (isset($doc->analyses->anasim->state)) {
            $state = $doc->analyses->anasim->state;

            if ($state == 'running') {
                $plagiarismfile->status = "analyzing";
            } else if ($state == 'finished') {
                $scores = $doc->light_reports->anasim->scores;

                $plagiarismfile->status = "scored";
                $plagiarismfile->similarityscore = $scores->similarity_percent ?? 0;
                $plagiarismfile->reporturl = $compilatio->get_report_url($plagiarismfile->externalid);

                $emailstudents = $DB->get_field('plagiarism_compilatio_config',
                    'value',
                    array('cm' => $plagiarismfile->cm, 'name' => 'student_email'));
                if (!empty($emailstudents)) {
                    $compilatio = new plagiarism_plugin_compilatio();
                    $compilatio->compilatio_send_student_email($plagiarismfile);
                }

            } else if ($state == 'crashed' || $state == 'aborted' || $state == 'canceled') {
                $plagiarismfile->status = "error_analysis_failed";
            }
        }

        if (!$manuallytriggered) {
            $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
        }

        $DB->update_record('plagiarism_compilatio_files', $plagiarismfile);
    }
}
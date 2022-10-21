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
 * analyses.php - Contains methods to start an analysis and get the analysis result.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

/**
 * CompilatioAnalyses class
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CompilatioAnalyses {
    /**
     * Start an analyse
     *
     * @param  object $cmpfile     File
     * @return mixed Return true if succeed, the analyse object
     */
    public static function start_analysis($cmpfile) {

        global $DB, $OUTPUT;

        $userid = $DB->get_field("plagiarism_compilatio_module", "userid", array("cmid" => $cmpfile->cm));
        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'), $userid);

        $analyse = $compilatio->start_analyse($cmpfile->externalid);

        if ($analyse === true) {
            $cmpfile->status = "queue";
            $cmpfile->timesubmitted = time();

        } else if (strpos($analyse, 'No document found with id') !== false) {
            $cmpfile->status = "error_not_found";

        } else if (strpos($analyse, 'Document doesn\'t exceed minimum word limit') !== false) {
            $cmpfile->status = "error_too_short";

        } else if (strpos($analyse, 'Document exceed maximum word limit') !== false) {
            $cmpfile->status = "error_too_long";

        } else if (strpos($analyse, 'is not extracted, wait few seconds and retry.') !== false) {
            // Do nothing, wait for document extraction.
            // TODO afficher message document en cours d'extraction.
            return;
        } else if ($analyse == 'Error need terms of service validation') {
            return;
        } else {
            // TODO update this and handle extraction_error.
            echo $OUTPUT->notification(get_string('failedanalysis', 'plagiarism_compilatio') . $analyse);
            return $analyse;
        }
        $DB->update_record('plagiarism_compilatio_files', $cmpfile);

        return $analyse;
    }

    /**
     * Check an analysis
     *
     * @param  object $cmpfile    File
     * @param  bool   $manuallytriggered Manually triggered
     * @return void
     */
    public static function check_analysis($cmpfile, $manuallytriggered = false) {

        global $DB;

        $userid = $DB->get_field("plagiarism_compilatio_module", "userid", array("cmid" => $cmpfile->cm));
        $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'), $userid);

        $doc = $compilatio->get_document($cmpfile->externalid);

        if ($doc == 'Not Found') {
            $cmpfile->status = "error_not_found";
        }

        if (isset($doc->analyses->anasim->state)) {
            $state = $doc->analyses->anasim->state;

            if ($state == 'running') {
                $cmpfile->status = "analyzing";
            } else if ($state == 'finished') {
                $scores = $doc->light_reports->anasim->scores;

                $cmpfile->status = "scored";
                $cmpfile->similarityscore = $scores->similarity_percent ?? 0;

                $emailstudents = $DB->get_field('plagiarism_compilatio_module', 'studentemail', array('cmid' => $cmpfile->cm));
                if (!empty($emailstudents)) {
                    $compilatio = new plagiarism_plugin_compilatio();
                    $compilatio->compilatio_send_student_email($cmpfile);
                }

            } else if ($state == 'crashed' || $state == 'aborted' || $state == 'canceled') {
                $cmpfile->status = "error_analysis_failed";
            }
        }

        if (!$manuallytriggered) {
            $cmpfile->attempt = $cmpfile->attempt + 1;
        }

        $DB->update_record('plagiarism_compilatio_files', $cmpfile);
    }
}

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
 * analysis.php - Contains methods to start an analysis and get the analysis result.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use plagiarism_compilatio\compilatio\api;

/**
 * analysis class
 */
class analysis {
    /**
     * Start an analyse
     *
     * @param  object $cmpfile     File
     * @return mixed Return true if succeed, the analyse object
     */
    public static function start_analysis($cmpfile) {

        global $DB, $OUTPUT;

        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmpfile->cm]);
        $compilatio = new api($userid);

        $analyse = $compilatio->start_analyse($cmpfile->externalid);

        if ($analyse === true) {
            $cmpfile->status = 'queue';
            $cmpfile->timesubmitted = time();

        } else if (strpos($analyse, 'Document doesn\'t exceed minimum word limit') !== false) {
            $cmpfile->status = 'error_too_short';

        } else if (strpos($analyse, 'Document exceed maximum word limit') !== false) {
            $cmpfile->status = 'error_too_long';

        } else if (strpos($analyse, 'is not extracted, wait few seconds and retry.') !== false) {
            return get_string('extraction_in_progress', 'plagiarism_compilatio');
        } else {
            return $analyse;
        }
        $DB->update_record('plagiarism_compilatio_files', $cmpfile);

        return $cmpfile->status;
    }

    /**
     * Check an analysis
     *
     * @param  object $cmpfile File
     * @return object $cmpfile File with updated status
     */
    public static function check_analysis($cmpfile) {

        global $DB;

        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmpfile->cm]);
        $compilatio = new api($userid);

        $doc = $compilatio->get_document($cmpfile->externalid);

        if ($doc == 'Not Found') {
            $cmpfile->status = 'error_not_found';
        }

        $recipe = get_config('plagiarism_compilatio', 'recipe');

        if (isset($doc->analyses->$recipe->state)) {
            $state = $doc->analyses->$recipe->state;

            $cmpfile->analysisid ??= $doc->analyses->$recipe->id;

            if ($state == 'running') {
                $cmpfile->status = 'analysing';
            } else if ($state == 'finished') {
                $scores = $doc->light_reports->$recipe->scores;

                $cmpfile->status = 'scored';

                $cmpfile->globalscore = round($scores->global_score_percent ?? 0);

                $cmpfile->simscore = isset($scores->similarity_percent)
                    ? round($scores->similarity_percent)
                    : null;
                $cmpfile->utlscore = isset($scores->unrecognized_text_language_percent)
                    ? round($scores->unrecognized_text_language_percent)
                    : null;
                $cmpfile->aiscore = isset($scores->ai_generated_percent)
                    ? round($scores->ai_generated_percent)
                    : null;

                $scoresmapping = [
                    'ai_generated' => 'aiscore',
                    'unrecognized_text_language' => 'utlscore',
                    'exact' => 'simscore',
                ];

                $ignoredscores = [];

                foreach ($doc->light_reports->$recipe->ignored_types as $ignoredtype) {
                    isset($scoresmapping[$ignoredtype]) ? $ignoredscores[] = $scoresmapping[$ignoredtype] : null;
                }

                $cmpfile->ignoredscores = implode(',', $ignoredscores);

            } else if ($state == 'crashed' || $state == 'aborted' || $state == 'canceled') {
                $cmpfile->status = 'error_analysis_failed';
            }
        }

        $DB->update_record('plagiarism_compilatio_files', $cmpfile);

        return $cmpfile;
    }
}

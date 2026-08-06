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
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use plagiarism_compilatio\compilatio\api as compilatioApi;


/**
 * analysis class
 */
class analysis {
    /** @var array Analysis status order used to prevent status regressions. */
    private const STATUS_PRIORITY = [
        'sent' => 0,
        'to_analyze' => 0,
        'queue' => 1,
        'analysing' => 2,
        'error_not_found' => 3,
        'error_too_short' => 3,
        'error_too_long' => 3,
        'error_extraction_failed' => 3,
        'error_analysis_failed' => 3,
        'scored' => 4,
    ];

    /**
     * Start an analyse
     *
     * @param  object $cmpfile     File
     * @return object|string Return true if succeed, the analyse object
     */
    public static function start_analysis($cmpfile): object|string {

        global $DB;

        $originalcmpfile = clone $cmpfile;
        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmpfile->cm]);
        $compilatio = new compilatioApi($userid);

        $analyse = $compilatio->start_analyse($cmpfile->externalid);

        if ($analyse === true) {
            $cmpfile->status = 'queue';
            $cmpfile->timesubmitted = time();
        } else if (strpos($analyse, 'Document doesn\'t exceed minimum word limit') !== false) {
            $cmpfile->status = 'error_too_short';
        } else if (strpos($analyse, 'Document exceed maximum word limit') !== false) {
            $cmpfile->status = 'error_too_long';
        } else if (strpos($analyse, 'is not extracted, wait few seconds and retry.') !== false) {
            if (
                is_object($document = $compilatio->get_document($cmpfile->externalid))
                && in_array('extraction_error', $document->tags)
            ) {
                $cmpfile->status = 'error_extraction_failed';
            } else {
                return get_string('extraction_in_progress', 'plagiarism_compilatio');
            }
        } else if (strpos($analyse, 'is already analysed') !== false) {
            $cmpfile->status = 'queue';
        } else {
            return $analyse;
        }
        self::update_changed_fields($originalcmpfile, $cmpfile);

        return $cmpfile->status;
    }

    /**
     * Check an analysis
     *
     * @param  object $cmpfile File
     * @return object $cmpfile File with updated status, old cmpfile if get_document returns an error
     */
    public static function check_analysis($cmpfile): object {

        global $DB;

        $originalcmpfile = clone $cmpfile;
        $userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmpfile->cm]);
        $compilatio = new api($userid);

        $doc = $compilatio->get_document($cmpfile->externalid);

        if ($doc == 'Not Found') {
            $cmpfile->status = 'error_not_found';
        } else if ($doc == 'Compilation services undergoing maintenance') {
            return $cmpfile;
        }

        $recipe = array_key_first((array)$doc->analyses);

        if (isset($doc->analyses->$recipe->state)) {
            $state = $doc->analyses->$recipe->state;

            if (!isset($cmpfile->analysisid)) {
                $cmpfile->analysisid = $doc->analyses->$recipe->id;
            }

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

        self::update_changed_fields($originalcmpfile, $cmpfile);

        return $cmpfile;
    }

    /**
     * Update only fields changed while processing an analysis.
     *
     * Updating a complete record loaded before an API call can overwrite changes
     * made by another request while the API call is in progress.
     *
     * @param object $originalcmpfile File before processing
     * @param object $cmpfile File after processing
     * @return void
     */
    private static function update_changed_fields($originalcmpfile, $cmpfile) {
        global $DB;

        $record = (object) ['id' => $cmpfile->id];
        $columns = $DB->get_columns('plagiarism_compilatio_files');

        foreach (get_object_vars($cmpfile) as $field => $value) {
            if ($field === 'id' || !isset($columns[$field])) {
                continue;
            }

            if (!property_exists($originalcmpfile, $field) || $originalcmpfile->$field !== $value) {
                $record->$field = $value;
            }
        }

        if (count(get_object_vars($record)) === 1) {
            return;
        }

        if (!property_exists($record, 'status')) {
            $DB->update_record('plagiarism_compilatio_files', $record);
            return;
        }

        $setfields = [];
        $values = get_object_vars($record);
        unset($values['id']);

        foreach (array_keys($values) as $field) {
            $setfields[] = "$field = ?";
        }

        $expectedstatus = $originalcmpfile->status;
        $maxattempts = count(self::STATUS_PRIORITY) + 1;

        for ($attempt = 0; $attempt < $maxattempts; $attempt++) {
            $params = array_values($values);
            $params[] = $cmpfile->id;
            $params[] = $expectedstatus;

            $DB->execute(
                'UPDATE {plagiarism_compilatio_files}
                    SET ' . implode(', ', $setfields) . '
                  WHERE id = ? AND status = ?',
                $params
            );

            $currentstatus = $DB->get_field('plagiarism_compilatio_files', 'status', ['id' => $cmpfile->id]);

            if ($currentstatus === false || $currentstatus === $record->status) {
                return;
            }

            if (!self::can_transition_to($currentstatus, $record->status)) {
                $cmpfile->status = $currentstatus;
                return;
            }

            $expectedstatus = $currentstatus;
        }

        $cmpfile->status = $currentstatus;
    }

    /**
     * Check whether an analysis status can move forward.
     *
     * @param string $currentstatus Current database status
     * @param string $newstatus Requested status
     * @return bool
     */
    private static function can_transition_to($currentstatus, $newstatus) {
        if ($currentstatus === $newstatus) {
            return true;
        }

        if (!isset(self::STATUS_PRIORITY[$currentstatus], self::STATUS_PRIORITY[$newstatus])) {
            return false;
        }

        return self::STATUS_PRIORITY[$newstatus] > self::STATUS_PRIORITY[$currentstatus];
    }
}

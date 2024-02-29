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
 * Update similarity score state for a document
 *
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['docId']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');

global $DB;

require_login();

$cmid = required_param('cmid', PARAM_TEXT);
$checkedvalues = optional_param_array('checkedvalues', [], PARAM_TEXT);
$scores = required_param_array('scores', PARAM_TEXT);

$toremove = [];
$cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
$compilatio = new CompilatioAPI($cmconfig->userid);

foreach ($scores as $score) {
    if (!in_array($score, $checkedvalues)) {
        $toremove[] = $score;
    }
}

$files = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'status' => 'scored']);

if (in_array('similarities', $toremove)) {
    $key = array_search('similarities', $toremove);
    unset($toremove[$key]);
    $toremove[] = 'exact';
    $toremove[] = 'same_meaning';
}

$cmconfig->ignoredscores = !empty($toremove) ? implode(',', $toremove) : '';
$DB->update_record('plagiarism_compilatio_cm_cfg', (object) $cmconfig);
$ignoredtype = json_encode(['ignored_types' => array_values($toremove)]);
$docsid = [];
foreach ($files as $file) {
    $docsid[] = $file->identifier;
    $file->updatetaskid = $compilatio->update_score_as_selections($file->analysisid, $ignoredtype);
}

foreach ($files as $file) {
    $report = $compilatio->get_updated_report($file->analysisid, $file->updatetaskid);

    $file->exact_percent = $report->scores->exact_percent;
    $file->same_meaning_percent = $report->scores->same_meaning_percent;
    $file->unrecognized_text_language_percent = $report->scores->unrecognized_text_language_percent;
    $file->quotation_percent = $report->scores->quotation_percent;
    $file->reference_percent = $report->scores->reference_percent;
    $file->user_annotation_percent = $report->scores->user_annotation_percent;
    $file->mentioned_percent = $report->scores->mentioned_percent;
    $file->aiscore = $report->scores->ai_generated_percent;
    $file->simscore = $report->scores->similarity_percent;
    $file->utlscore = $report->scores->unrecognized_text_language_percent;
    $file->globalscore = $report->scores->global_score_percent;
    $file->ignoredscores = $cmconfig->ignoredscores;

    $DB->update_record('plagiarism_compilatio_files', $file);
}

echo json_encode($docsid);

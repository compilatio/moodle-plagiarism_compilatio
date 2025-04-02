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
 * Update report with ignored scores for documents of course module
 *
 * @package   plagiarism_compilatio
 * @copyright 2024 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['cmid']
 * @param   string $_POST['checkedvalues']
 * @param   string $_POST['scores']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\compilatio\analysis;

require_login();

$cmid = required_param('cmid', PARAM_TEXT);

$context = context_module::instance($cmid);
require_capability('moodle/course:manageactivities', $context);

global $DB;

$checkedvalues = optional_param_array('checkedvalues', [], PARAM_TEXT);
$scores = required_param_array('scores', PARAM_TEXT);

$cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
$compilatio = new api($cmconfig->userid);

$ignoredscores = array_diff($scores, $checkedvalues);

$files = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'status' => 'scored']);

$ignoredtypes = [];
foreach ($ignoredscores as $ignoredscore) {
    switch ($ignoredscore) {
        case 'simscore':
            $ignoredtypes[] = 'exact';
            $ignoredtypes[] = 'same_meaning';
            break;
        case 'aiscore':
            $ignoredtypes[] = 'ai_generated';
            break;
        case 'utlscore':
            $ignoredtypes[] = 'unrecognized_text_language';
            break;
    }
}

$cmconfig->ignoredscores = !empty($ignoredscores) ? implode(',', $ignoredscores) : '';

$DB->update_record('plagiarism_compilatio_cm_cfg', $cmconfig);

$ignoredtypes = json_encode(['ignored_types' => array_values($ignoredtypes)]);

foreach ($files as $file) {
    if ($file->analysisid === null) {
        $file = analysis::check_analysis($file);
    }

    $file->updatetaskid = $compilatio->update_and_rebuild_report($file->analysisid, $ignoredtypes);
}

foreach ($files as $file) {
    $report = $compilatio->get_updated_report($file->analysisid, $file->updatetaskid);

    if ($report === false) {
        continue;
    }

    $file->globalscore = round($report->scores->global_score_percent ?? 0);

    $file->simscore = isset($report->scores->similarity_percent)
        ? round($report->scores->similarity_percent)
        : null;
    $file->utlscore = isset($report->scores->unrecognized_text_language_percent)
        ? round($report->scores->unrecognized_text_language_percent)
        : null;
    $file->aiscore = isset($report->scores->ai_generated_percent)
        ? round($report->scores->ai_generated_percent)
        : null;

    $file->ignoredscores = $cmconfig->ignoredscores;

    $DB->update_record('plagiarism_compilatio_files', $file);
}

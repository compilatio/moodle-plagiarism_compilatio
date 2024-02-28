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

global $DB;

require_login();

$cmid = required_param('cmid', PARAM_TEXT);
$checkedvalues = optional_param_array('checkedvalues', [], PARAM_TEXT);
$scores = required_param_array('scores', PARAM_TEXT);

$toremove = [];
$cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
$compilatio = new CompilatioAPI($cmconfig->userid);

foreach($scores as $score){
    if(!in_array($score, $checkedvalues)){
        $toremove[] = $score;
    }
}

$files = $DB->get_records('plagiarism_compilatio_files', ['cm' => $cmid, 'status' => 'scored']);

if (in_array('similarities', $toremove)){
    $key = array_search('similarities', $toremove);
    unset($toremove[$key]);
    $toremove[] = 'exact';
    $toremove[] = 'same_meaning';
}

$cmconfig->ignoredscores = !empty($toremove) ? implode(',', $toremove) : '';
$DB->update_record('plagiarism_compilatio_cm_cfg', (object) $cmconfig);
$ignoredtype = json_encode(['ignored_types' => array_values($toremove)]);
$docsid = [];
foreach($files as $file){
    $docsid[] = $file->identifier;
    $result = $compilatio->update_score_as_selections($file->analysisid, $ignoredtype);
}
echo json_encode($docsid);

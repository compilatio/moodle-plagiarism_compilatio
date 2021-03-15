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
 * Get waiting time for analysis begins via Compilatio SOAP API
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

// Get constants.
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

require_login();

$compilatio = compilatio_get_compilatio_service(get_config('plagiarism_compilatio', 'apiconfigid'));
$reswait = $compilatio->get_waiting_time();
$maxanalysistime = 1800;
if ($reswait != false && $reswait->magister_queue + $reswait->magister_analysis_time > $maxanalysistime) {

    $waittime = new Stdclass();
    $waittime->total = format_time($reswait->magister_queue + $reswait->magister_analysis_time);
    $waittime->queue = format_time($reswait->magister_queue);
    $waittime->analysis_time = format_time($reswait->magister_analysis_time);

    echo "<div class='compilatio-alert'>
            <strong>" . get_string("waiting_time_title", "plagiarism_compilatio") . $waittime->total . "</strong><br/>" .
            get_string("waiting_time_content", "plagiarism_compilatio", $waittime)
            . get_config('plagiarism_compilatio', 'idgroupe')
            . get_string("waiting_time_content_help", "plagiarism_compilatio") .
        "</div>";
} else {
    echo false;
}

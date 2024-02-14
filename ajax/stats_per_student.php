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
 * Get stats per student
 *
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['cmid']
 * @param string $_POST['selectedstudent']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/statistics.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();

global $DB;

$selectedstudent = required_param('selectedstudent', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_TEXT);

$output = is_numeric($selectedstudent)
    ? CompilatioStatistics::get_statistics_by_student($selectedstudent, $cmid)
    : "";

echo $output;

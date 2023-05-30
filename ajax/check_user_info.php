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
 * Get Compilatio user and update his info if necessary
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['userid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

require_login();

global $DB, $USER;

$userid = required_param('userid', PARAM_RAW);

$compilatio = new CompilatioAPI();
$cmpuser = $compilatio->get_user($userid);

if (
    $cmpuser->origin == 'LMS-Moodle'
    && (
        $cmpuser->firstname !== $USER->firstname
        || $cmpuser->lastname !== $USER->lastname
        || strtolower($cmpuser->email) !== strtolower($USER->email)
    )
) {
    $compilatio->update_user($userid, $USER->firstname, $USER->lastname, $USER->email);
}

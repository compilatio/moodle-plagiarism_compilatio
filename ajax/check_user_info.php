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
 * @package   plagiarism_compilatio
 * @copyright 2025 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param string $_POST['userid']
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\compilatio\university_component;
require_login();

$cmid = required_param('cmid', PARAM_TEXT);

$context = context_module::instance($cmid);
require_capability('plagiarism/compilatio:enable', $context);

global $DB, $USER;

$compilatioid = $DB->get_field('plagiarism_compilatio_user', 'compilatioid', ['userid' => $USER->id]);

$compilatio = new api();
$cmpuser = $compilatio->get_user($compilatioid);

$compilatiouniversitycomponent = new university_component($DB);

$useruniversitycomponent = $compilatiouniversitycomponent->retreive_university_component_for_user((string) $USER->id);
$currentuniversitycomponent = $cmpuser->bundle_data[0]->university_component;
if (
    !empty($cmpuser)
    && $cmpuser->origin == 'LMS-Moodle'
    && (
        $cmpuser->firstname !== $USER->firstname
        || $cmpuser->lastname !== $USER->lastname
        || strtolower($cmpuser->email) !== strtolower($USER->email)
        || $currentuniversitycomponent !== $useruniversitycomponent
    )
) {
    $compilatio->update_user(
        $compilatioid,
        $USER->firstname,
        $USER->lastname,
        $USER->email,
        $useruniversitycomponent
    );
}

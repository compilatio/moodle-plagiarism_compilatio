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
 * Get JWT to redirect user to report
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['idDoc']
 * @return  string
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio.class.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();

$docid = required_param('docId', PARAM_RAW);

$compilatio = compilatio_get_compilatio_service(get_config('plagiarism_compilatio', 'apiconfigid'));
$jwt = $compilatio->get_report_token($docid);

if ($jwt === false) {
    echo false;
} else {
    echo "https://app.compilatio.net/api/private/reports/redirect/" . $jwt;
}

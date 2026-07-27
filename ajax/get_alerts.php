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
 * Get Compilatio alerts
 *
 * @package   plagiarism_compilatio
 * @copyright 2026 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\compilatio\alerts;

require_login();
if (isguestuser()) {
    redirect(new moodle_url('/'));
    die();
}

$userid = required_param('userid', PARAM_TEXT);
$module = required_param('module', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);
global $SESSION;

$compilatioalerts = new alerts(compilatio_retreive_user_language(), $userid, $module);
$alerts = $compilatioalerts->get($SESSION, $cmid);
$html = [];

foreach ($alerts as $index => $alert) {
    if (isset($alert['content'])) {
        $html[] = $compilatioalerts->get_alert_body($alert, $index);
        continue;
    }

    $html[] = $alert;
}

echo json_encode($html);

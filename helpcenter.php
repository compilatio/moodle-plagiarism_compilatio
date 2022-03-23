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
 * This script redirects to Compilatio helpcenter
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * It is called from assignments pages or plugin administration section
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

use plagiarism_compilatio\CompilatioService;

require_login();

// Gheck GET parameter.
$availpages = ['admin', 'teacher'];

$page = optional_param('page', 'teacher', PARAM_RAW);
$userid = optional_param('userid', null, PARAM_RAW);

if (in_array($page, $availpages) === false) {
    $page = 'teacher';
}

$compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'), $userid);
$token = $compilatio->get_zendesk_jwt();

$helpcenterpage = get_config('plagiarism_compilatio', 'helpcenter_' . $page);
$helpcenterpage = "hc/sections/360000112337";

header('Location: https://compilatio.zendesk.com/access/jwt?jwt=' . $token . "&return_to=" . urlencode($helpcenterpage));
exit;

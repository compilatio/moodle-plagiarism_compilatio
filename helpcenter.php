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
 * This script redirects to Compilatio helpcenter - It is called from assignments pages or plugin administration section
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

use plagiarism_compilatio\compilatio\api;

require_login();

global $USER;

$lang = substr(current_language(), 0, 2);
$lang = in_array($lang, ['fr', 'en', 'it', 'es', 'de', 'pt']) ? $lang : 'fr';

$userid = $DB->get_field('plagiarism_compilatio_user', 'compilatioid', ['userid' => $USER->id]);
if ($userid === false) {
    header("Location: https://support.compilatio.net/hc/" . $lang);
    exit;
}

// Check GET parameter.
$availpages = ['admin', 'teacher', 'service_status'];
$page = optional_param('page', 'teacher', PARAM_RAW);
if (in_array($page, $availpages) === false) {
    $page = 'teacher';
}

$helpcenterpage = get_config('plagiarism_compilatio', 'helpcenter_' . $page);

if ($page == 'service_status') {
    header("Location: https://support.compilatio.net/hc/{$lang}/" . $helpcenterpage);
    exit;
}

$compilatio = new api($userid);
$token = $compilatio->get_zendesk_jwt();

header('Location: https://compilatio.zendesk.com/access/jwt?jwt=' . $token . '&return_to=' . urlencode($helpcenterpage));
exit;

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
 * admin_tab_help.php - Display help links for the administrator
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2026 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\compilatio\api;

require_login();
admin_externalpage_setup('plagiarismcompilatio');
$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');
$plagiarismplugin = new plagiarism_plugin_compilatio();

echo $OUTPUT->header();

$currenttab = 'compilatiohelp';
require_once($CFG->dirroot . '/plagiarism/compilatio/admin_tabs.php');

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$compilatio = new api();
$validapikey = $compilatio->check_apikey();

if ($validapikey === true) {
    echo("<p class='mt-3'>
            <a href='helpcenter.php?page=admin' target='_blank' >
                " . get_string('admin_goto_helpcenter', 'plagiarism_compilatio') . "
                <i class='fa fa-external-link'></i>
            </a>
        </p>");
} else {
    echo(get_string('helpcenter_error', 'plagiarism_compilatio')
        . "<a href='https://support.compilatio.net/'>https://support.compilatio.net</a>");
}
echo("<p class='mt-3'>
        <a href='helpcenter.php?page=service_status' target='_blank' >
            " . get_string('goto_compilatio_service_status', 'plagiarism_compilatio') . "
            <i class='fa fa-external-link'></i>
        </a>
    </p>");
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

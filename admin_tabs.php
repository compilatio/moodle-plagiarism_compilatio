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
 * admin_tabs.php - Contains Plagiarism plugin script who create tab object in the plugin configuration web page.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$plugin = new stdClass();
require('version.php');

// Get tabs' title.
$strplagiarism         = get_string('compilatio', 'plagiarism_compilatio');
$strplagiarismdefaults = get_string('compilatiodefaults', 'plagiarism_compilatio');
$strautodiagnosis      = get_string('auto_diagnosis_title', 'plagiarism_compilatio');
$strstatistics         = get_string('tabs_title_stats', 'plagiarism_compilatio');
$strhelp               = get_string('tabs_title_help', 'plagiarism_compilatio');
$stradmintest          = get_string('tabs_title_error_management', 'plagiarism_compilatio');


// Display Compilatio logo.
echo "<img id='cmp-logo' class='mb-3' src='" . new moodle_url("/plagiarism/compilatio/pix/compilatio.png") . "'>";
echo '<div class="float-right">[ version: '. $plugin->version . ', release: ' . $plugin->release . ', instance id: '
    . substr(get_config('plagiarism_compilatio', 'instance_key'), 0, 6) . ' ]</div>';
echo '<div style="clear:both"></div>';

// Create tabs.
$tabs = [];
$tabs[] = new tabobject('course_module_settings', 'settings.php', $strplagiarism, $strplagiarism, false);
$tabs[] = new tabobject(
    'compilatiodefaults',
    'admin_tab_default_settings.php',
    $strplagiarismdefaults,
    $strplagiarismdefaults,
    false
);
$tabs[] = new tabobject('compilatioautodiagnosis', 'admin_tab_autodiagnosis.php', $strautodiagnosis, $strautodiagnosis, false);
$tabs[] = new tabobject('statistics', 'admin_tab_statistics.php', $strstatistics, $strstatistics, false);
$tabs[] = new tabobject('compilatiohelp', 'admin_tab_help.php', $strhelp, $strhelp, false);
$tabs[] = new tabobject('compilatioadmintest', 'admin_tab_error_management.php', $stradmintest, $stradmintest, false);

// Display tabs.
print_tabs([$tabs], $currenttab);

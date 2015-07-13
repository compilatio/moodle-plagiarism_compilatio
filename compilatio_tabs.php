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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

$strplagiarism = get_string('compilatio', 'plagiarism_compilatio');
$strplagiarismdefaults = get_string('compilatiodefaults', 'plagiarism_compilatio');

$strautodiagnosis = get_string('auto_diagnosis_title', 'plagiarism_compilatio');
$strstatistics = get_string('statistics_title', 'plagiarism_compilatio');

$strhelp = get_string('tabs_title_help', 'plagiarism_compilatio');

// Display Compilatio logo.
echo compilatio_get_logo();
echo '<div style="clear:both"></div>';

$tabs = array();
$tabs[] = new tabobject('compilatiosettings', 'settings.php', $strplagiarism, $strplagiarism, false);
$tabs[] = new tabobject('compilatiodefaults', 'compilatio_defaults.php', $strplagiarismdefaults, $strplagiarismdefaults, false);
$tabs[] = new tabobject('compilatioautodiagnosis', 'autodiagnosis.php', $strautodiagnosis, $strautodiagnosis, false);
$tabs[] = new tabobject('compilatiostatistics', 'statistics.php', $strstatistics, $strstatistics, false);
$tabs[] = new tabobject('compilatiohelp', 'help.php', $strhelp, $strhelp, false);
print_tabs(array($tabs), $currenttab);


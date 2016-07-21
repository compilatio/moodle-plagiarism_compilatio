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
 * help.php - Display help for the administrator
 *
 * @package   plagiarism_compilatio
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2012 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');

require_login();

admin_externalpage_setup('plagiarismcompilatio');


$context = context_system::instance();

require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$plagiarismplugin = new plagiarism_plugin_compilatio();


echo $OUTPUT->header();

$currenttab = 'compilatiohelp';
require_once('compilatio_tabs.php');

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$items = array("api");


echo "<ul>";
foreach ($items as $item) {
    echo "<li>";
    echo "<h5 class='help-title'>" . get_string("admin_help_compilatio_" . $item . "_title", "plagiarism_compilatio") . "</h5>";
    echo "<p class='help-content'>" . get_string("admin_help_compilatio_" . $item . "_content", "plagiarism_compilatio") . "</p>";
    echo "</li>";
}
echo "</ul>";

echo $OUTPUT->box_end();
echo $OUTPUT->footer();






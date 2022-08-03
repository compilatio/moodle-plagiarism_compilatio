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
 * plagiarism.php - allows the admin to configure plagiarism stuff
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

$PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'startMigration', array($CFG->httpswwwroot));

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$currenttab = 'compilatiomigrate';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo "<h3>" . get_string('migration_title', 'plagiarism_compilatio') . "</h3>";
echo "<p>" . get_string('migration_info', 'plagiarism_compilatio') . "</p>";

echo "<h5 class='compi-migration'>" . get_string('migration_form_title', 'plagiarism_compilatio') . "</h5>";
echo "<div class='form-inline'>
        <label>" . get_string('migration_apikey', 'plagiarism_compilatio') . " : </label>
        <input class='form-control m-2' type='text' id='apikey' name='apikey' required>
        <button id='compilatio-startmigration-btn' class='btn btn-primary'>" . get_string('migration_btn', 'plagiarism_compilatio') . "</button>
    </div>";

echo "<div style='display:none' id='compilatio-startmigration-info' class='alert alert-info alert-block fade in' role='alert' data-aria-autofocus='true'>
        " . get_string('migration_inprogress', 'plagiarism_compilatio') . " <i class='fa fa-spinner fa-spin fa-circle-notch'></i>
        <div id='migration-progress'>
        </div>
    </div>";

echo "<div class='compi-migration'>" . get_string('migration_support', 'plagiarism_compilatio') . "</div>";

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

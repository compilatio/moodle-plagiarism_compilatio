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

$PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'migrationState', array($CFG->httpswwwroot));

$restart = optional_param('restart', null, PARAM_RAW);
if ($restart == '1') {
    $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_message'");
    redirect('migrate.php');
}

$stop = optional_param('stop', null, PARAM_RAW);
if ($stop == '1') {
    $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_message'");
    $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_message', 'value' => "stopped"]);
    redirect('migrate.php');
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$currenttab = 'compilatiomigrate';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo "<h3>" . get_string('migration_title', 'plagiarism_compilatio') . "</h3>";
echo "<p>" . get_string('migration_info', 'plagiarism_compilatio') . "</p>";

$apikey = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_apikey'));
$message = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_message'));
if (empty($apikey) || ($message->value ?? '') == "stopped") {
    echo "<h5 class='compi-migration'>" . get_string('migration_form_title', 'plagiarism_compilatio') . "</h5>";
    echo "<div class='form-inline'>
            <label>" . get_string('migration_apikey', 'plagiarism_compilatio') . " : </label>
            <input class='form-control m-2' type='text' id='apikey' name='apikey' required>
            <button id='compilatio-startmigration-btn' class='btn btn-primary'>" . get_string('migration_btn', 'plagiarism_compilatio') . "</button>
        </div>";
}

echo "<div id='compi-migration-state'></div>";

echo "<div class='compi-migration'>" . get_string('migration_support', 'plagiarism_compilatio') . "</div>";

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

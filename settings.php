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

$mform = new compilatio_setup_form();
$plagiarismplugin = new plagiarism_plugin_compilatio();

if ($mform->is_cancelled()) {
    redirect('settings.php');
}

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $elements = ["enabled", "enable_mod_assign", "enable_mod_forum", "enable_mod_workshop", "enable_mod_quiz",
        "allow_teachers_to_show_reports", "allow_search_tab", "allow_student_analyses"];
    foreach ($elements as $elem) {
        if (!isset($data->$elem)) {
            $data->$elem = 0;
        }
    }

    foreach ($data as $field => $value) {
        // Ignore the button and API Config.
        if ($field != 'submitbutton') {
            set_config($field, $value, 'plagiarism_compilatio');
        }
    }

    // The setting compilatio_use is deprecated in Moodle 3.9+ but it must be kept for versions < 3.9 (versions < 2020061500).
    if ($CFG->version < 2020061500) {
        set_config('compilatio_use', $data->enabled, 'plagiarism');
    }

    // Set the default config for course modules if not set.
    $plagiarismdefaults = $DB->get_records('plagiarism_compilatio_config', array('cm' => 0));
    if (empty($plagiarismdefaults)) {
        $plagiarismelements = array(
                'use_compilatio' => 1,
                'compilatio_show_student_score' => 0,
                'compilatio_show_student_report' => 0,
                'compi_student_analyses' => 0,
                'compilatio_studentemail' => 0,
                'compilatio_analysistype' => 1,
                'green_threshold' => 10,
                'orange_threshold' => 25,
                'indexing_state' => 1,
            );
        foreach ($plagiarismelements as $name => $value) {
            $newelement = new Stdclass();
            $newelement->cm = 0;
            $newelement->name = $name;
            $newelement->value = $value;
            $DB->insert_record('plagiarism_compilatio_config', $newelement);
        }
    }

    cache_helper::invalidate_by_definition('core', 'config', array(), 'plagiarism_compilatio');
    compilatio_update_meta();

    redirect('settings.php');
}

echo $OUTPUT->header();
$currenttab = 'compilatiosettings';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');

$plagiarismsettings = (array) get_config('plagiarism_compilatio');
$mform->set_data($plagiarismsettings);

if (!empty($plagiarismsettings['enabled'])) {
    $compilatio = new CompilatioService(get_config('plagiarism_compilatio', 'apikey'));
    $validapikey = $compilatio->checkApikey();
    if ($validapikey === true) {
        if (!$compilatio->checkAllowStudentAnalyses()) {
            set_config('allow_student_analyses', 0, 'plagiarism_compilatio');
        }
        echo $OUTPUT->notification(get_string('enabledandworking', 'plagiarism_compilatio'), 'notifysuccess');
    } else {
        // Disable compilatio as this config isn't correct.
        set_config('enabled', 0, 'plagiarism_compilatio');
        if ($CFG->version < 2020061500) {
            set_config('compilatio_use', 0, 'plagiarism');
        }
        echo $OUTPUT->notification(get_string("saved_config_failed", "plagiarism_compilatio") . $validapikey);
    }
}

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

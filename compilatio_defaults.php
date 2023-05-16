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
 * compilatio_defaults.php - Displays default values to use inside assignments for Compilatio
 *
 * @package    plagiarism_cmp
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/cmp/lib.php');
require_once($CFG->dirroot . '/plagiarism/cmp/compilatio_form.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();

$fileid = optional_param('fileid', 0, PARAM_INT);
$resetuser = optional_param('reset', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$mform = new compilatio_defaults_form(null);

// Get the defaults - cmid(0) is the default list.
$defaultconfig = $DB->get_record('plagiarism_cmp_module', ['cmid' => 0]);
if (!empty($defaultconfig)) {
    $mform->set_data($defaultconfig);
}

echo $OUTPUT->header();
$currenttab = 'compilatiodefaults';
require_once($CFG->dirroot . '/plagiarism/cmp/compilatio_tabs.php');

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $plugin = new plagiarism_plugin_cmp();

    $data->analysistype = 'manual';

    $defaultconfig = $DB->get_record('plagiarism_cmp_module', ['cmid' => 0]);

    $newconfig = false;
    if (empty($defaultconfig)) {
        $defaultconfig = new stdClass();
        $defaultconfig->cmid = 0;
        $newconfig = true;
    }

    foreach ($plugin->config_options() as $element) {
        $defaultconfig->$element = $data->$element ?? null;
    }

    if ($newconfig) {
        $DB->insert_record('plagiarism_cmp_module', $defaultconfig);
    } else {
        $DB->update_record('plagiarism_cmp_module', $defaultconfig);
    }



    // Now set defaults.
    foreach ($plagiarismelements as $element) {
        if (isset($data->$element)) {
            if (isset($plagiarismdefaults[$element])) { // Update.
                $newelement->id = $DB->get_field('plagiarism_cmp_config', 'id', (['cm' => 0, 'name' => $element]));
                $DB->update_record('plagiarism_cmp_config', $newelement);
            } else { // Insert.
                $DB->insert_record('plagiarism_cmp_config', $newelement);
            }
        }
    }
    echo $OUTPUT->notification(get_string('defaultupdated', 'plagiarism_cmp'), 'notifysuccess');
}
echo $OUTPUT->box(get_string('defaults_desc', 'plagiarism_cmp'));

$mform->display();
echo $OUTPUT->footer();

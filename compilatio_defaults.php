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
 * @package plagiarism_compilatio
 * @author Dan Marsden <dan@danmarsden.com>
 * @copyright 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/compilatio/lib.php');
require_once('compilatio_form.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();

$fileid = optional_param('fileid', 0, PARAM_INT);
$resetuser = optional_param('reset', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$mform = new compilatio_defaults_form(null);
// Get the defaults - cmid(0) is the default list.
$plagiarismdefaults = $DB->get_records_menu('plagiarism_compilatio_config', array('cm'=>0), '', 'name, value');
if (!empty($plagiarismdefaults)) {
    $mform->set_data($plagiarismdefaults);
}
echo $OUTPUT->header();
$currenttab='compilatiodefaults';
require_once('compilatio_tabs.php');
if (($data = $mform->get_data()) && confirm_sesskey()) {
    $plagiarismplugin = new plagiarism_plugin_compilatio();
    
    $data->compilatio_analysistype = COMPILATIO_ANALYSISTYPE_MANUAL;
    
    $plagiarismelements = $plagiarismplugin->config_options();
    foreach ($plagiarismelements as $element) {
        if (isset($data->$element)) {
            $newelement = new Stdclass();
            $newelement->cm = 0;
            $newelement->name = $element;
            $newelement->value = $data->$element;
            if (isset($plagiarismdefaults[$element])) { // Update.
                $newelement->id = $DB->get_field('plagiarism_compilatio_config', 'id', (array('cm'=>0, 'name'=>$element)));
                $DB->update_record('plagiarism_compilatio_config', $newelement);
            } else { // Insert.
                $DB->insert_record('plagiarism_compilatio_config', $newelement);
            }
        }
    }
    echo $OUTPUT->notification(get_string('defaultupdated', 'plagiarism_compilatio'), 'notifysuccess');
}
echo $OUTPUT->box(get_string('defaults_desc', 'plagiarism_compilatio'));

$mform->display();
echo $OUTPUT->footer();
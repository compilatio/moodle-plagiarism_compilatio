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
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}
require_once($CFG->dirroot . '/lib/formslib.php');

class compilatio_setup_form extends moodleform {

    // Define the form.
    protected function definition() {
        global $CFG;

        $mform = & $this->_form;
        $mform->addElement('html', get_string('compilatioexplain', 'plagiarism_compilatio'));
        $mform->addElement('checkbox', 'compilatio_use', get_string('activate_compilatio', 'plagiarism_compilatio'));


        $mform->addElement('html', '<p style="font-size: 12px;font-style: italic;">' . get_string("disclaimer_data", "plagiarism_compilatio") . '</p>');


        $mform->addElement('text', 'compilatio_api', get_string('compilatioapi', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_api', 'compilatioapi', 'plagiarism_compilatio');
        $mform->addRule('compilatio_api', null, 'required', null, 'client');
        $mform->setDefault('compilatio_api', 'http://service.compilatio.net/webservices/CompilatioUserClient2.wsdl');
        $mform->setType('compilatio_api', PARAM_URL);

        $mform->addElement('passwordunmask', 'compilatio_password', get_string('compilatiopassword', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_password', 'compilatiopassword', 'plagiarism_compilatio');
        $mform->addRule('compilatio_password', null, 'required', null, 'client');

        $mform->addElement('textarea', 'compilatio_student_disclosure', get_string('students_disclosure', 'plagiarism_compilatio'), 'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('compilatio_student_disclosure', 'students_disclosure', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $mform->addElement('checkbox', 'compilatio_allow_teachers_to_show_reports', get_string("allow_teachers_to_show_reports", "plagiarism_compilatio"));
        $mform->setDefault('compilatio_allow_teachers_to_show_reports', 0);

        $mods = get_plugin_list('mod');
        foreach ($mods as $mod => $modname) {
            if (plugin_supports('mod', $mod, FEATURE_PLAGIARISM)) {
                $modstring = 'compilatio_enable_mod_' . $mod;
                $string = "";
                if (string_exists($modstring, "plagiarism_compilatio")) {
                    $string = get_string($modstring, 'plagiarism_compilatio');
                } else {
                    $string = get_string('compilatioenableplugin', 'plagiarism_compilatio', $mod);
                }
                $mform->addElement('checkbox', $modstring, $string);
            }
        }

        $this->add_action_buttons(true);
    }

}

class compilatio_defaults_form extends moodleform {

    // Define the form.
    protected function definition() {
        $mform = & $this->_form;
        compilatio_get_form_elements($mform, true);
        $this->add_action_buttons(true);
    }

}

function string_exists($string, $component) {
    return strpos(@get_string($string, $component), '[[') === false;
}

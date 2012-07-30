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
require_once($CFG->dirroot.'/lib/formslib.php');

class compilatio_setup_form extends moodleform {

    // Define the form.
    protected function definition () {
        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('html', get_string('compilatioexplain', 'plagiarism_compilatio'));
        $mform->addElement('checkbox', 'compilatio_use', get_string('usecompilatio', 'plagiarism_compilatio'));

        $mform->addElement('text', 'compilatio_api', get_string('compilatio_api', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_api', 'compilatio_api', 'plagiarism_compilatio');
        $mform->addRule('compilatio_api', null, 'required', null, 'client');
        $mform->setDefault('compilatio_api', 'https://service.compilatio.net/webservices/compilatioUserClient.wsdl');

        $mform->addElement('passwordunmask', 'compilatio_password', get_string('compilatio_password', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_password', 'compilatio_password', 'plagiarism_compilatio');
        $mform->addRule('compilatio_password', null, 'required', null, 'client');

        $mform->addElement('textarea', 'compilatio_student_disclosure', get_string('studentdisclosure', 'plagiarism_compilatio'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('compilatio_student_disclosure', 'studentdisclosure', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $this->add_action_buttons(true);
    }
}

class compilatio_defaults_form extends moodleform {

    // Define the form.
    protected function definition () {
        $mform =& $this->_form;
        compilatio_get_form_elements($mform, true);
        $this->add_action_buttons(true);
    }
}
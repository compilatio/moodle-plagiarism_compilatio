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
 * compilatio_form.php - Contains Plagiarism plugin helper methods for communicate with the web service.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Setup form class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatio_setup_form extends moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        global $CFG;

        $mform = & $this->_form;
        $mform->addElement('html', get_string('compilatioexplain', 'plagiarism_compilatio'));
        $mform->addElement('checkbox', 'compilatio_use', get_string('activate_compilatio', 'plagiarism_compilatio'));

        $mform->addElement('html', '<p style="font-size: 12px;font-style: italic;">' .
                           get_string("disclaimer_data", "plagiarism_compilatio") . '</p>');

        $mform->addElement('text', 'compilatio_api', get_string('compilatioapi', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_api', 'compilatioapi', 'plagiarism_compilatio');
        $mform->addRule('compilatio_api', null, 'required', null, 'client');
        $mform->setDefault('compilatio_api', 'https://beta.compilatio.net');
        $mform->setType('compilatio_api', PARAM_URL);

        $mform->addElement('passwordunmask', 'compilatio_password', get_string('compilatiopassword', 'plagiarism_compilatio'));
        $mform->addHelpButton('compilatio_password', 'compilatiopassword', 'plagiarism_compilatio');
        $mform->addRule('compilatio_password', null, 'required', null, 'client');

        $mform->addElement('textarea', 'compilatio_student_disclosure',
                           get_string('students_disclosure', 'plagiarism_compilatio'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('compilatio_student_disclosure', 'students_disclosure', 'plagiarism_compilatio');
        $mform->setDefault('compilatio_student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $mform->addElement('checkbox', 'compilatio_allow_teachers_to_show_reports',
                           get_string("allow_teachers_to_show_reports",
                           "plagiarism_compilatio"));
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

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio',
            'compilatio_owner_file', '', get_string('owner_file_school', 'plagiarism_compilatio'), 1);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_school_details", "plagiarism_compilatio") . '</p>');
        $radioarray[] = $mform->createElement('radio',
            'compilatio_owner_file', '', get_string('owner_file_student', 'plagiarism_compilatio'), 0);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_student_details", "plagiarism_compilatio") . '</p>');

        $mform->addGroup($radioarray, 'compilatio_owner_file', get_string('owner_file', 'plagiarism_compilatio'), array(''), false);
        $mform->setDefault('compilatio_owner_file', 1);

        $this->add_action_buttons(true);
    }

}

/**
 * Class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatio_defaults_form extends moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        $mform = & $this->_form;
        compilatio_get_form_elements($mform, true);
        $this->add_action_buttons(true);
    }

}

/**
 * Method who checks if a string exist
 *
 * @param  string $string    String
 * @param  string $component Component
 * @return mixed             Return the position of the string if succeed, false otherwise
 */
function string_exists($string, $component) {
    return strpos(@get_string($string, $component), '[[') === false;
}

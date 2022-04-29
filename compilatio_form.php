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

use plagiarism_compilatio\CompilatioService;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

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
        global $CFG, $DB;

        $mform = & $this->_form;
        $mform->addElement('html', get_string('compilatioexplain', 'plagiarism_compilatio'));
        $mform->addElement('checkbox', 'enabled', get_string('activate_compilatio', 'plagiarism_compilatio'));

        $mform->addElement('html', '<p style="font-size: 12px;font-style: italic;">' .
                           get_string("disclaimer_data", "plagiarism_compilatio") . '</p>');

        $mform->addElement('text', 'apikey', get_string('apikey', 'plagiarism_compilatio'));
        $mform->setType('apikey', PARAM_RAW);
        $mform->addHelpButton('apikey', 'apikey', 'plagiarism_compilatio');
        $mform->addRule('apikey', null, 'required', null, 'client');

        $mform->addElement('textarea', 'student_disclosure',
                           get_string('students_disclosure', 'plagiarism_compilatio'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('student_disclosure', 'students_disclosure', 'plagiarism_compilatio');
        $mform->setDefault('student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $mform->addElement('html', get_string('enable_activities_title', 'plagiarism_compilatio'));

        $mods = get_plugin_list('mod');
        foreach ($mods as $mod => $modname) {
            if (plugin_supports('mod', $mod, FEATURE_PLAGIARISM)) {
                $modstring = 'enable_mod_' . $mod;
                $string = "";
                if (string_exists($modstring, "plagiarism_compilatio")) {
                    $string = get_string($modstring, 'plagiarism_compilatio');
                }
                $mform->addElement('checkbox', $modstring, $string);
            }
        }

        $mform->addElement('html', get_string('teacher_features_title', 'plagiarism_compilatio'));

        $mform->addElement('checkbox', 'checkbox_show_reports',
                           get_string("checkbox_show_reports", "plagiarism_compilatio"));
        $mform->setDefault('checkbox_show_reports', 0);

        $apikey = get_config('plagiarism_compilatio', 'apikey');
        if (!empty($apikey)) {
            $compilatio = new CompilatioService($apikey);

            if ($compilatio->check_allow_student_analyses()) {
                $mform->addElement('checkbox', 'checkbox_student_analyses',
                    get_string("checkbox_student_analyses", "plagiarism_compilatio"));
                $mform->setDefault('checkbox_student_analyses', 0);
            }
        }

        $mform->addElement('checkbox', 'checkbox_search_tab', get_string("checkbox_search_tab", "plagiarism_compilatio"));
        $mform->setDefault('checkbox_search_tab', 0);
        $mform->addHelpButton('checkbox_search_tab', 'checkbox_search_tab', 'plagiarism_compilatio');

        $mform->addElement('checkbox', 'checkbox_analyses_auto', get_string("checkbox_analyses_auto", "plagiarism_compilatio"));
        $mform->setDefault('checkbox_analyses_auto', 0);
        $mform->addHelpButton('checkbox_analyses_auto', 'checkbox_analyses_auto', 'plagiarism_compilatio');

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_school', 'plagiarism_compilatio'), 1);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_school_details", "plagiarism_compilatio") . '</p>');
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_student', 'plagiarism_compilatio'), 0);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_student_details", "plagiarism_compilatio") . '</p>');

        $mform->addGroup($radioarray, 'owner_file', get_string('owner_file', 'plagiarism_compilatio'), array(''), false);
        $mform->setDefault('owner_file', 1);

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

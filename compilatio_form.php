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
 * @package    plagiarism_cmp
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/plagiarism/cmp/lib.php');
require_once($CFG->dirroot . '/plagiarism/cmp/classes/compilatio/api.php');

/**
 * Setup form class
 */
class compilatio_setup_form extends moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = & $this->_form;
        $mform->addElement('html', get_string('compilatioexplain', 'plagiarism_cmp'));
        $mform->addElement('checkbox', 'enabled', get_string('activate_compilatio', 'plagiarism_cmp'));

        $mform->addElement('html', '<p style="font-size: 12px;font-style: italic;">' .
                           get_string("disclaimer_data", "plagiarism_cmp") . '</p>');

        $mform->addElement('text', 'apikey', get_string('apikey', 'plagiarism_cmp'));
        $mform->setType('apikey', PARAM_RAW);
        $mform->addHelpButton('apikey', 'apikey', 'plagiarism_cmp');
        $mform->addRule('apikey', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'disable_ssl_verification', get_string("disable_ssl_verification", "plagiarism_cmp"));
        $mform->setDefault('disable_ssl_verification', 0);
        $mform->addHelpButton('disable_ssl_verification', 'disable_ssl_verification', 'plagiarism_cmp');

        $mform->addElement('textarea', 'student_disclosure',
                           get_string('students_disclosure', 'plagiarism_cmp'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('student_disclosure', 'students_disclosure', 'plagiarism_cmp');
        $mform->setDefault('student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_cmp'));

        $mform->addElement('html', get_string('enable_activities_title', 'plagiarism_cmp'));

        $mods = get_plugin_list('mod');
        foreach ($mods as $mod => $modname) {
            if (plugin_supports('mod', $mod, FEATURE_PLAGIARISM)) {
                $modstring = 'enable_mod_' . $mod;
                $string = "";
                if (string_exists($modstring, "plagiarism_cmp")) {
                    $string = get_string($modstring, 'plagiarism_cmp');
                }
                $mform->addElement('checkbox', $modstring, $string);
            }
        }

        $mform->addElement('html', get_string('teacher_features_title', 'plagiarism_cmp'));

        $mform->addElement('checkbox', 'enable_show_reports',
                           get_string("enable_show_reports", "plagiarism_cmp"));
        $mform->setDefault('enable_show_reports', 0);

        $apikey = get_config('plagiarism_cmp', 'apikey');
        if (!empty($apikey)) {
            $compilatio = new CompilatioAPI($apikey);

            if ($compilatio->check_allow_student_analyses()) {
                $mform->addElement('checkbox', 'enable_student_analyses',
                    get_string("enable_student_analyses", "plagiarism_cmp"));
                $mform->setDefault('enable_student_analyses', 0);
                $mform->addHelpButton('enable_student_analyses', 'enable_student_analyses', 'plagiarism_cmp');
            }
        }

        $mform->addElement('checkbox', 'enable_analyses_auto', get_string("enable_analyses_auto", "plagiarism_cmp"));
        $mform->setDefault('enable_analyses_auto', 0);
        $mform->addHelpButton('enable_analyses_auto', 'enable_analyses_auto', 'plagiarism_cmp');

        $mform->addElement('checkbox', 'enable_search_tab', get_string("enable_search_tab", "plagiarism_cmp"));
        $mform->setDefault('enable_search_tab', 0);
        $mform->addHelpButton('enable_search_tab', 'enable_search_tab', 'plagiarism_cmp');

        $mform->addElement('html', get_string('document_deleting', 'plagiarism_cmp'));
        $mform->addElement('checkbox', 'keep_docs_indexed', get_string("keep_docs_indexed", "plagiarism_cmp"));
        $mform->setDefault('keep_docs_indexed', 1);
        $mform->addHelpButton('keep_docs_indexed', 'keep_docs_indexed', 'plagiarism_cmp');

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_school', 'plagiarism_cmp'), 1);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_school_details", "plagiarism_cmp") . '</p>');
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_student', 'plagiarism_cmp'), 0);
        $radioarray[] = $mform->createElement('html',
            '<p style="font-size: 12px;font-style: italic;">'
            . get_string("owner_file_student_details", "plagiarism_cmp") . '</p>');

        $mform->addGroup($radioarray, 'owner_file', get_string('owner_file', 'plagiarism_cmp'), [''], false);
        $mform->setDefault('owner_file', 1);

        $this->add_action_buttons(true);
    }

}

/**
 * Class defaults form
 */
class compilatio_defaults_form extends moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        $mform = & $this->_form;
        cmp_get_form_elements($mform, true);
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

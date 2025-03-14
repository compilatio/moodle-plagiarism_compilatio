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
 * admin_forms.php - Contains plugin admin forms.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.

require_once($CFG->dirroot . '/lib/formslib.php');

use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\compilatio\course_module_settings;

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

        $mform->addElement('html', '<p><small class="font-italic">' .
                           get_string("disclaimer_data", "plagiarism_compilatio") . '</small></p>');

        $mform->addElement('text', 'apikey', get_string('apikey', 'plagiarism_compilatio'));
        $mform->setType('apikey', PARAM_RAW);
        $mform->addHelpButton('apikey', 'apikey', 'plagiarism_compilatio');
        $mform->addRule('apikey', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'disable_ssl_verification', get_string("disable_ssl_verification", "plagiarism_compilatio"));
        $mform->setDefault('disable_ssl_verification', 0);
        $mform->addHelpButton('disable_ssl_verification', 'disable_ssl_verification', 'plagiarism_compilatio');

        $mform->addElement('textarea', 'student_disclosure',
                           get_string('students_disclosure', 'plagiarism_compilatio'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('student_disclosure', 'students_disclosure', 'plagiarism_compilatio');
        $mform->setDefault('student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $mform->addElement('html', get_string('enable_activities_title', 'plagiarism_compilatio'));

        $mods = core_component::get_plugin_list('mod');
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

        $mform->addElement('checkbox', 'enable_show_reports',
                           get_string("enable_show_reports", "plagiarism_compilatio"));
        $mform->setDefault('enable_show_reports', 0);

        $apikey = get_config('plagiarism_compilatio', 'apikey');
        if (!empty($apikey)) {
            $compilatio = new api($apikey);

            if ($compilatio->check_allow_student_analyses()) {
                $mform->addElement('checkbox', 'enable_student_analyses',
                    get_string("enable_student_analyses", "plagiarism_compilatio"));
                $mform->setDefault('enable_student_analyses', 0);
                $mform->addHelpButton('enable_student_analyses', 'enable_student_analyses', 'plagiarism_compilatio');
            }
        }

        $mform->addElement('checkbox', 'enable_analyses_auto', get_string("enable_analyses_auto", "plagiarism_compilatio"));
        $mform->setDefault('enable_analyses_auto', 0);
        $mform->addHelpButton('enable_analyses_auto', 'enable_analyses_auto', 'plagiarism_compilatio');

        $mform->addElement('checkbox', 'enable_search_tab', get_string("enable_search_tab", "plagiarism_compilatio"));
        $mform->setDefault('enable_search_tab', 0);
        $mform->addHelpButton('enable_search_tab', 'enable_search_tab', 'plagiarism_compilatio');

        $mform->addElement('html', get_string('document_deleting', 'plagiarism_compilatio'));
        $mform->addElement('checkbox', 'keep_docs_indexed', get_string("keep_docs_indexed", "plagiarism_compilatio"));
        $mform->setDefault('keep_docs_indexed', 1);
        $mform->addHelpButton('keep_docs_indexed', 'keep_docs_indexed', 'plagiarism_compilatio');

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_school', 'plagiarism_compilatio'), 1);
        $radioarray[] = $mform->createElement('html',
            '<small class="font-italic mb-3">'
            . get_string("owner_file_school_details", "plagiarism_compilatio") . '</small>');
        $radioarray[] = $mform->createElement('radio',
            'owner_file', '', get_string('owner_file_student', 'plagiarism_compilatio'), 0);
        $radioarray[] = $mform->createElement('html',
            '<small class="font-italic mb-3">'
            . get_string("owner_file_student_details", "plagiarism_compilatio") . '</small>');

        $mform->addGroup($radioarray, 'owner_file', get_string('owner_file', 'plagiarism_compilatio'), [''], false);
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
        course_module_settings::get_form_elements($mform, true);
        $this->add_action_buttons(true);
    }
}

/**
 * Class
 * @copyright  2024 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatio_restart_form extends moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        $mform = & $this->_form;

        $mform->addElement('select', 'reset', get_string('selectanaction', 'core'), [
            'documents' => get_string('resend_document_in_error', 'plagiarism_compilatio'),
            'analyses' => get_string('restart_failed_analyses', 'plagiarism_compilatio'),
        ]);
        $mform->setDefault('reset', 'documents');

        $mform->addElement('date_time_selector', 'startdate', get_string('selectperiod', 'core'));
        $mform->addElement('date_time_selector', 'enddate', '');

        $this->add_action_buttons(false, get_string('go', 'core'));
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

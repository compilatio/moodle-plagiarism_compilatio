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

        // API configuration.
        $apiconfigs = $DB->get_records('plagiarism_compilatio_apicon');
        $mform->addElement('html', '<h4>' . get_string("apiconfiguration", "plagiarism_compilatio") . '</h4>');
        $mform->addElement('html', "<table class='table table-striped table-bordered table-hover' style='table-layout: fixed;'>
            <thead>
                <tr>
                    <th style='width: 8%;'>" . get_string('formenabled', 'plagiarism_compilatio') . "</th>
                    <th style='width: 23%;'>" . get_string('formurl', 'plagiarism_compilatio') . "</th>
                    <th style='width: 23%;'>" . get_string('formapikey', 'plagiarism_compilatio') . "</th>
                    <th style='width: 26%;'>" . get_string('formstartdate', 'plagiarism_compilatio') . "</th>
                    <th style='width: 10%;'>" . get_string('formcheck', 'plagiarism_compilatio') . "</th>
                    <th style='width: 10%;'>" . get_string('formdelete', 'plagiarism_compilatio') . "</th>
                </tr>
            </thead>");
        foreach ($apiconfigs as $apiconfig) {
            $mform->addElement('html', "<tr><td>");
            $mform->addElement('radio', 'apiconfigid', '', '', $apiconfig->id);
            $mform->addElement('html', "</td>
                <td style='word-wrap: break-word;'>" . $apiconfig->url . "</td>
                <td style='word-wrap: break-word;'>" . $apiconfig->api_key . "</td><td>");
            if ($apiconfig->startdate != 0) {
                $mform->addElement('html', userdate($apiconfig->startdate, '%d %B %Y'));
            }
            $mform->addElement('html', "</td><td style='text-align: center;'>");
            $quotas = compilatio_getquotas($apiconfig->id);
            if ($quotas["quotas"] == null) {
                $mform->addElement('html', "<i class='fa fa-times-circle text-danger fa-2x'></i>");
            } else {
                $mform->addElement('html', "<i class='fa fa-check-circle text-success fa-2x'></i>");
            }
            $mform->addElement('html', "</td><td style='text-align: center;'>");
            if ($DB->count_records('plagiarism_compilatio_files', array('apiconfigid' => $apiconfig->id)) == 0) {
                $mform->addElement('html', "<a href='?delete=" . $apiconfig->id . "'><i class='fa fa-trash fa-2x'></i></a>");
            }
            $mform->addElement('html', "</td></tr>");
        }

        $mform->addElement('html', "<tr><td></td><td>");
        $mform->addElement('text', 'url', '', ['class' => 'test']);
        $mform->setDefault('url', 'https://service.compilatio.net/webservices/CompilatioUserClient.wsdl');
        $mform->addHelpButton('url', 'compilatioapi', 'plagiarism_compilatio');
        $mform->setType('url', PARAM_URL);
        $mform->addElement('html', "</td><td>");
        $mform->addElement('text', 'api_key', '');
        $mform->setType('api_key', PARAM_RAW);
        $mform->addHelpButton('api_key', 'compilatiopassword', 'plagiarism_compilatio');
        $mform->addElement('html', "</td><td>");
        $mform->addElement('date_selector', 'startdate', '', array('optional' => true));
        $mform->addHelpButton('startdate', 'compilatiodate', 'plagiarism_compilatio');
        $mform->addElement('html', "<td></td><td></td></td></tr></table>");
        // API configuration.

        $mform->addElement('textarea', 'student_disclosure',
                           get_string('students_disclosure', 'plagiarism_compilatio'),
                           'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('student_disclosure', 'students_disclosure', 'plagiarism_compilatio');
        $mform->setDefault('student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_compilatio'));

        $mform->addElement('checkbox', 'allow_teachers_to_show_reports',
                           get_string("allow_teachers_to_show_reports", "plagiarism_compilatio"));
        $mform->setDefault('allow_teachers_to_show_reports', 0);

        $apiconfigid = get_config('plagiarism_compilatio', 'apiconfigid');
        if (!empty($apiconfigid)) {
            $compilatio = compilatio_get_compilatio_service($apiconfigid);

            if ($compilatio->check_allow_student_analyses()) {
                $mform->addElement('checkbox', 'allow_student_analyses',
                    get_string("allow_student_analyses", "plagiarism_compilatio"));
                $mform->setDefault('allow_student_analyses', 0);
            }
        }

        $mform->addElement('checkbox', 'allow_search_tab', get_string("allow_search_tab", "plagiarism_compilatio"));
        $mform->setDefault('allow_search_tab', 0);
        $mform->addHelpButton('allow_search_tab', 'allow_search_tab', 'plagiarism_compilatio');

        $mods = get_plugin_list('mod');
        foreach ($mods as $mod => $modname) {
            if (plugin_supports('mod', $mod, FEATURE_PLAGIARISM)) {
                if ($mod != 'quiz') {
                    $modstring = 'enable_mod_' . $mod;
                    $string = "";
                    if (string_exists($modstring, "plagiarism_compilatio")) {
                        $string = get_string($modstring, 'plagiarism_compilatio');
                    } else {
                        $string = get_string('compilatioenableplugin', 'plagiarism_compilatio', $mod);
                    }
                    $mform->addElement('checkbox', $modstring, $string);
                }
            }
        }

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
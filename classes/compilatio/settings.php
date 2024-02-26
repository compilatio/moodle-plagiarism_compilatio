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
 * settings.php - Contains methods to display and save settings.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

/**
 * CompilatioSettings class
 */
class CompilatioSettings {
    /**
     * Save Compilatio settings from a course module settings page
     *
     * @param stdClass $data
     * @param stdClass $course
     */
    public static function save_course_module_settings($data, $course) {
        global $DB, $USER;
        $plugin = new plagiarism_plugin_compilatio();
        if (!$plugin->get_settings()) {
            return $data;
        }

        if (isset($data->activated)) {
            // First get existing values.
            $cmconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $data->coursemodule]);

            $newconfig = false;
            if (empty($cmconfig)) {
                $newconfig = true;
                $cmconfig = new stdClass();
                $cmconfig->cmid = $data->coursemodule;
            }

            if ($data->activated === '1') {
                // Validation on thresholds.
                if (!isset($data->warningthreshold, $data->criticalthreshold) ||
                    $data->warningthreshold > $data->criticalthreshold ||
                    $data->warningthreshold > 100 || $data->warningthreshold < 0 ||
                    $data->criticalthreshold > 100 || $data->criticalthreshold < 0
                ) {
                    $data->warningthreshold = 10;
                    $data->criticalthreshold = 25;
                }

                if (get_config('plagiarism_compilatio', 'enable_show_reports') !== '1') {
                    $data->showstudentreport = 'never';
                }

                if ($newconfig || (!isset($cmconfig->userid) && $cmconfig->activated === '0')) {
                    $user = $DB->get_record('plagiarism_compilatio_user', ['userid' => $USER->id]);

                    if (empty($user)) {
                        $compilatio = new CompilatioAPI();
                        $user = $compilatio->get_or_create_user();
                    }

                    $cmconfig->userid = $user->compilatioid;

                    $compilatio = new CompilatioAPI($user->compilatioid);

                    if (isset($user->id) && ($data->termsofservice ?? false)) {
                        $user->validatedtermsofservice = true;
                        $DB->update_record('plagiarism_compilatio_user', $user);

                        $compilatio->validate_terms_of_service();
                    }
                }

                if (isset($cmconfig->userid)) {
                    $compilatio ??= new CompilatioAPI($cmconfig->userid);

                    // Get Datetime for Compilatio folder.
                    $date = new DateTime();
                    $date->setTimestamp($data->analysistime);
                    $analysistime = $date->format('Y-m-d H:i:s');

                    if ($newconfig || (!isset($cmconfig->folderid) && $cmconfig->activated === '0')) {
                        $folderid = $compilatio->set_folder(
                            $data->name,
                            $data->defaultindexing,
                            $data->analysistype,
                            $analysistime,
                            $data->warningthreshold,
                            $data->criticalthreshold
                        );
                        if (compilatio_valid_md5($folderid)) {
                            $cmconfig->folderid = $folderid;
                        }
                    } else {
                        $compilatio->update_folder(
                            $cmconfig->folderid,
                            $data->name,
                            $data->defaultindexing,
                            $data->analysistype,
                            $analysistime,
                            $data->warningthreshold,
                            $data->criticalthreshold
                        );
                    }
                }

                foreach ($plugin->config_options() as $element) {
                    $cmconfig->$element = $data->$element ?? null;
                }
            } else {
                $cmconfig->activated = 0;
            }

            if (get_config('plagiarism_compilatio', 'read_only_apikey') === '1') {
                return $data;
            }

            if ($newconfig) {
                $DB->insert_record('plagiarism_compilatio_cm_cfg', $cmconfig);
            } else {
                $DB->update_record('plagiarism_compilatio_cm_cfg', $cmconfig);
            }
        }
        return $data;
    }

    /**
     * Add Compilatio settings to a course module settings page
     *
     * @param moodleform $formwrapper
     * @param MoodleQuickForm $mform
     */
    public static function display_course_module_settings($formwrapper, $mform) {
        global $DB, $USER;

        $plugin = new plagiarism_plugin_compilatio();
        $plagiarismsettings = $plugin->get_settings();
        if (!$plagiarismsettings) {
            return;
        }
        // Hack to prevent this from showing on custom compilatioassignment type.
        if ($mform->elementExists('seuil_faible')) {
            return;
        }

        $cmid = null;
        if ($cm = $formwrapper->get_coursemodule()) {
            $cmid = $cm->id;
        }
        $matches = [];
        if (!preg_match('/^mod_([^_]+)_mod_form$/', get_class($formwrapper), $matches)) {
            return;
        }
        $modulename = 'mod_' . $matches[1];
        $modname = 'enable_' . $modulename;
        if (empty($plagiarismsettings[$modname])) {
            return;
        }
        $context = context_course::instance($formwrapper->get_course()->id);

        $defaultconfig = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => 0]);
        if (!empty($cmid)) {
            $config = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
        }

        $plagiarismelements = $plugin->config_options();

        if (has_capability('plagiarism/compilatio:enable', $context)) {
            $needtermsofservice = false;

            $cmpuser = $DB->get_record('plagiarism_compilatio_user', ['userid' => $USER->id]);
            if (empty($cmpuser) || $cmpuser->validatedtermsofservice == 0) {
                $needtermsofservice = true;
            }

            // Plugin v2 docs management.
            if (!empty($config) && null === $config->userid && $config->activated === '1') {
                $needtermsofservice = false;
            }

            if (!empty($config->userid)) {
                $teacheremail = $DB->get_field('user', 'email', ['id' => $cmpuser->userid]);
            }

            self::get_form_elements($mform, false, $needtermsofservice, $modulename, $teacheremail ?? null);

            // Disable all plagiarism elements if activated eg 0.
            foreach ($plagiarismelements as $element) {
                if ($element != 'activated') {
                    $mform->disabledIf($element, 'activated', 'eq', 0);
                }
            }
        } else { // Add plagiarism settings as hidden vars.
            foreach ($plagiarismelements as $element) {
                $mform->addElement('hidden', $element);
            }
        }

        foreach ($plagiarismelements as $element) {
            $mform->setDefault($element, $config->$element ?? $defaultconfig->$element);
        }
    }

    /**
     * Adds the list of plagiarism settings to a form.
     *
     * @param object  $mform    Moodle form object
     * @param boolean $defaults if this is being loaded from defaults form or from inside a mod.
     * @param boolean $needtermsofservice
     * @param string  $modulename
     * @param string  $teacheremail
     */
    public static function get_form_elements($mform, $defaults = false, $needtermsofservice = false, $modulename = null, $teacheremail = null) {
        global $PAGE, $CFG, $DB, $USER;

        $lang = substr(current_language(), 0, 2);

        $ynoptions = [
            0 => get_string('no'),
            1 => get_string('yes'),
        ];

        $mform->addElement('header', 'plagiarismdesc', get_string('compilatio', 'plagiarism_compilatio'));

        if ($modulename === 'mod_quiz') {
            $minword = get_config('plagiarism_compilatio', 'min_word');
            $mform->addElement('html', '<p><b>' . get_string('quiz_help', 'plagiarism_compilatio', $minword) . '</b></p>');
        }

        $mform->addElement('select', 'activated', get_string('activated', 'plagiarism_compilatio'), $ynoptions);
        $mform->setDefault('activated', 1);

        $group = [];
        $infostring = isset($teacheremail)
            ? get_string('info_cm_activated', 'plagiarism_compilatio', $teacheremail)
            : get_string('info_cm_activation', 'plagiarism_compilatio', $USER->email);
        $group[] = $mform->createElement('html', "<p>{$infostring}</p>");
        $mform->addGroup($group, 'info_cm', '', ' ', false);
        $mform->hideIf('info_cm', 'activated', 'eq', '0');

        $termsofservice = 'https://app.compilatio.net/api/private/terms-of-service/magister/' . $lang;
        if ($needtermsofservice) {
            $mform->addElement(
                'checkbox',
                'termsofservice',
                get_string('terms_of_service', 'plagiarism_compilatio', $termsofservice)
            );
            $mform->setDefault('termsofservice', 0);
            $mform->addRule('termsofservice', null, 'required', null, 'client');

            $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_form', 'requiredTermsOfService');
        } else {
            $group = [];
            $group[] = $mform->createElement(
                'html',
                '<p>' . get_string('terms_of_service_info', 'plagiarism_compilatio', $termsofservice) . '</p>'
            );
            $mform->addGroup($group, 'tos_info', '', ' ', false);
            $mform->hideIf('tos_info', 'activated', 'eq', '0');
        }

        $analysistypes = [
            'manual' => get_string('analysistype_manual', 'plagiarism_compilatio'),
            'planned' => get_string('analysistype_prog', 'plagiarism_compilatio'),
        ];

        if (get_config('plagiarism_compilatio', 'enable_analyses_auto') == '1') {
            $analysistypes['auto'] = get_string('analysistype_auto', 'plagiarism_compilatio');
            $help = 'analysistype_auto';
        }

        if (!$defaults) { // Only show this inside a module page - not on default settings pages.
            $mform->addElement('select', 'analysistype', get_string('analysistype', 'plagiarism_compilatio'), $analysistypes);
            $mform->addHelpButton('analysistype', $help ?? 'analysistype', 'plagiarism_compilatio');
            $mform->setDefault('analysistype', 'manual');
        }

        if (!$defaults) { // Only show this inside a module page - not on default settings pages.
            $mform->addElement(
                'date_time_selector',
                'analysistime',
                get_string('analysis_date', 'plagiarism_compilatio'),
                ['optional' => false]
            );
            $mform->setDefault('analysistime', time() + 7 * 24 * 3600);
            $mform->disabledif('analysistime', 'analysistype', 'noteq', 'planned');

            if ($lang == 'fr') {
                $group = [];
                $group[] = $mform->createElement(
                    'static',
                    'calendar',
                    '',
                    "<img style='width: 40em;' src='" . new moodle_url('/plagiarism/compilatio/pix/affluence_calendar.png') . "'>"
                );
                $mform->addGroup($group, 'calendargroup', '', ' ', false);
                $mform->hideIf('calendargroup', 'analysistype', 'noteq', 'planned');
            }
        }

        $showoptions = [
            'never' => get_string('never'),
            'immediately' => get_string('immediately', 'plagiarism_compilatio'),
            'closed' => get_string('showwhenclosed', 'plagiarism_compilatio'),
        ];

        $mform->addElement('select', 'showstudentscore', get_string('showstudentscore', 'plagiarism_compilatio'), $showoptions);
        $mform->addHelpButton('showstudentscore', 'showstudentscore', 'plagiarism_compilatio');

        if (get_config('plagiarism_compilatio', 'enable_show_reports') === '1') {
            $mform->addElement(
                'select',
                'showstudentreport',
                get_string('showstudentreport', 'plagiarism_compilatio'),
                $showoptions
            );
            $mform->addHelpButton('showstudentreport', 'showstudentreport', 'plagiarism_compilatio');

            $reporttypes = [
                'detailed' => get_string('detailed', 'plagiarism_compilatio'),
                'certificate' => get_string('certificate', 'plagiarism_compilatio'),
            ];
            $mform->addElement('select', 'reporttype', get_string('reporttype', 'plagiarism_compilatio'), $reporttypes);
            $mform->addHelpButton('reporttype', 'reporttype', 'plagiarism_compilatio');
            $mform->setDefault('reporttype', 'detailed');
            if (!$defaults) {
                $mform->hideIf('reporttype', 'showstudentreport', 'eq', 'never');
            }
        } else {
            $mform->addElement('html', '<p>' . get_string('admin_disabled_reports', 'plagiarism_compilatio') . '</p>');
        }

        if (get_config('plagiarism_compilatio', 'enable_student_analyses') === '1' && !$defaults) {
            if ($mform->elementExists('submissiondrafts')) {
                $mform->addElement(
                    'select',
                    'studentanalyses',
                    get_string('studentanalyses', 'plagiarism_compilatio'),
                    $ynoptions
                );
                $mform->addHelpButton('studentanalyses', 'studentanalyses', 'plagiarism_compilatio');

                $mform->disabledif('studentanalyses', 'submissiondrafts', 'eq', '0');

                $group = [];
                $group[] = $mform->createElement('html', "<p class='text-danger'>" .
                    get_string(
                        'activate_submissiondraft',
                        'plagiarism_compilatio',
                        get_string('submissiondrafts', 'assign')
                    ) .
                    " <b>" . get_string('submissionsettings', 'assign') . ".</b></p>");
                $mform->addGroup($group, 'activatesubmissiondraft', '', ' ', false);
                $mform->hideIf('activatesubmissiondraft', 'submissiondrafts', 'eq', '1');
            }
        }

        // Indexing state.
        $mform->addElement('select', 'defaultindexing', get_string('defaultindexing', 'plagiarism_compilatio'), $ynoptions);
        $mform->addHelpButton('defaultindexing', 'defaultindexing', 'plagiarism_compilatio');
        $mform->setDefault('defaultindexing', 1);

        // Threshold settings.
        $mform->addElement('html', '<p><strong>' . get_string('thresholds_settings', 'plagiarism_compilatio') . '</strong></p>');
        $mform->addElement('html', '<p>' . get_string('thresholds_description', 'plagiarism_compilatio') . '</p>');

        $mform->addElement('html', '<div>');
        $mform->addElement(
            'text',
            'warningthreshold',
            get_string('green_threshold', 'plagiarism_compilatio'),
            "size='5' id='warningthreshold'"
        );
        $mform->addElement('html', '<noscript>' . get_string('similarity_percent', 'plagiarism_compilatio') . '</noscript>');

        $mform->addElement(
            'text',
            'criticalthreshold',
            get_string('orange_threshold', 'plagiarism_compilatio'),
            "size='5' id='criticalthreshold'"
        );

        $mform->addElement('html', '<noscript>' .
            get_string('similarity_percent', 'plagiarism_compilatio') .
            ', ' . get_string('red_threshold', 'plagiarism_compilatio') .
            '</noscript>');
        $mform->addElement('html', '</div>');

        // Max file size / min words / max words.
        $size = (get_config('plagiarism_compilatio', 'max_size') / 1024 / 1024);
        $mform->addElement('html', '<p>' . get_string('max_file_size', 'plagiarism_compilatio', $size) . '</p>');

        $word = new stdClass();
        $word->max = get_config('plagiarism_compilatio', 'max_word');
        $word->min = get_config('plagiarism_compilatio', 'min_word');
        $mform->addElement('html', '<p>' . get_string('word_limits', 'plagiarism_compilatio', $word) . '</p>');

        // File types allowed.
        $filetypes = json_decode(get_config('plagiarism_compilatio', 'file_types'));
        $filetypesstring = '';
        foreach ($filetypes as $type => $value) {
            $filetypesstring .= $type . ', ';
        }
        $filetypesstring = substr($filetypesstring, 0, -2);
        $mform->addElement(
            'html',
            '<div>' . get_string('help_compilatio_format_content', 'plagiarism_compilatio') . $filetypesstring . '</div>'
        );

        // Used to append text nicely after the inputs.
        $strsimilaritypercent = get_string('similarity_percent', 'plagiarism_compilatio');
        $strredtreshold = get_string('red_threshold', 'plagiarism_compilatio');
        $PAGE->requires->js_call_amd(
            'plagiarism_compilatio/compilatio_form',
            'afterPercentValues',
            [$strsimilaritypercent, $strredtreshold]
        );

        // Numeric validation for Thresholds.
        $mform->addRule('warningthreshold', get_string('numeric_threshold', 'plagiarism_compilatio'), 'numeric', null, 'client');
        $mform->addRule('criticalthreshold', get_string('numeric_threshold', 'plagiarism_compilatio'), 'numeric', null, 'client');

        $mform->setType('warningthreshold', PARAM_INT);
        $mform->setType('criticalthreshold', PARAM_INT);

        $mform->setDefault('warningthreshold', '10');
        $mform->setDefault('criticalthreshold', '25');
    }

    public static function get_options_score_analyse($cmid) {

        Global $DB;

        $ignoredscore = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $cmid]);
        $recipe = get_config('plagiarism_compilatio', 'recipe');

        $output = get_string('include_in_suspecte_text_percentage', 'plagiarism_compilatio') . "
            <div class='form-check mt-2 ml-3'>
                <input class='form-check-input' type='checkbox' value='' id='optionscoresimilarities'>
                <label class='form-check-label' for='defaultCheck1'>
                    " . get_string('similarities_percentage', 'plagiarism_compilatio') . "
                </label>
            </div>
            <div class='form-check mt-2 ml-3'>
                <input class='form-check-input' type='checkbox' value='' id='optionscoreutl'>
                <label class='form-check-label' for='defaultCheck1'>
                    " . get_string('utl_percentage', 'plagiarism_compilatio') . "
                </label>
            </div>
        ";
        $output .= 
            $recipe === "anasim-premium" 
                ? "
                    <div class='form-check mt-2 ml-3'>
                        <input class='form-check-input' type='checkbox' value='' id='optionscoreia'>
                        <label class='form-check-label' for='defaultCheck1'>
                            " . get_string('ia_percentage', 'plagiarism_compilatio') . "
                        </label>
                    </div> " 
                : "";

        $output .= "<p class='font-weight-lighter font-italic mt-4'>" . get_string('options_score_informations', 'plagiarism_compilatio') . "</p>
            <div class='d-flex flex-row-reverse mr-1'>
                <button type='button' class='btn btn-primary'>" . get_string('update', 'plagiarism_compilatio') . "</button>
            </div>";
        return $output;
    }
}

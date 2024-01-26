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
 * settings.php - allows the admin to configure plugin global settings
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use plagiarism_compilatio\task\update_meta;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

$mform = new compilatio_setup_form();
$plagiarismplugin = new plagiarism_plugin_compilatio();

if ($mform->is_cancelled()) {
    redirect('settings.php');
}

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $elements = [
        'enabled',
        'enable_mod_assign',
        'enable_mod_forum',
        'enable_mod_workshop',
        'enable_mod_quiz',
        'enable_show_reports',
        'enable_search_tab',
        'enable_student_analyses',
        'enable_analyses_auto',
        'disable_ssl_verification',
        'keep_docs_indexed',
    ];

    foreach ($elements as $elem) {
        if (!isset($data->$elem)) {
            $data->$elem = 0;
        }
    }

    foreach ($data as $field => $value) {
        // Ignore the button and API Config.
        if ($field != 'submitbutton') {
            set_config($field, $value, 'plagiarism_compilatio');
        }
    }

    // The setting compilatio_use is deprecated in Moodle 3.9+ but it must be kept for versions < 3.9 (versions < 2020061500).
    if ($CFG->version < 2020061500) {
        set_config('compilatio_use', $data->enabled, 'plagiarism');
    }

    // Set the default config for course modules if not set.
    $plagiarismdefaults = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => 0]);
    if (empty($plagiarismdefaults)) {
        $defaultconfig = new stdClass();
        $defaultconfig->cmid = 0;
        $defaultconfig->activated = 1;
        $defaultconfig->showstudentscore = 'never';
        $defaultconfig->showstudentreport = 'never';
        $defaultconfig->studentanalyses = 0;
        $defaultconfig->analysistype = 'manual';
        $defaultconfig->warningthreshold = 10;
        $defaultconfig->criticalthreshold = 25;
        $defaultconfig->defaultindexing = 1;
        $DB->insert_record('plagiarism_compilatio_cm_cfg', $defaultconfig);
    }

    cache_helper::invalidate_by_definition('core', 'config', [], 'plagiarism_compilatio');
    $updatemeta = new update_meta();
    $updatemeta->execute();

    redirect('settings.php');
}

echo $OUTPUT->header();
$currenttab = 'compilatiosettings';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');

$plagiarismsettings = (array) get_config('plagiarism_compilatio');
$mform->set_data($plagiarismsettings);

if (!empty($plagiarismsettings['enabled'])) {
    $compilatio = new CompilatioAPI();
    $validapikey = $compilatio->check_apikey();

    if (get_config('plagiarism_compilatio', 'read_only_apikey') === '1') {
        echo $OUTPUT->notification(get_string('read_only_apikey', 'plagiarism_compilatio'));
    }

    if ($validapikey === true) {
        if (!$compilatio->check_allow_student_analyses()) {
            set_config('enable_student_analyses', 0, 'plagiarism_compilatio');
        }

        $subscription = $compilatio->get_subscription_info();

        $subscriptioninfos = '';
        $subscriptioninfos .= '<li>' . get_string('subscription_start', 'plagiarism_compilatio') . ' '
            . compilatio_format_date($subscription->validity_period->start) . '</li>';
        $subscriptioninfos .= '<li>' . get_string('subscription_end', 'plagiarism_compilatio') . ' '
            . compilatio_format_date($subscription->validity_period->end) . '</li>';

        if (isset($subscription->quotas)) {
            foreach ($subscription->quotas as $quota) {
                if (($quota->blocking === false && $quota->resource === 'analysis_count') ||
                    ($quota->blocking === true && $quota->resource === 'analysis_page_count')) {

                    $subscriptioninfos .= '<li>'
                            . get_string('subscription_' . $quota->resource, 'plagiarism_compilatio', $quota) .
                        '</li>';
                }
            }
        }

        echo $OUTPUT->notification(
            '<p>' . get_string('enabledandworking', 'plagiarism_compilatio') . '</p>'
            . get_string('subscription', 'plagiarism_compilatio') . "<ul class='m-0'>" . $subscriptioninfos . '</ul>',
            'notifysuccess'
        );        
    } else {  
        if ($validapikey == 'Forbidden'){
            echo $OUTPUT->notification(get_string('wrong_apikey_type', 'plagiarism_compilatio'));
        } else {
            echo $OUTPUT->notification(get_string('saved_config_failed', 'plagiarism_compilatio') . ' ' . $validapikey);
        } 
        // Disable compilatio as this config isn't correct.
        set_config('enabled', 0, 'plagiarism_compilatio');
    }
}

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

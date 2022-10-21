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
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_compilatio
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2012 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$deleteconfig = optional_param('delete', 0, PARAM_INT);
if ($deleteconfig) {
    $DB->delete_records('plagiarism_compilatio_apicon', array('id' => $deleteconfig));
}

$mform = new compilatio_setup_form();
$plagiarismplugin = new plagiarism_plugin_compilatio();

if ($mform->is_cancelled()) {
    redirect('settings.php');
}
// Boolean to test only once the connection if it has failed.
$incorrectconfig = false;

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $elements = [
        "enabled",
        "enable_mod_assign",
        "enable_mod_forum",
        "enable_mod_workshop",
        "enable_mod_quiz",
        "allow_teachers_to_show_reports",
        "allow_search_tab",
        "allow_student_analyses",
        "allow_analyses_auto",
        "disable_ssl_verification",
        "keep_docs_indexed"
    ];
    foreach ($elements as $elem) {
        if (!isset($data->$elem)) {
            $data->$elem = 0;
        }
    }

    foreach ($data as $field => $value) {
        // Ignore the button and API Config.
        if ($field != 'submitbutton' && $field != 'url' && $field != 'startdate' && $field != 'api_key') {
            set_config($field, $value, 'plagiarism_compilatio');
        }
    }

    if (!empty($data->url) && !empty($data->api_key)) {
        $apiconfig = new stdclass();
        $apiconfig->startdate = $data->startdate;
        $apiconfig->url = rtrim($data->url, '/'); // Strip trailing slash from api.
        $apiconfig->api_key = $data->api_key;

        $apiconfigid = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);
        if ($data->startdate == 0) {
            set_config('apiconfigid', $apiconfigid, 'plagiarism_compilatio');
        }
    }

    // The setting compilatio_use is deprecated in Moodle 3.9+ but it must be kept for versions < 3.9 (versions < 2020061500).
    if ($CFG->version < 2020061500) {
        set_config('compilatio_use', $data->enabled, 'plagiarism');
    }

    // Set the default config for course modules if not set.
    $plagiarismdefaults = $DB->get_records('plagiarism_compilatio_config', array('cm' => 0));
    if (empty($plagiarismdefaults)) {
        $plagiarismelements = array(
                'use_compilatio' => 1,
                'compilatio_show_student_score' => 0,
                'compilatio_show_student_report' => 0,
                'compi_student_analyses' => 0,
                'compilatio_studentemail' => 0,
                'compilatio_analysistype' => 1,
                'green_threshold' => 10,
                'orange_threshold' => 25,
                'indexing_state' => 1,
            );
        foreach ($plagiarismelements as $name => $value) {
            $newelement = new Stdclass();
            $newelement->cm = 0;
            $newelement->name = $name;
            $newelement->value = $value;
            $DB->insert_record('plagiarism_compilatio_config', $newelement);
        }
    }

    cache_helper::invalidate_by_definition('core', 'config', array(), 'plagiarism_compilatio');
    // TODO - check settings to see if valid.
    $error = '';
    $quotas = compilatio_getquotas();

    if ($quotas["quotas"] == null) {
        // Disable compilatio as this config isn't correct.
        set_config('enabled', 0, 'plagiarism_compilatio');
        if ($CFG->version < 2020061500) {
            set_config('compilatio_use', 0, 'plagiarism');
        }
        $incorrectconfig = true;
        if ($quotas["error"] == null) {
            $error = "Invalid API configuration. If you are using a v5 API key please check that you set the API URL field to https://app.compilatio.net/api/private/soap/wsdl";
        } else {
            $error = $quotas["error"];
        }
    } else {
        $apiconfigid = get_config('plagiarism_compilatio', 'apiconfigid');
        $compilatio = compilatio_get_compilatio_service($apiconfigid);
        if (!$compilatio->check_allow_student_analyses()) {
            set_config('allow_student_analyses', 0, 'plagiarism_compilatio');
        }

        compilatio_update_meta();
    }

    redirect('settings.php?error=' . $error);
}

echo $OUTPUT->header();
$currenttab = 'compilatiosettings';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');

$error = optional_param('error', '', PARAM_TEXT);
if (!empty($error)) {
    echo $OUTPUT->notification(get_string("saved_config_failed", "plagiarism_compilatio") . $error);
}

$plagiarismsettings = (array) get_config('plagiarism_compilatio');
$mform->set_data($plagiarismsettings);

if (!empty($plagiarismsettings['enabled']) && !$incorrectconfig) {
    $quotasarray = compilatio_getquotas();
    $quotas = $quotasarray['quotas'];
    if ($quotas == null) {
        // Disable compilatio as this config isn't correct.
        set_config('enabled', 0, 'plagiarism_compilatio');
        if ($CFG->version < 2020061500) {
            set_config('compilatio_use', 0, 'plagiarism');
        }
        echo $OUTPUT->notification(get_string("saved_config_failed", "plagiarism_compilatio") . $quotasarray['error']);
    } else {
        echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
        echo $OUTPUT->notification(get_string('enabledandworking', 'plagiarism_compilatio'), 'notifysuccess');

        $expirationdate = $DB->get_field('plagiarism_compilatio_data', 'value', array('name' => 'account_expire_on'));

        $a = new stdClass();
        $a->used = $quotas->usedCredits;
        $a->end_date = strtolower(compilatio_format_date($expirationdate));

        if (date("Y-m") == $expirationdate) {
            echo "<div class='compilatio-alert compilatio-alert-danger'>
                <strong>" . get_string("account_expire_soon_title", "plagiarism_compilatio") . "</strong><br/>" .
                get_string("admin_account_expire_content", "plagiarism_compilatio") . "</div>";
        }

        echo "<p>" . get_string('subscription_state', 'plagiarism_compilatio', $a) . '</p>';
        echo $OUTPUT->box_end();
    }
    $plagiarismsettings = get_config('plagiarism_compilatio');
    $compilatio = compilatio_get_compilatio_service($plagiarismsettings->apiconfigid);
}

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

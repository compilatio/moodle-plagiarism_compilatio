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
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$plagiarismplugin = new plagiarism_plugin_compilatio();
$plagiarismsettings = (array) get_config('plagiarism_compilatio');

// Test if compilatio is enabled.
if (isset($plagiarismsettings["enabled"])) {
    $enabledsuccess = $plagiarismsettings["enabled"] === "1";
} else {
    $enabledsuccess = false;
}

/*
 * Connection test : the soapcli attribute of compilatioclass will be a string
 * describing the error if any occurs.
 * API key does not matter here.
 */
if (isset($plagiarismsettings["apiconfigid"])) {
    $url = $DB->get_field('plagiarism_compilatio_apicon', 'url',
        array('id' => $plagiarismsettings["apiconfigid"]));
} else {
    $url = "https://service.compilatio.net/webservices/CompilatioUserClient.wsdl";
}
$apiconfig = new stdclass();
$apiconfig->url = $url;
$apiconfig->api_key = "KEY";

$apiconfigid = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);

$compilatio = compilatio_get_compilatio_service($apiconfigid);
$connectionsuccess = !is_string($compilatio->soapcli);

$DB->delete_records('plagiarism_compilatio_apicon', array('id' => $apiconfigid));

// Test if Compilatio is enabled for assign.
if (isset($plagiarismsettings["enable_mod_assign"])) {
    $assignsuccess = $plagiarismsettings["enable_mod_assign"];
} else {
    $assignsuccess = false;
}

// Test if Compilatio is enabled for workshops.
if (isset($plagiarismsettings["enable_mod_workshop"])) {
    $workshopsuccess = $plagiarismsettings["enable_mod_workshop"];
} else {
    $workshopsuccess = false;
}

// Test if Compilatio is enabled for forums.
if (isset($plagiarismsettings["enable_mod_forum"])) {
    $forumsuccess = $plagiarismsettings["enable_mod_forum"];
} else {
    $forumsuccess = false;
}

// Test if Compilatio is enabled for quiz.
if (isset($plagiarismsettings["enable_mod_quiz"])) {
    $quizsuccess = $plagiarismsettings["enable_mod_quiz"];
} else {
    $quizsuccess = false;
}

// API key test. Fails if GetQuota method return NULL.
if (isset($plagiarismsettings["apiconfigid"])) {
    $apikeysuccess = ws_helper::test_connection();
} else {
    $apikeysuccess = false;
}

echo $OUTPUT->header();
$currenttab = 'compilatioautodiagnosis';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$alerts = array();

if ($enabledsuccess) {
    $alerts[] = array('success', get_string("plugin_enabled", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("plugin_disabled", "plagiarism_compilatio"));
}

if ($connectionsuccess) {
    $alerts[] = array('success', get_string("webservice_ok", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("webservice_not_ok", "plagiarism_compilatio"));
}

if ($apikeysuccess) {
    $alerts[] = array('success', get_string("api_key_valid", "plagiarism_compilatio"));
} else if (!$connectionsuccess) {
    $alerts[] = array('warning', get_string("api_key_not_tested", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("api_key_not_valid", "plagiarism_compilatio"));
}

$lastcron = $DB->get_record('plagiarism_compilatio_data', array('name' => 'last_cron'));

if ($lastcron == null) {
    // Cron function in lib.php has never been called.
    $alerts[] = array('danger', get_string("cron_check_never_called", "plagiarism_compilatio"));
} else {
    $cronfrequency = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));

    if ($cronfrequency == null) {
        // We don't have data about frequency yet.
        if ($lastcron->value <= strtotime("-1 hour")) {
            // Cron hasn't been called within the previous hour.
            $alerts[] = array('warning', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron->value)) . " " .
                get_string("cron_check_not_ok", "plagiarism_compilatio") . " " .
                get_string("cron_recommandation", "plagiarism_compilatio")
            );
        } else {
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron->value)) . " " .
                get_string("cron_recommandation", "plagiarism_compilatio"));
        }
    } else {
        if ($cronfrequency->value > 15 || $lastcron->value <= strtotime("-1 hour")) {// Warning.
            $alert = get_string("cron_check", "plagiarism_compilatio", userdate($lastcron->value)) . " ";

            if ($lastcron->value <= strtotime("-1 hour")) {
                // Cron hasn't been called within the previous hour.
                $alert .= get_string("cron_check_not_ok", "plagiarism_compilatio") . " ";
            }

            $alert .= get_string("cron_frequency", "plagiarism_compilatio", $cronfrequency->value) . " ";


            $alerts[] = array('warning', $alert . get_string("cron_recommandation", "plagiarism_compilatio"));
        } else {
            // Cron is called more than once every 15 minutes.
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron->value)) . " " .
                get_string("cron_frequency", "plagiarism_compilatio", $cronfrequency->value) . " " .
                get_string("cron_recommandation", "plagiarism_compilatio")
            );
        }
    }
}

if ($assignsuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_assign", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_assign", "plagiarism_compilatio"));
}


if ($workshopsuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_workshop", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_workshop", "plagiarism_compilatio"));
}


if ($forumsuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_forum", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_forum", "plagiarism_compilatio"));
}

if ($quizsuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_quiz", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_quiz", "plagiarism_compilatio"));
}

/*
 * Display alerts :
 * Index 0 for criticality & index 1 for message
 */
foreach ($alerts as $alert) {
    echo "<div class='compilatio-alert compilatio-alert-" . $alert[0] . "'>" . $alert[1] . "</div>";
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

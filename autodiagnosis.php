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

$plagiarismplugin = new plagiarism_plugin_compilatio();
$plagiarismsettings = (array) get_config('plagiarism');

// Test if compilatio is enabled.
if (isset($plagiarismsettings["compilatio_use"])) {
    $enabledSuccess = $plagiarismsettings["compilatio_use"] === "1";
} else {
    $enabledSuccess = false;
}

/*
 * Connection test : the soapcli attribute of compilatioclass will be a string
 * describing the error if any occurs.
 * API key does not matter here.
 */
if (isset($plagiarismsettings["compilatio_api"])) {
    $compilatio = new compilatioservice(
            "KEY", $plagiarismsettings['compilatio_api'], $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    $connectionSuccess = !is_string($compilatio->soapcli);
} else {
    $compilatio = new compilatioservice(
            "KEY", "http://service.compilatio.net/webservices/CompilatioUserClient2.wsdl", $CFG->proxyhost, $CFG->proxyport, $CFG->proxyuser, $CFG->proxypassword);
    $connectionSuccess = !is_string($compilatio->soapcli);
}

// Test if Compilatio is enabled for assign.
if (isset($plagiarismsettings["compilatio_enable_mod_assign"])) {
    $assignSuccess = $plagiarismsettings["compilatio_enable_mod_assign"];
} else {
    $assignSuccess = false;
}

// Test if Compilatio is enabled for workshops.
if (isset($plagiarismsettings["compilatio_enable_mod_workshop"])) {
    $workshopSuccess = $plagiarismsettings["compilatio_enable_mod_workshop"];
} else {
    $workshopSuccess = false;
}

// Test if Compilatio is enabled for forums.
if (isset($plagiarismsettings["compilatio_enable_mod_forum"])) {
    $forumSuccess = $plagiarismsettings["compilatio_enable_mod_forum"];
} else {
    $forumSuccess = false;
}

// API key test. Fails if GetQuota method return NULL.
if (isset($plagiarismsettings["compilatio_password"], $plagiarismsettings["compilatio_api"])) {
    $APIKeySuccess = compilatio_test_connection($plagiarismsettings["compilatio_password"], $plagiarismsettings["compilatio_api"]);
} else {
    $APIKeySuccess = false;
}

echo $OUTPUT->header();
$currenttab = 'compilatioautodiagnosis';
require_once('compilatio_tabs.php');
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

$alerts = array();

if ($enabledSuccess) {
    $alerts[] = array('success', get_string("plugin_enabled", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("plugin_disabled", "plagiarism_compilatio"));
}

if ($connectionSuccess) {
    $alerts[] = array('success', get_string("webservice_ok", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("webservice_not_ok", "plagiarism_compilatio"));
}

if ($APIKeySuccess) {
    $alerts[] = array('success', get_string("api_key_valid", "plagiarism_compilatio"));
} else if (!$connectionSuccess) {
    $alerts[] = array('warning', get_string("api_key_not_tested", "plagiarism_compilatio"));
} else {
    $alerts[] = array('danger', get_string("api_key_not_valid", "plagiarism_compilatio"));
}

$lastCron = $DB->get_record('plagiarism_compilatio_data', array('name' => 'last_cron'));

if ($lastCron == null) {
    // Cron function in lib.php has never been called.
    $alerts[] = array('danger', get_string("cron_check_never_called", "plagiarism_compilatio"));
} else {
    $cronFrequency = $DB->get_record('plagiarism_compilatio_data', array('name' => 'cron_frequency'));

    if ($cronFrequency == null) {
        //We don't have data about frequency yet.
        if ($lastCron->value <= strtotime("-1 hour")) {
            // Cron hasn't been called within the previous hour.
            $alerts[] = array('warning', get_string("cron_check", "plagiarism_compilatio", userdate($lastCron->value)) . " " .
                get_string("cron_check_not_ok", "plagiarism_compilatio") . " " .
                get_string("cron_recommandation", "plagiarism_compilatio")
            );
        } else {
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastCron->value)) . " " .
                get_string("cron_recommandation", "plagiarism_compilatio"));
        }
    } else {
        if ($cronFrequency->value > 15 || $lastCron->value <= strtotime("-1 hour")) {// Warning.
            $alert = get_string("cron_check", "plagiarism_compilatio", userdate($lastCron->value)) . " ";

            if ($lastCron->value <= strtotime("-1 hour")) {
                // Cron hasn't been called within the previous hour.
                $alert .= get_string("cron_check_not_ok", "plagiarism_compilatio") . " ";
            }

            $alert .= get_string("cron_frequency", "plagiarism_compilatio", $cronFrequency->value) . " ";


            $alerts[] = array('warning', $alert . get_string("cron_recommandation", "plagiarism_compilatio"));
        } else {
            // Cron is called more than once every 15 minutes.
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastCron->value)) . " " .
                get_string("cron_frequency", "plagiarism_compilatio", $cronFrequency->value) . " " .
                get_string("cron_recommandation", "plagiarism_compilatio")
            );
        }
    }
}

if ($assignSuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_assign", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_assign", "plagiarism_compilatio"));
}


if ($workshopSuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_workshop", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_disabled_workshop", "plagiarism_compilatio"));
}


if ($forumSuccess) {
    $alerts[] = array('success', get_string("plugin_enabled_forum", "plagiarism_compilatio"));
} else {
    $alerts[] = array('warning', get_string("plugin_enabled_forum", "plagiarism_compilatio"));
}

/*
 * Display alerts :
 * Index 0 for criticality & index 1 for message
 */
foreach ($alerts as $alert) {
    echo "<div class='alert alert-" . $alert[0] . "'>" . $alert[1] . "</div>";
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();




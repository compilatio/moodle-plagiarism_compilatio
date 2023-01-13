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
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

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

// Connection test.
$compilatio = new CompilatioAPI("test");
if ($compilatio->check_apikey() == "Forbidden ! Your api key is invalid") {
    $connectionsuccess = true;
} else {
    $connectionsuccess = false;
}

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

// API key test.
$compilatio = new CompilatioAPI($plagiarismsettings["apikey"]);
if ($compilatio->check_apikey()) {
    $apikeysuccess = true;
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

$lastcron = get_config('plagiarism_compilatio', 'last_cron');

if ($lastcron == null) {
    // Cron function in lib.php has never been called.
    $alerts[] = array('danger', get_string("cron_check_never_called", "plagiarism_compilatio"));
} else {
    $cronfrequency = get_config('plagiarism_compilatio', 'cron_frequency');

    if ($cronfrequency == null) {
        // We don't have data about frequency yet.
        if ($lastcron <= strtotime("-1 hour")) {
            // Cron hasn't been called within the previous hour.
            $alerts[] = array('warning', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron)) . " " .
                get_string("cron_check_not_ok", "plagiarism_compilatio") . " " .
                get_string("cron_recommandation", "plagiarism_compilatio")
            );
        } else {
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron)) . " " .
                get_string("cron_recommandation", "plagiarism_compilatio"));
        }
    } else {
        if ($cronfrequency > 15 || $lastcron <= strtotime("-1 hour")) {// Warning.
            $alert = get_string("cron_check", "plagiarism_compilatio", userdate($lastcron)) . " ";

            if ($lastcron <= strtotime("-1 hour")) {
                // Cron hasn't been called within the previous hour.
                $alert .= get_string("cron_check_not_ok", "plagiarism_compilatio") . " ";
            }

            $alert .= get_string("cron_frequency", "plagiarism_compilatio", $cronfrequency) . " ";


            $alerts[] = array('warning', $alert . get_string("cron_recommandation", "plagiarism_compilatio"));
        } else {
            // Cron is called more than once every 15 minutes.
            $alerts[] = array('success', get_string("cron_check", "plagiarism_compilatio", userdate($lastcron)) . " " .
                get_string("cron_frequency", "plagiarism_compilatio", $cronfrequency) . " " .
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
    $alerts[] = array('warning', get_string("plugin_enabled_quiz", "plagiarism_compilatio"));
}

/*
 * Display alerts :
 * Index 0 for criticality & index 1 for message
 */
foreach ($alerts as $alert) {
    echo "<div class='cmp-alert cmp-alert-" . $alert[0] . "'>" . $alert[1] . "</div>";
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

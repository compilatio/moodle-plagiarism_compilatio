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
 * CLI script to add module settings for the Compilatio plagiarism plugin.
 *
 * @package    plagiarism_compilatio
 * @copyright  2025 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->libdir . '/clilib.php');

use plagiarism_compilatio\compilatio\course_module_settings;
// Définition des options CLI.
$defaultmodulesettings = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => 0]);
$plugin = new \plagiarism_plugin_compilatio();
$modulesettings = $plugin->get_settings();
if (empty($modulesettings) || !($modulesettings['enabled'] == 1)) {
    cli_error(get_string('exception:not_enabled_module', 'plagiarism_compilatio'));
    die(1);
}

$modulestype = ["quiz", "assign", "forum", "workshop"];
$enabledmoduletypes = [];
foreach ($modulestype as $moduletype) {
    if (isset($modulesettings["enable_mod_{$moduletype}"]) && $modulesettings["enable_mod_{$moduletype}"] == 1) {
        $enabledmoduletypes[] = $moduletype;
    }
}
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'all' => false,
    'enabled' => false,
    'courseids' => '',
    'modulestypename' => implode(',', $enabledmoduletypes),
    'analysistype' => $defaultmodulesettings ? $defaultmodulesettings->analysistype : 'manual',
    'analysisdate' => '',
    'showstudentscore' => $defaultmodulesettings ? $defaultmodulesettings->showstudentscore : 'never',
    'showstudentreport' => $defaultmodulesettings ? $defaultmodulesettings->showstudentreport : 'never',
    'nodefaultindexing' => $defaultmodulesettings ? ($defaultmodulesettings->defaultindexing == 0) : false,
    'greenthreshold' => $defaultmodulesettings ? $defaultmodulesettings->warningthreshold : 10,
    'orangethreshold' => $defaultmodulesettings ? $defaultmodulesettings->criticalthreshold : 25
], [
    'h' => 'help',
    'a' => 'all',
    'e' => 'enabled',
    'c' => 'courseids',
    'm' => 'modulestypename',
    't' => 'analysistype',
    's' => 'showstudentscore',
    'r' => 'showstudentreport',
    'i' => 'defaultindexing',
    'g' => 'greenthreshold',
    'o' => 'orangethreshold'
]);

if ($options['help']) {
    $help = get_string('cli_addmodulesettings_help', 'plagiarism_compilatio');
    echo $help;
    exit(0);
}


// Vérification des options mutuellement exclusives.
if ($options['all'] && $options['enabled']) {
    cli_error(get_string('exception:options_all_enabled', 'plagiarism_compilatio'));
}

$courseids = [];
if (!empty($options['courseids'])) {
    $courseids = array_map('intval', explode(',', $options['courseids']));
}
$analysistype = $options['analysistype'] ?? 'manual';
$analysisdate = $options['analysisdate'] ?? '';

// Validation du paramètre analysisdate si analysistype = planned
if ($analysistype === 'planned') {
    if (empty($analysisdate)) {
        cli_error(get_string('exception:noanalysisdate', 'plagiarism_compilatio'));
        die(1);
    }
    // Vérification du format : YYYY-MM-DD HH:MM:SS
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $analysisdate)) {
        cli_error(get_string('exception:wrongdateformat', 'plagiarism_compilatio'));
        die(1);
    }
    // Vérification que la date est valide
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $analysisdate);
    if (!$dt || $dt->format('Y-m-d H:i:s') !== $analysisdate) {
        cli_error(get_string('exception:datenogood', 'plagiarism_compilatio'));
        die(1);
    }
    $datetime = new DateTime($analysisdate);
    $analysistime = $datetime->getTimestamp();
}

$showstudentscore = $options['showstudentscore'] ?? 'never';
$showstudentreport = $options['showstudentreport'] ?? 'never';
$defaultindexing = $options['nodefaultindexing'] ? 0 : 1;

// Validation du paramètre displayscoretostudent
$validscores = ['never', 'immediatly', 'closed'];
if (!in_array($showstudentscore, $validscores, true)) {
    cli_error(get_string('exception:displayscoretostudentwrong', 'plagiarism_compilatio'));
    die(1);
}

if (!empty($options['modulestypename'])) {
    // Validation des types de modules.
    $moduleok = array_map(fn($value) => in_array($value, $modulestype) !== false && in_array($value, $enabledmoduletypes), explode(',', $options['modulestypename']));
    if (in_array(false, $moduleok, true)) {
        cli_error(get_string('exception:invalid_moduletype', 'plagiarism_compilatio'));
        die(1);
    }
    $modulestype = array_map('trim', explode(',', $options['modulestypename']));
}

// Récupération des cours à traiter.
if (empty($courseids)) {
    $courses = $DB->get_records('course', ["visible" => 1]);
} else {
    list($sql, $params) = $DB->get_in_or_equal($courseids);
    $courses = $DB->get_records_select('course', "visible = 1 AND id $sql", $params);
}

$cmpuser = $DB->get_record_sql("select * from {plagiarism_compilatio_user} where id = (select min(id) from {plagiarism_compilatio_user})");
global $USER;
$USER = $DB->get_record('user', ['id' => $cmpuser->userid], '*', MUST_EXIST);
if (get_config('plagiarism_compilatio', 'enable_show_reports') !== '1') {
    $showstudentreport = 'never';
}
foreach ($courses as $course) {
    echo "Cours: {$course->fullname} (ID: {$course->id})\n";
    foreach ($modulestype as $modtype) {
        // Récupération des modules d'activité pour ce cours et ce type qui ne sont pas encore configurés.
        /** Mdlcode assume: $modtype ['quiz','assign','forum','workshop'] */
        $sql = "SELECT cm.id as coursemodule, cm.instance, cm.visible, cm.module, mo.name
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                JOIN {{$modtype}} mo ON mo.id = cm.instance
                LEFT JOIN {plagiarism_compilatio_cm_cfg} pcfg ON pcfg.cmid = cm.id
                WHERE cm.course = ? AND m.name = ?
                AND pcfg.id IS NULL";
        $params = [$course->id, $modtype];
        $modules = $DB->get_records_sql($sql, $params);

        foreach ($modules as $module) {
            // Filtre selon --all ou --enabled
            if ($options['enabled'] && !$module->visible) {
                continue;
            }
            echo get_string('process_info', 'plagiarism_compilatio', $module);
            // Ici, ajoute ta logique de traitement du module
            $data = new stdClass();
            $data->coursemodule = $module->coursemodule;
            $data->name = $module->name;
            $data->activated = '1';
            $data->showstudentscore = $showstudentscore;
            $data->showstudentreport = $showstudentreport;
            $data->analysistype = $analysistype;

            $data->defaultindexing = $defaultindexing;
            $data->warningthreshold = $options['greenthreshold'];
            $data->criticalthreshold = $options['orangethreshold'];
            course_module_settings::save_course_module_settings($data, $course);
        }
    }
}


echo get_string('process_ended', 'plagiarism_compilatio');
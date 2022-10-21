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
 * migrate.php
 *
 * @package   plagiarism_compilatio
 * @copyright 2022 Compilatio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$restart = optional_param('restart', null, PARAM_RAW);
if ($restart == '1') {
    $files = $DB->get_records_select("plagiarism_compilatio_files", "CHAR_LENGTH(externalid) = 32 && migrationstatus != 200");

    foreach ($files as $file) {
        $file->migrationstatus = null;
        $DB->update_record("plagiarism_compilatio_files", $file);
    }

    $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'start_migration', 'value' => '1']);
    redirect('migrate.php');
}

$apikey = optional_param('apikey', null, PARAM_RAW);
if (!empty($apikey)) {
    $DB->delete_records("plagiarism_compilatio_data", ["name" => "start_migration"]);

    $ch = curl_init();
    $params = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('X-Auth-Token: ' . $apikey, 'Content-Type: application/json'),
        CURLOPT_URL => COMPILATIO_API_URL . "/authentication/check-api-key"
    ];

    // Proxy settings.
    if (!empty($CFG->proxyhost)) {
        $params[CURLOPT_PROXY] = $CFG->proxyhost;

        $params[CURLOPT_HTTPPROXYTUNNEL] = false;

        if (!empty($CFG->proxytype) && ($CFG->proxytype == 'SOCKS5')) {
            $params[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
        }

        if (!empty($CFG->proxyport)) {
            $params[CURLOPT_PROXYPORT] = $CFG->proxyport;
        }

        if (!empty($CFG->proxyuser) && !empty($CFG->proxypassword)) {
            $params[CURLOPT_PROXYUSERPWD] = $CFG->proxyuser . ':' . $CFG->proxypassword;
        }
    }

    if (get_config('plagiarism_compilatio', 'disable_ssl_verification') == 1) {
        $params[CURLOPT_SSL_VERIFYPEER] = false;
    }

    curl_setopt_array($ch, $params);
    $t = curl_exec($ch);
    $response = json_decode($t);

    if (isset($response->status->code) && $response->status->code == 200) {
        $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'start_migration', 'value' => '1']);

        $apiconfig = new stdClass();
        $apiconfig->startdate = 0;
        $apiconfig->url = COMPILATIO_API_URL . "/soap/wsdl";
        $apiconfig->api_key = $apikey;

        $apiconfigid = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);

        set_config('apiconfigid', $apiconfigid, 'plagiarism_compilatio');

        $DB->set_field("plagiarism_compilatio_files", "apiconfigid", $apiconfigid);
        $DB->delete_records_select("plagiarism_compilatio_apicon", "id != ?", array($apiconfigid));
    } else {
        echo "<div class='compilatio-alert compilatio-alert-danger'>
                Error : " . ($response->status->message ?? '') . curl_error($ch) .
            "</div>";
    }

    redirect('migrate.php');
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$currenttab = 'compilatiomigrate';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo "<h3>" . get_string('migration_title', 'plagiarism_compilatio') . "</h3>";
echo "<p>" . get_string('migration_info', 'plagiarism_compilatio') . "</p>";

echo "<h5 class='compi-migration'>" . get_string('migration_form_title', 'plagiarism_compilatio') . "</h5>";
echo "<div class='form-inline'>
        <label>" . get_string('migration_apikey', 'plagiarism_compilatio') . " : </label>
        <form>
            <input class='form-control m-2' type='text' id='apikey' name='apikey' required>
            <button id='compilatio-startmigration-btn' class='btn btn-primary'>"
                . get_string('migration_btn', 'plagiarism_compilatio') .
            "</button>
        </form>
    </div>";

$v4files = $DB->count_records_select("plagiarism_compilatio_files", "CHAR_LENGTH(externalid) = 32 && migrationstatus IS NULL");
$migrationrunning = $DB->get_record('plagiarism_compilatio_data', array('name' => 'start_migration'));

if ($v4files > 0 && !empty($migrationrunning)) {
    echo "<div class='compilatio-alert compilatio-alert-info'>
            <b>" . get_string('migration_np', 'plagiarism_compilatio') . "</b>
        </div>";

    echo "<div class='compilatio-alert compilatio-alert-info'>" . get_string('migration_inprogress', 'plagiarism_compilatio')
        . "<br>" . $v4files . " " . get_string('migration_toupdate_doc', 'plagiarism_compilatio') . "</div>";
}

$migrationcountsuccess = $DB->count_records("plagiarism_compilatio_files", ["migrationstatus" => 200]);
if ($migrationcountsuccess > 0) {
    $mes = '';
    if ($v4files == 0) {
        $mes = get_string('migration_completed', 'plagiarism_compilatio');
    }
    echo "<div class='compilatio-alert compilatio-alert-success'>"
            . $mes . " " . $migrationcountsuccess . " " . get_string('migration_success_doc', 'plagiarism_compilatio') .
        "</div>";
}

$migrationcounterror = $DB->count_records_select("plagiarism_compilatio_files", "migrationstatus != 200");
if ($migrationcounterror > 0) {
    echo "<div style='display: flex; justify-content: space-between;' class='compilatio-alert compilatio-alert-danger'>"
            . $migrationcounterror . " " . get_string('migration_failed_doc', 'plagiarism_compilatio');
    if (empty($migrationrunning)) {
        echo "<form>
            <input id='restart' name='restart' type='hidden' value='1'>
            <button class='btn btn-primary'>" . get_string('migration_restart', 'plagiarism_compilatio') . "</button>
        </form>";
    }
    echo "</div>";
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

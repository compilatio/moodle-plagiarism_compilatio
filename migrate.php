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

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$currenttab = 'compilatiomigrate';
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_tabs.php');
echo "<h3>" . get_string('migration_title', 'plagiarism_compilatio') . "</h3>";
echo "<p>" . get_string('migration_info', 'plagiarism_compilatio') . "</p>";
echo "<form class='form-inline' action='migrate.php' method='post'>
        <label>" . get_string('migration_apikey', 'plagiarism_compilatio') . " : </label>
        <input class='form-control m-2' type='text' id='apikey' name='apikey' required>
        <input class='btn btn-primary' type='submit' value='" . get_string('migration_btn', 'plagiarism_compilatio') . "'>
    </form>";

$apikey = optional_param('apikey', null, PARAM_RAW);
if (!empty($apikey)) {
    $countsuccess = 0;

    $ch = curl_init();
    $params = [
        CURLOPT_URL => "https://app.compilatio.net/api/private/authentication/check-api-key",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('X-Auth-Token: ' . $apikey, 'Content-Type: application/json'),
    ];

    curl_setopt_array($ch, $params);
    $response = json_decode(curl_exec($ch));
    curl_close($ch);

    if (isset($response->status->code) && $response->status->code == 200) {
        $apiconfig = new stdClass();
        $apiconfig->startdate = 0;
        $apiconfig->url = "https://app.compilatio.net/api/private/soap/wsdl";
        $apiconfig->api_key = $apikey;

        $apiconfigid = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);

        set_config('apiconfigid', $apiconfigid, 'plagiarism_compilatio');

        $ch = curl_init();
        $params[CURLOPT_URL] = "https://app.compilatio.net/api/private/documents/list?projection="
            . json_encode(["old_prod_id" => true]);

        curl_setopt_array($ch, $params);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        if (isset($response->data->documents)) {
            foreach ($response->data->documents as $doc) {
                if (isset($doc->old_prod_id)) {
                    $v4file = $DB->get_record("plagiarism_compilatio_files", array("externalid" => $doc->old_prod_id));
                    if (!empty($v4file)) {
                        $v4file->externalid = $doc->id;
                        $v4file->apiconfigid = $apiconfigid;
                        if ($DB->update_record("plagiarism_compilatio_files", $v4file)) {
                            $countsuccess += 1;
                        }
                    }
                }
            }

            $sql = "SELECT * FROM {plagiarism_compilatio_files} files
                JOIN {plagiarism_compilatio_apicon} apicon ON files.apiconfigid = apicon.id
                WHERE apiconfigid != ? AND api_key LIKE 'mo7-%'";
            $v4files = $DB->get_records_sql($sql, [$apiconfigid]);
            if (empty($v4files)) {
                $DB->delete_records_select("plagiarism_compilatio_apicon", "id != ?", array($apiconfigid));
                echo $OUTPUT->notification(get_string('migration_success', 'plagiarism_compilatio'), 'notifysuccess');
            } else {
                echo $OUTPUT->notification($countsuccess . " / " . ($countsuccess + count($v4files))
                    . " " . get_string('migration_success_doc', 'plagiarism_compilatio'), 'notifysuccess');
            }
        } else {
            echo $OUTPUT->notification("Failed to get v5 documents : " . $response->status->message ?? "");
        }
    } else {
        echo $OUTPUT->notification("Invalid API Key");
    }
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

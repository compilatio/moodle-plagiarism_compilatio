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
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['apikey']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_login();

$apikey = optional_param('apikey', null, PARAM_RAW);
$i = optional_param('i', null, PARAM_RAW);

if (!empty($apikey)) {
    $ch = curl_init();
    $params = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('X-Auth-Token: ' . $apikey, 'Content-Type: application/json'),
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

    if ($i == 0) {
        $_SESSION["countsuccess"] = 0;
        $params[CURLOPT_URL] = "https://app.compilatio.net/api/private/authentication/check-api-key";

        curl_setopt_array($ch, $params);
        $t = curl_exec($ch);
        $response = json_decode($t);

        if (isset($response->status->code) && $response->status->code == 200) {
            $apiconfig = new stdClass();
            $apiconfig->startdate = 0;
            $apiconfig->url = "https://app.compilatio.net/api/private/soap/wsdl";
            $apiconfig->api_key = $apikey;

            $_SESSION["apiconfigid"] = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);

            set_config('apiconfigid', $_SESSION["apiconfigid"], 'plagiarism_compilatio');
            echo true;
        } else {
            echo "Error : Invalid API Key : " . ($response->status->message ?? '') . curl_error($ch);
        }
    } else {
        $params[CURLOPT_URL] = "https://app.compilatio.net/api/private/documents/list?limit=500&page=" . $i . "&sort[metadata.indexed]=1&projection="
        . json_encode(["old_prod_id" => true]);

        curl_setopt_array($ch, $params);
        $t = curl_exec($ch);
        $response = json_decode($t);

        if (isset($response->data->documents)) {
            if (!empty($response->data->documents)) {
                foreach ($response->data->documents as $doc) {
                    if (isset($doc->old_prod_id)) {
                        $v4file = $DB->get_record("plagiarism_compilatio_files", array("externalid" => $doc->old_prod_id));
                        if (!empty($v4file)) {
                            $v4file->externalid = $doc->id;
                            $v4file->apiconfigid = $_SESSION["apiconfigid"];
                            if ($DB->update_record("plagiarism_compilatio_files", $v4file)) {
                                $_SESSION["countsuccess"] += 1;
                            }
                        }
                    }
                }
                echo true;
            } else {
                $sql = "SELECT * FROM {plagiarism_compilatio_files} files
                    JOIN {plagiarism_compilatio_apicon} apicon ON files.apiconfigid = apicon.id
                    WHERE externalid IS NOT NULL AND apiconfigid != ? AND api_key LIKE 'mo7-%'";
                $v4files = $DB->get_records_sql($sql, [$_SESSION["apiconfigid"]]);

                echo $_SESSION["countsuccess"] . " / " . ($_SESSION["countsuccess"] + count($v4files))
                . " " . get_string('migration_success_doc', 'plagiarism_compilatio');

                $DB->set_field("plagiarism_compilatio_files", "apiconfigid", $_SESSION["apiconfigid"]);
                $DB->delete_records_select("plagiarism_compilatio_apicon", "id != ?", array($_SESSION["apiconfigid"]));
            }
        } else {
            echo "Error : Failed to get v5 documents : cURL params : " . var_export($params, true) . " / cURL Error : " . curl_error($ch) . " / cURL response : " . var_export($t, true);
        }
    }

    curl_close($ch);
}

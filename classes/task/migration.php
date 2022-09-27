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
 * migration.php - Contains Plagiarism plugin migration task.
 *
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

/**
 * Task class
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migration extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('migration', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $PAGE, $CFG, $DB;

        $apikey = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_apikey'));
        $message = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_message'));

        if (!empty($apikey) && empty($message)) {
            $apikey = $apikey->value;

            $progress = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_progress'));
            $i = $progress->value ?? 0;

            do {
                $continue = false;

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

                if (get_config('plagiarism_compilatio', 'disable_ssl_verification') == 1) {
                    $params[CURLOPT_SSL_VERIFYPEER] = false;
                }

                if ($i == 0) {
                    $params[CURLOPT_URL] = "https://app.compilatio.net/api/private/document/count";

                    curl_setopt_array($ch, $params);
                    $t = curl_exec($ch);
                    $response = json_decode($t);

                    if (isset($response->status->code) && $response->status->code == 200) {
                        $apiconfig = new \stdClass();
                        $apiconfig->startdate = 0;
                        $apiconfig->url = "https://app.compilatio.net/api/private/soap/wsdl";
                        $apiconfig->api_key = $apikey;

                        $apiconfigid = $DB->insert_record('plagiarism_compilatio_apicon', $apiconfig);

                        set_config('apiconfigid', $apiconfigid, 'plagiarism_compilatio');

                        $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_apiconfigid'");
                        $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_apiconfigid', 'value' => $apiconfigid]);

                        $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_total'");
                        $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_total', 'value' => ceil($response->data->count / 500)]);

                        $continue = true;
                    } else {
                        $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_message'");
                        $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_message', 'value' => "Error : Invalid API Key : " . ($response->status->message ?? '') . curl_error($ch)]);
                    }
                } else {
                    $params[CURLOPT_URL] = "https://app.compilatio.net/api/private/documents/list?limit=500&page=" . $i . "&sort[metadata.indexed]=1&projection="
                    . json_encode(["old_prod_id" => true]);

                    curl_setopt_array($ch, $params);
                    $t = curl_exec($ch);
                    $response = json_decode($t);

                    if (isset($response->data->documents)) {
                        if (!empty($response->data->documents)) {
                            $success = 0;

                            foreach ($response->data->documents as $doc) {
                                if (isset($doc->old_prod_id)) {
                                    $v4file = $DB->get_record("plagiarism_compilatio_files", array("externalid" => $doc->old_prod_id));
                                    if (!empty($v4file)) {
                                        $v4file->externalid = $doc->id;
                                        $v4file->apiconfigid = $_SESSION["apiconfigid"];
                                        if ($DB->update_record("plagiarism_compilatio_files", $v4file)) {
                                            $success += 1;
                                        }
                                    }
                                }
                            }

                            $countsuccess = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_countsuccess'));

                            if (empty($countsuccess)) {
                                $item = new \stdClass();
                                $item->name = "migration_countsuccess";
                                $item->value = 0;
                                $DB->insert_record('plagiarism_compilatio_data', $item);
                            } else {
                                $countsuccess->value = intval($countsuccess->value) + $success;
                                $DB->update_record('plagiarism_compilatio_data', $countsuccess);
                            }

                            $progress = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_progress'));

                            if (empty($progress)) {
                                $item = new \stdClass();
                                $item->name = "migration_progress";
                                $item->value = $i;
                                $DB->insert_record('plagiarism_compilatio_data', $item);
                            } else {
                                $progress->value = $i;
                                $DB->update_record('plagiarism_compilatio_data', $progress);
                            }

                            $continue = true;
                        } else {
                            $apiconfigid = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_apiconfigid'));

                            $sql = "SELECT COUNT(files.id) FROM {plagiarism_compilatio_files} files
                                JOIN {plagiarism_compilatio_apicon} apicon ON files.apiconfigid = apicon.id
                                WHERE externalid IS NOT NULL AND apiconfigid != ? AND api_key LIKE 'mo7-%'";
                            $countV4files = $DB->count_records_sql($sql, [$apiconfigid->value]);

                            $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_count_v4'");
                            $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_count_v4', 'value' => $countV4files]);

                            $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_message'");
                            $DB->insert_record('plagiarism_compilatio_data', (object) ['name' => 'migration_message', 'value' => "success"]);

                            $DB->set_field("plagiarism_compilatio_files", "apiconfigid", $apiconfigid->value);
                            $DB->delete_records_select("plagiarism_compilatio_apicon", "id != ?", array($apiconfigid->value));
                        }
                    } else {
                        $DB->delete_records_select("plagiarism_compilatio_data", "name = 'migration_message'");
                        $item = new \stdClass();
                        $item->name = "migration_message";
                        $item->value = "Error : Failed to get v5 documents : cURL params : " . var_export($params, true) . " / cURL Error : " . curl_error($ch) . " / cURL response : " . var_export($t, true);
                        $DB->insert_record('plagiarism_compilatio_data', $item);
                    }
                }

                curl_close($ch);

                $i += 1;

                $message = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_message'));
                if (!empty($message)) {
                    $continue = false;
                }
            } while ($continue);
        }
    }
}

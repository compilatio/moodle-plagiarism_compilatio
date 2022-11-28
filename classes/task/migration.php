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
        return get_string('migration_task', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $PAGE, $CFG, $DB;

        if (empty($DB->get_record('plagiarism_compilatio_data', array('name' => 'start_migration')))) {
            return;
        }

        for ($i = 0; $i < 40; $i++) {
            $v4files = $DB->get_records_select("plagiarism_compilatio_files",
                "CHAR_LENGTH(externalid) = 32 AND migrationstatus IS NULL", null, '', '*', 0, 25);

            if (empty($v4files) && is_array($v4files)) {
                $DB->delete_records('plagiarism_compilatio_data', array('name' => 'start_migration'));
                return;
            }

            $requests = [];
            foreach ($v4files as $file) {
                $requests[] = [
                    'method' => 'Get',
                    'path' => '/private/document/' . $file->externalid
                ];
            }

            $apiconfig = $DB->get_record(
                'plagiarism_compilatio_apicon',
                array('id' => get_config('plagiarism_compilatio', 'apiconfigid'))
            );

            $ch = curl_init();
            $params = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array('X-Auth-Token: ' . $apiconfig->api_key, 'Content-Type: application/json'),
                CURLOPT_URL => "https://app.compilatio.net/api/private/composite",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requests)
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
            $result = json_decode($t);
            curl_close($ch);

            if (!isset($result->data->responses)) {
                mtrace("API call error / cURL Error : " . curl_error($ch) . " / cURL response : " . var_export($t, true));
                return;
            }

            $continue = false;
            
            foreach ($result->data->responses as $response) {
                if (isset($response->data->document)) {
                    // Document migrated from v4.
                    if (isset($response->data->document->old_prod_id)) {
                        $docid = $response->data->document->old_prod_id;
                    } else { // Document v5 loaded with SOAP API wrapper.
                        $docid = $response->data->document->id;
                    }
                    $file = $DB->get_record(
                        "plagiarism_compilatio_files",
                        array("externalid" => $docid)
                    );
                    if (!empty($file)) {
                        $file->externalid = $response->data->document->id;
                        $file->migrationstatus = 200;
                        $DB->update_record("plagiarism_compilatio_files", $file);
                    }
                    $continue = true;
                } else if ($response->status->code !== 503) {
                    $docid = end(explode('/', $response->request->path));
                    $files = $DB->get_records("plagiarism_compilatio_files", array("externalid" => $docid));
                    foreach ($files as $file) {
                        $file->migrationstatus = $response->status->code;
                        $DB->update_record("plagiarism_compilatio_files", $file);
                    }
                    $continue = true;
                }

            }

            if (!$continue) {
                mtrace("Get documents call APIs error");
                return;
            }

            if (count($requests) < 25) break;
        }
    }
}

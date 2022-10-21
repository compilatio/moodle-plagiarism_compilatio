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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

namespace plagiarism_compilatio\task;
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

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
                "CHAR_LENGTH(externalid) = 32 && migrationstatus IS NULL", null, '', '*', 0, 25);

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
                CURLOPT_URL => COMPILATIO_API_URL . "/composite",
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
            $result = json_decode(curl_exec($ch));
            curl_close($ch);

            foreach ($result->data->responses as $response) {

                if (isset($response->data->document)) {
                    $file = $DB->get_record(
                        "plagiarism_compilatio_files",
                        array("externalid" => $response->data->document->old_prod_id)
                    );
                    if (!empty($file)) {
                        $file->externalid = $response->data->document->id;
                        $file->migrationstatus = 200;
                        $DB->update_record("plagiarism_compilatio_files", $file);
                    }
                } else if ($response->status->code !== 503) {
                    $docid = end(explode('/', $response->request->path));
                    $file = $DB->get_record("plagiarism_compilatio_files", array("externalid" => $docid));
                    $file->migrationstatus = $response->status->code;
                    $DB->update_record("plagiarism_compilatio_files", $file);
                }
            }
        }
    }
}

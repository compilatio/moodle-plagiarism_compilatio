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

if (!empty($apikey)) {
    $DB->delete_records_select("plagiarism_compilatio_data", "name LIKE 'migration_%'");
    
    $item = new stdClass();
    $item->name = "migration_apikey";
    $item->value = $apikey;
    $DB->insert_record('plagiarism_compilatio_data', $item);

    echo "<div class='alert alert-info alert-block fade in' role='alert' data-aria-autofocus='true'>
        " . get_string('migration_waiting', 'plagiarism_compilatio') . "
    </div>";
}

$apikey = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_apikey'));

if (!empty($apikey)) {
    echo "<div style='margin: 2rem 0 1rem 0;'>";
    echo "<h5>" . get_string('migration_state', 'plagiarism_compilatio', $apikey->value) . " <span style='font-size: 12px;'>" . get_string('migration_state_info', 'plagiarism_compilatio') . "</span></h5>";

    $message = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_message'));

    if (empty($message)) {
        $progress = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_progress'));

        if (empty($progress)) {
            echo "<div class='alert alert-info alert-block fade in' role='alert' data-aria-autofocus='true'>
                " . get_string('migration_waiting', 'plagiarism_compilatio') . "
            </div>";
        } else {
            $total = $DB->get_record('plagiarism_compilatio_data', array('name' => 'migration_total'));
    
            echo "<div class='alert alert-info alert-block fade in' role='alert' data-aria-autofocus='true'>
                " . get_string('migration_inprogress', 'plagiarism_compilatio') . " <i class='fa fa-spinner fa-spin fa-circle-notch'></i>
                <div id='migration-progress'>
                    <progress id='migration-update-progress' value='{$progress->value}' max='{$total->value}'></progress>
                </div>
            </div>";
        }
    } else {
        if (strpos($message->value, 'Error') !== 0) {
            if ($message->value == "stopped") {
                echo "<div style='display: flex;' class='alert alert-success alert-block fade in' role='alert' data-aria-autofocus='true'>" . get_string('migration_stopped', 'plagiarism_compilatio') . "
                    <form>
                        <input id='restart' name='restart' type='hidden' value='1'>
                        <button style='margin-left: 1rem;' id='cmp-restart-migration-btn' class='btn btn-primary'>" . get_string('migration_restart_btn', 'plagiarism_compilatio') . "</button>
                    </form>
                </div>";
            } else {
                echo "<div class='alert alert-success alert-block fade in' role='alert' data-aria-autofocus='true'>{$message->value}</div>";
            }
            $success = true;
        } else {
            echo "<div style='display: flex;' class='alert alert-danger alert-block fade in' role='alert' data-aria-autofocus='true'>{$message->value}
                    <form>
                        <input id='restart' name='restart' type='hidden' value='1'>
                        <button style='margin-left: 1rem;' id='cmp-restart-migration-btn' class='btn btn-primary'>" . get_string('migration_restart_btn', 'plagiarism_compilatio') . "</button>
                    </form>
                </div>";
        }
    }
    if (!isset($success)) {
        echo "<form>
            <input id='stop' name='stop' type='hidden' value='1'>
            <button class='btn btn-primary'>" . get_string('migration_stop', 'plagiarism_compilatio') . "</button>
        </form>";
    }
    echo "</div>";
} 
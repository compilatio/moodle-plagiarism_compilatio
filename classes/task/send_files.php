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
 * send_files.php - Contains Plagiarism plugin send_files task.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

/**
 * Task class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_files extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('send_files', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        $compilatio = new \plagiarism_plugin_compilatio();
        compilatio_send_pending_files($compilatio->get_settings());
    }

}

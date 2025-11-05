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
 * trigger_analyses.php - Contains trigger_analyses task.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2025 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

use plagiarism_compilatio\compilatio\analysis;

/**
 * Trigger_analyses task class
 */
class trigger_analyses extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('trigger_analyses', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $DB;

        if (get_config('plagiarism_compilatio', 'compilatio_maintenance') === "1") {
            return;
        }

        // Return all files with Compilatio activated and to analyze.
        $sql = "SELECT file.* FROM {plagiarism_compilatio_files} file
            JOIN {plagiarism_compilatio_cm_cfg} config ON config.cmid = file.cm
            WHERE (file.status = 'sent'
                AND config.activated = '1'
                AND config.analysistype = 'planned'
                AND config.analysistime < ?)
            OR file.status = 'to_analyze'";
        $files = $DB->get_records_sql($sql, [time()]);

        foreach ($files as $file) {
            analysis::start_analysis($file);
        }
    }
}

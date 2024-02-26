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
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;
// Plugin v2 docs management.
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
        global $CFG, $DB;
        require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');

        $sql = "SELECT file.* FROM {plagiarism_compilatio_files} file
            JOIN {plagiarism_compilatio_cm_cfg} config ON config.cmid = file.cm
            WHERE file.status = 'sent' AND config.activated = '1' AND config.analysistype = 'planned' AND config.analysistime < ?";
        $files = $DB->get_records_sql($sql, [time()]);

        foreach ($files as $file) {
            \CompilatioAnalyses::start_analysis($file);
        }

        $files = $DB->get_records('plagiarism_compilatio_files', ['status' => 'to_analyze']);
        foreach ($files as $file) {
            \CompilatioAnalyses::start_analysis($file);
        }
    }
}

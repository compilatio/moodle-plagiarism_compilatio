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
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

use plagiarism_compilatio\compilatio\analysis;

/**
 * Trigger_analyses task class
 */
class trigger_analyses extends \core\task\scheduled_task {
    /**
     * Delay before considering a file stuck in the queue (in seconds)
     */
    const STUCK_QUEUE_DELAY = 3 * 3600;

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name(): string {
        return get_string('trigger_analyses', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute(): void {
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

        $this->restart_stuck_auto_analyses();
    }

    /**
     * Request again the analysis of documents stuck in queue in auto-analysis.
     * @return void
     */
    private function restart_stuck_auto_analyses(): void {
        global $DB;

        $sql = "SELECT file.* FROM {plagiarism_compilatio_files} file
            JOIN {plagiarism_compilatio_cm_cfg} config ON config.cmid = file.cm
            WHERE file.status = 'queue'
                AND file.analysisid IS NULL
                AND file.externalid IS NOT NULL
                AND file.timesubmitted < ?
                AND config.activated = '1'
                AND config.analysistype = 'auto'";
        $files = $DB->get_records_sql($sql, [time() - self::STUCK_QUEUE_DELAY]);

        foreach ($files as $file) {
            error_log('restarting stuck analysis for file ' . $file->id);
            $result = analysis::start_analysis($file);
            if (!in_array($result, ['queue', 'error_too_short', 'error_too_long', 'error_extraction_failed'], true)) {
                error_log('failed to restart analysis for file ' . $file->id . ': ' . $result);
            }
        }
    }
}

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
 * get_scores.php - Contains get_scores task.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

use plagiarism_compilatio\compilatio\analysis;

/**
 * Get_scores task class
 */
class get_scores extends \core\task\scheduled_task {
    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('get_scores', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {

        global $DB, $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        $compilatio = new \plagiarism_plugin_compilatio();

        if (get_config("plagiarism_compilatio", "compilatio_maintenance") === "1") {
            return;
        }

        // Keep track of the last cron execution.
        $lastcron = get_config('plagiarism_compilatio', 'last_cron');
        if ($lastcron != null) {
            $frequency = round((time() - $lastcron) / 60);
            set_config('cron_frequency', $frequency, 'plagiarism_compilatio');
        }
        set_config('last_cron', strtotime('now'), 'plagiarism_compilatio');

        if ($compilatio->get_settings()) {
            mtrace('getting Compilatio similarity scores');
            // Get all files set that have been submitted.
            $sql = "SELECT cf.* FROM {plagiarism_compilatio_files} cf
                JOIN {plagiarism_compilatio_cm_cfg} cfg ON cf.cm = cfg.cmid
                WHERE cf.status = 'analysing'
                OR cf.status = 'queue'
                OR (cf.status = 'sent' AND cfg.analysistype = 'planned' AND cfg.analysistime < ?)";
            $files = $DB->get_records_sql($sql, [time()]);

            if (!empty($files)) {
                foreach ($files as $plagiarismfile) {
                    mtrace('getting score for file ' . $plagiarismfile->id);
                    analysis::check_analysis($plagiarismfile);
                }
            }
        }
    }
}

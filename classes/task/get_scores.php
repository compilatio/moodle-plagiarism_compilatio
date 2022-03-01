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
 * get_scores.php - Contains Plagiarism plugin get_scores task.
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
        require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');

        $compilatio = new \plagiarism_plugin_compilatio();

        // Keep track of the last cron execution.
        $lastcron = get_config('plagiarism_compilatio', 'last_cron');
        if ($lastcron != null) {
            $frequency = round((time() - $lastcron) / 60);
            set_config('cron_frequency', $frequency, 'plagiarism_compilatio');
        }
        set_config('last_cron', strtotime("now"), 'plagiarism_compilatio');

        if ($plagiarismsettings = $compilatio->get_settings()) {
            mtrace("getting Compilatio similarity scores");
            // Get all files set that have been submitted.
            $sql = "status = 'analyzing' OR status = 'queue'";
            $files = $DB->get_records_select('plagiarism_compilatio_files', $sql);
            if (!empty($files)) {
                foreach ($files as $plagiarismfile) {
                    // Check if we need to delay this submission.
                    $attemptallowed = self::compilatio_check_delay($plagiarismfile);
                    if (!$attemptallowed) {
                        continue;
                    }
                    mtrace("getting score for file " . $plagiarismfile->id);
                    \CompilatioAnalyses::check_analysis($plagiarismfile); // Get status and set reporturl/status if required.
                }
            }
        }
    }

    /**
     * Function to check timesubmitted and attempt to see if we need to delay an API check
     *
     * @param  array $plagiarismfile    A row of plagiarism_compilatio_files in database
     * @return bool                     Return true if succeed, false otherwise
     */
    public static function compilatio_check_delay($plagiarismfile) {
        // Initial wait time - this is doubled each time a check is made until the max delay is met.
        $submissiondelay = 5;
        // Maximum time to wait between checks.
        $maxsubmissiondelay = 120;

        $i = 0;
        $wait = 0;
        while ($i < $plagiarismfile->attempt) {
            $time = $submissiondelay * ($plagiarismfile->attempt - $i);
            if ($time > $maxsubmissiondelay) {
                $time = $maxsubmissiondelay;
            }
            $wait += $time;
            $i++;
        }
        $wait = (int) $wait * 60;
        $timetocheck = (int) ($plagiarismfile->timesubmitted + $wait);

        if ($timetocheck < time()) {
            return true;
        } else {
            return false;
        }
    }
}

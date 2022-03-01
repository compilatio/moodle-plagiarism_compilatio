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
 * trigger_timed_analyses.php - Contains Plagiarism plugin trigger_timed_analyses task.
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
class trigger_timed_analyses extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('trigger_timed_analyses', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/analyses.php');

        // Now check for any assignments with a scheduled processing time that is after now.
        $sql = "SELECT cf.* FROM {plagiarism_compilatio_files} cf
                    LEFT JOIN {plagiarism_compilatio_config} cc1 ON cc1.cm = cf.cm
                    LEFT JOIN {plagiarism_compilatio_config} cc2 ON cc2.cm = cf.cm
                    LEFT JOIN {plagiarism_compilatio_config} cc3 ON cc3.cm = cf.cm
                    WHERE cf.status = 'sent'
                    AND cc1.name = 'use_compilatio' AND cc1.value='1'
                    AND cc2.name = 'analysis_type' AND cc2.value = 'planned'
                    AND cc3.name = 'time_analyse'
                    AND " . $DB->sql_cast_char2int('cc3.value') . " < ?";
        $plagiarismfiles = $DB->get_records_sql($sql, array(time()));

        foreach ($plagiarismfiles as $plagiarismfile) {
            CompilatioAnalyses::start_analysis($plagiarismfile);
        }
    }

}

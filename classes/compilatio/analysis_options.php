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
 * analysis_settings.php - Contains methods to display and save settings.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

/**
 * CompilatioSettings class
 */
class CompilatioAnalysisOptions {

    public static function get_analysis_options() {

        global $DB, $PAGE, $CFG;
        $compilatio = new CompilatioAPI();

        $output = "
            <div class='cmp-relative-position'>
                <div class='cmp-table-height'>
                    <table class='table mb-0 align-middle rounded-lg cmp-bckgrnd-grey table-hover'>
                        <thead>
                            <tr>
                                <th class='text-center align-middle cmp-border-none'>" . get_string('question', 'plagiarism_compilatio') . "</th>
                                <th class='text-center align-middle cmp-border-none'>" . get_string('start_analysis', 'plagiarism_compilatio') . "</th>
                            </tr>
                        </thead>
                    </table>
                    <input class='btn btn-primary' id='start_analysis_selected_questions_btn' type='submit' value='" .get_string('start_selected_questions_analysis', 'plagiarism_compilatio'). "'>
                </div>
            </div>";

        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'start_analysis_selected_questions', [$CFG->httpswwwroot, $cmid]);
        return $output;
    }

}
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

use mod_quiz\question\bank\qbank_helper;

/**
 * CompilatioSettings class
 */
class CompilatioAnalysisOptions {

    public static function get_analysis_options($cmid) {

        global $DB, $PAGE, $CFG, $SESSION;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $compilatio = new CompilatioAPI();

        $sql = "SELECT {quiz}.id
            FROM {quiz}
            INNER JOIN {course_modules} ON {course_modules}.instance = {quiz}.id
            WHERE {course_modules}.id = ?";

        $quizid = $DB->get_field_sql($sql, [$cmid]);

        $modulecontext = context_module::instance($cmid);
        $quizquestions = qbank_helper::get_question_structure($quizid, $modulecontext);
        $output = "
            <div class='cmp-relative-position'>
                <div class='cmp-table-height'>
                    <table class='table mb-0 align-middle rounded-lg cmp-bckgrnd-grey table-hover'>
                        <thead class='thead-light'>
                            <tr>
                                <th class='text-center align-middle'>" . get_string('question', 'plagiarism_compilatio') . "</th>
                                <th class='text-center align-middle'>" . get_string('title_sent', 'plagiarism_compilatio') . "</th>
                            </tr>
                        </thead>
                        <tbody class='cmp-bckgrnd-white'>";

        foreach ($quizquestions as $quizquestion) {
            $output .= "<tr>
                            <td class='text-center align-middle'>" . get_string('question', 'plagiarism_compilatio') . " " . $quizquestion->slot . "</td>
                            <td class='text-center align-middle'> ";

            $output.= $quizquestion->qtype == 'essay' 
                    ? "<button class='btn btn-primary start-analysis-btn' 
                            data-question-id='" . $quizquestion->questionid . "'
                            id='start_analysis_selected_questions_btn_" . $quizquestion->slot . "' 
                            type='button'
                        >
                            <i class='fa fa-play-circle'></i>
                        </button>" 
                    : "<div class='font-italic'>" . get_string('invalid_question_type', 'plagiarism_compilatio') . "</div>";

            $output .="</td>
                        </tr>";
        }
        
        $output .= "</tbody>
                    </table>
                </div>
            </div>";
        
        $PAGE->requires->js_call_amd('plagiarism_compilatio/compilatio_ajax_api', 'start_analysis_selected_questions', [$CFG->httpswwwroot, $cmid, $quizid]);
        return $output;
    }

}
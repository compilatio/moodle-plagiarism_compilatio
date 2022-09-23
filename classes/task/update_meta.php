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
 * update_meta.php - Contains Plagiarism plugin update_meta task.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

/**
 * Task class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_meta extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('update_meta', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        // Update the "Compilatio unavailable" marker in the database.
        $compilatio = new \CompilatioService("test");
        if ($compilatio->check_apikey() == "Forbidden ! Your api key is invalid") {
            set_config('connection_webservice', 1, 'plagiarism_compilatio');
        } else {
            set_config('connection_webservice', 0, 'plagiarism_compilatio');
        }

        $compilatio = new \CompilatioService(get_config('plagiarism_compilatio', 'apikey'));

        // Send data about plugin version to Compilatio.
        $language = $CFG->lang;
        $releasephp = phpversion();
        $releasemoodle = $CFG->release;
        $releaseplugin = get_config('plagiarism_compilatio', 'version');
        $cronfrequency = get_config('plagiarism_compilatio', 'cron_frequency');
        if ($cronfrequency == null) {
            $cronfrequency = 0;
        }
        $compilatio->set_moodle_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency);

        // Update compilatio config.
        $config = $compilatio->get_config();
        set_config('min_word', $config->minDocumentWord, 'plagiarism_compilatio');
        set_config('max_word', $config->maxDocumentWord, 'plagiarism_compilatio');
        set_config('max_size', $config->maxDocumentSize, 'plagiarism_compilatio');

        set_config('helpcenter_admin', $config->zendeskPages->moodle_admin, 'plagiarism_compilatio');
        set_config('helpcenter_teacher', $config->zendeskPages->moodle_teacher, 'plagiarism_compilatio');

        set_config('file_types', json_encode($compilatio->get_allowed_file_types()), 'plagiarism_compilatio');
    }
}

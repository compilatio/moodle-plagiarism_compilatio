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
 * @package    plagiarism_cmp
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_cmp\task;

require_once($CFG->dirroot . '/plagiarism/cmp/classes/compilatio/api.php');

/**
 * Task class
 */
class update_meta extends \core\task\scheduled_task {

    /**
     * Get the task name
     * @return string Name
     */
    public function get_name() {
        return get_string('update_meta', 'plagiarism_cmp');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/plagiarism/cmp/lib.php');

        // Update the 'Compilatio unavailable' marker in the database.
        $compilatio = new \CompilatioAPI('test');
        if ($compilatio->check_apikey() == 'Forbidden ! Your api key is invalid') {
            set_config('connection_webservice', 1, 'plagiarism_cmp');
        } else {
            set_config('connection_webservice', 0, 'plagiarism_cmp');
        }

        $compilatio = new \CompilatioAPI(get_config('plagiarism_cmp', 'apikey'));

        // Send data about plugin version to Compilatio.
        $language = $CFG->lang;
        $releasephp = phpversion();
        $releasemoodle = $CFG->release;
        $releaseplugin = get_config('plagiarism_cmp', 'version');
        $cronfrequency = get_config('plagiarism_cmp', 'cron_frequency');
        if ($cronfrequency == null) {
            $cronfrequency = 0;
        }
        $compilatio->set_moodle_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency);

        // Update compilatio config.
        $config = $compilatio->get_config();

        if (!empty($config)) {
            set_config('min_word', $config->minDocumentWord, 'plagiarism_cmp');
            set_config('max_word', $config->maxDocumentWord, 'plagiarism_cmp');
            set_config('max_size', $config->maxDocumentSize, 'plagiarism_cmp');

            set_config('helpcenter_admin', $config->zendeskPages->moodle_admin, 'plagiarism_cmp');
            set_config('helpcenter_teacher', $config->zendeskPages->moodle_teacher, 'plagiarism_cmp');
            //set_config('last_plugin_version', $config->moodle->lastVersion, 'plagiarism_cmp');
        }

        $filetypes = $compilatio->get_allowed_file_types();

        if (!empty($filetypes)) {
            set_config('file_types', json_encode($filetypes), 'plagiarism_cmp');
        }
    }
}

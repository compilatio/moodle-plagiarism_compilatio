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
 * update_meta.php - Contains update_meta task.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\task;

use communication_matrix\local\spec\v1p1;
use plagiarism_compilatio\compilatio\api;

/**
 * Update_meta task class
 */
class update_meta extends \core\task\scheduled_task {
    /**
     * Get the task name
     * @return string Name
     */
    public function get_name(): string {
        return get_string('update_meta', 'plagiarism_compilatio');
    }

    /**
     * Execute the task
     * @return void
     */
    public function execute(): void {
        global $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        // Update the 'Compilatio unavailable' marker in the database.
        $compilatio = new api(null, 'test');
        if ($compilatio->check_apikey() == 'Forbidden ! Your api key is invalid') {
            set_config('connection_webservice', 1, 'plagiarism_compilatio');
        } else if (!$compilatio->is_in_maintenance()) {
            set_config('connection_webservice', 0, 'plagiarism_compilatio');
        }

        $instancekey = get_config('plagiarism_compilatio', 'instance_key');
        if (empty($instancekey)) {
            $instancekey = sha1(microtime() . getmypid() . random_bytes(50));
            set_config('instance_key', $instancekey, 'plagiarism_compilatio');
        }

        $compilatio = new api();
        $compilatio->check_apikey();
        $compilatio->set_moodle_configuration(
            phpversion(),
            $CFG->release,
            get_config('plagiarism_compilatio', 'version'),
            $CFG->lang,
            get_config('plagiarism_compilatio', 'cron_frequency') ?? 0,
            $instancekey
        );

        // Update compilatio config.
        $config = $compilatio->get_config();
        if (!empty($config)) {
            set_config('min_word', $config->min_document_word, 'plagiarism_compilatio');
            set_config('max_word', $config->max_document_word, 'plagiarism_compilatio');
            set_config('max_size', $config->max_document_size, 'plagiarism_compilatio');

            set_config('helpcenter_admin', $config->zendesk_pages->moodle_admin, 'plagiarism_compilatio');
            set_config('helpcenter_teacher', $config->zendesk_pages->moodle_teacher, 'plagiarism_compilatio');
            set_config('helpcenter_service_status', $config->zendesk_pages->service_status, 'plagiarism_compilatio');

            set_config('supported_languages', json_encode($config->supported_languages), 'plagiarism_compilatio');
            set_config('file_types', json_encode($config->allowed_extensions_for_document), 'plagiarism_compilatio');
        }
    }
}

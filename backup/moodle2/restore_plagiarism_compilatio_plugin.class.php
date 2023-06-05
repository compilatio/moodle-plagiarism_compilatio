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
 * restore_plagiarism_compilatio_plugin.class.php - Contains Plagiarism plugin methods to restore the plugin.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore class
 */
class restore_plagiarism_compilatio_plugin extends restore_plagiarism_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     * @return  void
     */
    protected function define_course_plugin_structure() {
        $paths = [];

        // Add own format stuff.
        $elename = 'compilatioconfig';
        $elepath = $this->get_pathfor('compilatio_configs/compilatio_config');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process configuration
     * @param  object $data Data
     * @return void
     */
    public function process_compilatioconfig($data) {
        $data = (object)$data;

        if ($this->task->is_samesite()) { // Files can only be restored if this is the same site as was backed up.
            // Only restore if a link to this course doesn't already exist in this install.
            set_config($this->task->get_courseid(), $data->value, $data->plugin);
        }
    }

    /**
     * Returns the paths to be handled by the plugin at module level
     * @return  array Paths
     */
    protected function define_module_plugin_structure() {
        $paths = [];

        // Add own format stuff.
        $elename = 'compilatiomodule';
        $elepath = $this->get_pathfor('compilatio_modules/compilatio_module');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'compilatiofiles';
        $elepath = $this->get_pathfor('/compilatio_files/compilatio_file');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.

    }

    /**
     * Process configuration
     * @param  object $data Data
     * @return void
     */
    public function process_compilatiomodule($data) {
        global $DB;

        if ($this->task->is_samesite()) { // Files can only be restored if this is the same site as was backed up.
            $data = (object)$data;
            $oldid = $data->id;
            $data->cmid = $this->task->get_moduleid();

            $DB->insert_record('plagiarism_compilatio_cm_cfg', $data);
        }
    }

    /**
     * Process file configuration
     * @param  object $data Data
     * @return void
     */
    public function process_compilatiofiles($data) {
        global $DB;

        if ($this->task->is_samesite()) { // Files can only be restored if this is the same site as was backed up.
            $data = (object)$data;
            $oldid = $data->id;
            $data->cm = $this->task->get_moduleid();
            $data->userid = $this->get_mappingid('user', $data->userid);

            $DB->insert_record('plagiarism_compilatio_file', $data);
        }
    }
}

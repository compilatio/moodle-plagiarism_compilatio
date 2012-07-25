<?php

defined('MOODLE_INTERNAL') || die();


class restore_plagiarism_compilatio_plugin extends restore_plagiarism_plugin {
    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        // Add own format stuff
        $elename = 'compilatioconfig';
        $elepath = $this->get_pathfor('compilatio_configs/compilatio_config'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths
    }

    public function process_compilatioconfig($data) {
        $data = (object)$data;

        if ($this->task->is_samesite()) { //files can only be restored if this is the same site as was backed up.
            //only restore if a link to this course doesn't already exist in this install.
            set_config($this->task->get_courseid(), $data->value, $data->plugin);
        }
    }

    /**
     * Returns the paths to be handled by the plugin at module level
     */
    protected function define_module_plugin_structure() {
        $paths = array();

        // Add own format stuff
        $elename = 'compilatioconfigmod';
        $elepath = $this->get_pathfor('compilatio_configs/compilatio_config'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'compilatiofiles';
        $elepath = $this->get_pathfor('/compilatio_files/compilatio_file'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths

    }

    public function process_compilatioconfigmod($data) {
        global $DB;

        if ($this->task->is_samesite()) { //files can only be restored if this is the same site as was backed up.
            $data = (object)$data;
            $oldid = $data->id;
            $data->cm = $this->task->get_moduleid();

            $DB->insert_record('plagiarism_compilatio_config', $data);
        }
    }

    public function process_compilatiofiles($data) {
        global $DB;

        if ($this->task->is_samesite()) { //files can only be restored if this is the same site as was backed up.
            $data = (object)$data;
            $oldid = $data->id;
            $data->cm = $this->task->get_moduleid();
            $data->userid = $this->get_mappingid('user', $data->userid);

            $DB->insert_record('plagiarism_compilatio_files', $data);
        }
    }
}
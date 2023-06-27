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
 * backup_plagiarism_compilatio_plugin.class.php - Contains Plagiarism plugin methods to backup the plugin.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Backup class
 */
class backup_plagiarism_compilatio_plugin extends backup_plagiarism_plugin {

    /**
     * Define the plugin's structure
     * @return object Structure object
     */
    protected function define_module_plugin_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        $compilatioconfigs = new backup_nested_element('compilatio_modules');
        $compilatioconfig = new backup_nested_element('compilatio_module', ['id'],
            ['folderid', 'userid', 'activated', 'showstudentreport', 'showstudentscore', 'studentanalyses',
                'analysistype', 'analysistime', 'warningthreshold', 'criticalthreshold', 'defaultindexing']);
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('plagiarism_compilatio_module', ['cmid' => backup::VAR_PARENTID]);

        // Now information about files to module.
        $compilatiofiles = new backup_nested_element('compilatio_files');
        $compilatiofile = new backup_nested_element('compilatio_file', ['id'],
            ['userid', 'identifier', 'filename', 'externalid',
                'status', 'globalscore', 'aiscore', 'utlscore', 'similarityscore', 'timesubmitted', 'indexed', 'reporturl']);

        $pluginwrapper->add_child($compilatiofiles);
        $compilatiofiles->add_child($compilatiofile);
        if ($userinfo) {
            $compilatiofile->set_source_table('plagiarism_compilatio_file', ['cm' => backup::VAR_PARENTID]);
        }
        return $plugin;
    }

    /**
     * Define course plugin structure
     * @return object Plugin structure object
     */
    protected function define_course_plugin_structure() {
        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Save id from compilatio course.
        $compilatioconfigs = new backup_nested_element('compilatio_configs');
        $compilatioconfig = new backup_nested_element('compilatio_config', ['id'], ['plugin', 'name', 'value']);
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('config_plugins', ['name' => backup::VAR_PARENTID,
            'plugin' => backup_helper::is_sqlparam('plagiarism_compilatio_course')]);
        return $plugin;
    }
}

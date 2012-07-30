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

defined('MOODLE_INTERNAL') || die();


class backup_plagiarism_compilatio_plugin extends backup_plagiarism_plugin {
    protected function define_module_plugin_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        $compilatioconfigs = new backup_nested_element('compilatio_configs');
        $compilatioconfig = new backup_nested_element('compilatio_config', array('id'), array('name', 'value'));
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('plagiarism_compilatio_config', array('cm' => backup::VAR_PARENTID));

        // Now information about files to module.
        $compilatiofiles = new backup_nested_element('compilatio_files');
        $compilatiofile = new backup_nested_element('compilatio_file', array('id'),
                            array('userid', 'identifier', 'filename', 'reporturl', 'optout',
                                  'statuscode', 'similarityscore', 'errorresponse', 'timesubmitted'));

        $pluginwrapper->add_child($compilatiofiles);
        $compilatiofiles->add_child($compilatiofile);
        if ($userinfo) {
            $compilatiofile->set_source_table('plagiarism_compilatio_files', array('cm' => backup::VAR_PARENTID));
        }
        return $plugin;
    }

    protected function define_course_plugin_structure() {
        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Save id from compilatio course.
        $compilatioconfigs = new backup_nested_element('compilatio_configs');
        $compilatioconfig = new backup_nested_element('compilatio_config', array('id'), array('plugin', 'name', 'value'));
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('config_plugins', array('name'=> backup::VAR_PARENTID,
            'plugin' => backup_helper::is_sqlparam('plagiarism_compilatio_course')));
        return $plugin;
    }
}
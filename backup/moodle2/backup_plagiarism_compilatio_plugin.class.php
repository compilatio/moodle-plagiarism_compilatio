<?php

defined('MOODLE_INTERNAL') || die();


class backup_plagiarism_compilatio_plugin extends backup_plagiarism_plugin {
    function define_module_plugin_structure() {
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        $compilatioconfigs = new backup_nested_element('compilatio_configs');
        $compilatioconfig = new backup_nested_element('compilatio_config', array('id'), array('name', 'value'));
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('plagiarism_compilatio_config', array('cm' => backup::VAR_PARENTID));

        //now information about files to module
        $compilatiofiles = new backup_nested_element('compilatio_files');
        $compilatiofile = new backup_nested_element('compilatio_file', array('id'),
                            array('userid', 'identifier','filename','reporturl','optout','statuscode','similarityscore','errorresponse','timesubmitted'));

        $pluginwrapper->add_child($compilatiofiles);
        $compilatiofiles->add_child($compilatiofile);
        if ($userinfo) {
            $compilatiofile->set_source_table('plagiarism_compilatio_files', array('cm' => backup::VAR_PARENTID));
        }
        return $plugin;
    }

    function define_course_plugin_structure() {
        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);
        //save id from compilatio course
        $compilatioconfigs = new backup_nested_element('compilatio_configs');
        $compilatioconfig = new backup_nested_element('compilatio_config', array('id'), array('plugin', 'name', 'value'));
        $pluginwrapper->add_child($compilatioconfigs);
        $compilatioconfigs->add_child($compilatioconfig);
        $compilatioconfig->set_source_table('config_plugins', array('name'=> backup::VAR_PARENTID, 'plugin' => backup_helper::is_sqlparam('plagiarism_compilatio_course')));
        return $plugin;
    }
}
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
// This file keeps track of upgrades to
// the plagiarism compilatio plugin.

/**
 * upgrade.php - Contains class to upgrade the plugin database between differents versions.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use plagiarism_compilatio\task\update_meta;

/**
 * Method to upgrade the database between differents versions
 *
 * @param  int  $oldversion Old version
 * @return bool Return true if succeed, false otherwise
 */
function xmldb_plagiarism_compilatio_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    $filestable = new xmldb_table('plagiarism_compilatio_files');
    if ($dbman->table_exists($filestable)) {
        $similarityscorefield = new xmldb_field('similarityscore', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);

        if ($dbman->field_exists($filestable, $similarityscorefield)) {
            $dbman->rename_field($filestable, $similarityscorefield, 'globalscore');
        }
    }

    $schema = new xmldb_structure('db');
    $schema->setVersion($CFG->version);
    $xmldbfile = new xmldb_file($CFG->dirroot . '/plagiarism/compilatio/db/install.xml');
    $xmldbfile->loadXMLStructure();
    $structure = $xmldbfile->getStructure();
    $tables = $structure->getTables();
    foreach ($tables as $table) {
        $table->setPrevious(null);
        $table->setNext(null);
        $schema->addTable($table);
    }

    $dbchecks = $dbman->check_database_schema($schema);
    $compilatiodbchecks = [];
    foreach ($dbchecks as $tablename => $results) {
        if (strpos($tablename, 'compilatio')) {
            $compilatiodbchecks[$tablename] = $results;
        }
    }

    $tablestodelete = [];
    $fieldstodelete = [];

    echo $OUTPUT->box_start('generalbox boxaligncenter');

    foreach ($compilatiodbchecks as $tablename => $results) {

        echo("<h4>" . $tablename . "</h4><p>");

        foreach ($results as $message) {
            echo($message . " => ");

            // Tables changes.
            if (preg_match("/^table is (.*)/", $message, $matches)) {
                if ($matches[1] == 'missing') {
                    $table = $schema->getTable($tablename);
                    $dbman->create_table($table);
                    echo("create table '" . $tablename . "'");
                }
                if ($matches[1] == 'not expected') {
                    $table = new xmldb_table($tablename);
                    $tablestodelete[] = $table;
                    echo("drop table '" . $tablename . "'");
                }
            }

            // Fields changes.
            if (preg_match("/^column '(.*?)' (.*?)(,| '| \(|$)/", $message, $matches)) {
                $table = new xmldb_table($tablename);

                if (strpos($matches[2], 'is not expected') === 0) {
                    $field = new xmldb_field($matches[1]);
                    echo('check for indexes before droping field => ');
                    $indexes = $DB->get_indexes($tablename);
                    foreach ($indexes as $k => $idx) {
                        if (in_array($matches[1], $idx['columns'])) {
                            echo('index ' . $k . 'found => ');
                            $indexname      = $k;
                            $indextype      = $idx['unique'];
                            $indexfields    = $idx['columns'];
                            $index          = new xmldb_index($indexname, $indextype, $indexfields);
                            $dbman->drop_index($table, $index);
                            echo("drop index '" . $k . "' => ");
                        }
                    }
                    $fieldstodelete[] = ['table' => $table, 'field' => $field];
                    echo("drop field '" . $matches[1] . "'");
                } else {
                    $field = $schema->getTable($tablename)->getField($matches[1]);
                    if ($matches[2] == 'is missing') {
                        $dbman->add_field($table, $field);
                        echo("add field '" . $matches[1] . "'");
                    }
                    if ($matches[2] == 'should be NOT NULL') {
                        $dbman->change_field_notnull($table, $field);
                        echo("change '" . $matches[1] . "' not null");
                    }
                    if ($matches[2] == 'has default') {
                        $dbman->change_field_default($table, $field);
                        echo("change default value for '" . $matches[1] . "'");
                    }
                    if (in_array($matches[2],
                        ['should allow NULL', 'has unknown type', 'has incorrect type', 'has unsupported type'])) {
                        $dbman->change_field_type($table, $field);
                        echo("change type for '" . $matches[1] . "'");
                    }
                }
            }

            // Indexes changes.
            if (preg_match("/^(.*?) index '(.*?)'/", $message, $matches)) {
                $table = new xmldb_table($tablename);
                if ($matches[1] == 'Unexpected') {
                    $indexes        = $DB->get_indexes($tablename);
                    $indexname      = $matches[2];
                    $indextype      = $indexes[$matches[2]]['unique'];
                    $indexfields    = $indexes[$matches[2]]['columns'];
                    $index          = new xmldb_index($indexname, $indextype, $indexfields);
                    $dbman->drop_index($table, $index);
                    echo("drop index '" . $matches[2] . "'");
                }
                if ($matches[1] == 'Missing') {
                    if (($index = $schema->getTable($tablename)->getIndex($matches[2])) !== null) {
                        $dbman->add_index($table, $index);
                    } else {
                        $index = $schema->getTable($tablename)->getKey($matches[2]);
                        $dbman->add_key($table, $index);
                    }
                    echo("add index '" . $matches[2] . "'");
                }
            }

            echo("<br />");
        }
        echo("</p>");
    }
    echo("<p>Compilatio tables structure has been checked.</p>");
    echo $OUTPUT->box_end();

    if ($oldversion <= 2015081400) {
        $DB->execute("UPDATE {plagiarism_compilatio_config} SET value='1' WHERE name='compilatio_analysistype' AND cm=0");
        upgrade_plugin_savepoint(true, 2015081400, 'plagiarism', 'compilatio');
    }

    // Get plugin configuration.
    $legacyconfig = (array) get_config('plagiarism');
    $newconfig = (array) get_config('plagiarism_compilatio');

    // Writes the new plugin configuration with legacy values.
    foreach ($legacyconfig as $k => $v) {
        if (strpos($k, 'compilatio_') === 0) {
            if ($k == 'compilatio_use') {
                $newname = 'enabled';
                // Forces old 'compilatio_use' to '1'. Enabling plugin will be deffered to 'enabled' parameter.
                try {
                    set_config('compilatio_use', '1', 'plagiarism');
                } catch (Exception $e) {
                    throw new moodle_exception("Failed to set plagiarism:compilatio_use to 1");
                    return false;
                }
            } else {
                $newname = substr($k, 11);
            }
            if (!isset($newconfig[$newname])) {
                try {
                    set_config($newname, $v, 'plagiarism_compilatio');
                } catch (Exception $e) {
                    throw new moodle_exception("Failed to set plagiarism_compilatio:" . $newname . " to " . $v);
                    return false;
                }
                if ($k != 'compilatio_use' || $CFG->version >= 2020061500) {
                    if (!unset_config($k, 'plagiarism')) {
                        throw new moodle_exception("Failed to unset plagiarism:" . $k);
                        return false;
                    }
                }
            }
        }
    }

    if ($CFG->version >= 2020061500) {
        unset_config('compilatio_use', 'plagiarism');
    }

    if ($oldversion < 2021011100) {

        $url = get_config('plagiarism_compilatio', 'api');
        $key = get_config('plagiarism_compilatio', 'password');

        $apikey = new stdclass();
        $apikey->url = $url;
        $apikey->api_key = $key;
        $apikeyid = $DB->insert_record('plagiarism_compilatio_apicon', $apikey);

        unset_config('api', 'plagiarism_compilatio');
        unset_config('password', 'plagiarism_compilatio');

        set_config('apiconfigid', $apikeyid, 'plagiarism_compilatio');

        upgrade_plugin_savepoint(true, 2021011100, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2021012500) {
        set_config('allow_search_tab', 0, 'plagiarism_compilatio');
        $DB->execute("UPDATE {plagiarism_compilatio_config} SET value='1' WHERE name='compilatio_analysistype' AND value='0'");
        upgrade_plugin_savepoint(true, 2021012500, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2021021800) {
        compilatio_update_meta();
        upgrade_plugin_savepoint(true, 2021021800, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2021062300) {
        $cms = $DB->get_records_sql('SELECT distinct cm FROM {plagiarism_compilatio_config}');
        foreach ($cms as $cm) {
            $newelement = new Stdclass();
            $newelement->cm = $cm->cm;
            $newelement->name = "compi_student_analyses";
            $newelement->value = 0;
            $DB->insert_record('plagiarism_compilatio_config', $newelement);
        }
        upgrade_plugin_savepoint(true, 2021062300, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2024022600) {
        // API key.

        $apikey = get_config('plagiarism_compilatio', 'apikey');
        if (empty($apikey)) {
            $apiconfigid = get_config('plagiarism_compilatio', 'apiconfigid');
            $apikey = $DB->get_field('plagiarism_compilatio_apicon', 'api_key', ['id' => $apiconfigid]);
            set_config('apikey', $apikey, 'plagiarism_compilatio');
        }

        require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
        $compilatio = new CompilatioAPI(null, $apikey);

        $compilatioid = $compilatio->get_apikey_user_id();
        $DB->insert_record('plagiarism_compilatio_user', (object) ['userid' => 0, 'compilatioid' => $compilatioid]);

        // Plugin settings.
        $settings = [
            'allow_analyses_auto'            => 'enable_analyses_auto',
            'allow_teachers_to_show_reports' => 'enable_show_reports',
            'allow_student_analyses'         => 'enable_student_analyses',
            'allow_search_tab'               => 'enable_search_tab',
        ];

        foreach ($settings as $oldsetting => $newsetting) {
            $value = get_config('plagiarism_compilatio', $oldsetting);

            if (!empty($value)) {
                set_config($newsetting, $value, 'plagiarism_compilatio');
                unset_config($oldsetting, 'plagiarism_compilatio');
            }
        }

        $settings = ['nb_mots_max', 'nb_mots_min', 'file_max_size', 'apiconfigid', 'idgroupe'];

        foreach ($settings as $setting) {
            unset_config($setting, 'plagiarism_compilatio');
        }

        // Course modules config.
        $cmids = $DB->get_fieldset_sql("SELECT DISTINCT cm FROM {plagiarism_compilatio_config}");

        foreach ($cmids as $cmid) {
            $config = $DB->get_records_menu('plagiarism_compilatio_config', ['cm' => $cmid], '', 'name, value');

            if (empty($config)) {
                continue;
            }

            $analysistype = [0 => 'auto', 1 => 'manual', 2 => 'planned'];
            $showstudent = [0 => 'never', 1 => 'immediately', 2 => 'closed'];

            $v3config = (object) [
                'cmid'              => $cmid,
                'activated'         => $config['use_compilatio'],
                'showstudentscore'  => $showstudent[$config['compilatio_show_student_score']],
                'showstudentreport' => $showstudent[$config['compilatio_show_student_report']],
                'studentanalyses'   => $config['compi_student_analyses'],
                'analysistype'      => $analysistype[$config['compilatio_analysistype']],
                'analysistime'      => $config['compilatio_timeanalyse'],
                'warningthreshold'  => $config['green_threshold'],
                'criticalthreshold' => $config['orange_threshold'],
                'defaultindexing'   => $config['indexing_state'],
            ];

            $DB->insert_record('plagiarism_compilatio_cm_cfg', $v3config);
            $DB->delete_records('plagiarism_compilatio_config', ['cm' => $cmid]);
        }

        // Recycle bin ids.
        $sql = "SELECT DISTINCT cm, recyclebinid FROM {plagiarism_compilatio_files} WHERE recyclebinid IS NOT NULL";
        $recyclebins = $DB->get_records_sql($sql);

        foreach ($recyclebins as $recyclebin) {
            $cmcfg = $DB->get_record('plagiarism_compilatio_cm_cfg', ['cmid' => $recyclebin->cm]);
            $cmcfg->recyclebinid = $recyclebin->recyclebinid;
            $DB->update_record('plagiarism_compilatio_cm_cfg', $cmcfg);
        }

        // Files.
        $status = [
            201 => 'sent',
            202 => 'sent',
            203 => 'analysing',
            404 => 'error_not_found',
            412 => 'error_too_short',
            413 => 'error_too_large',
            414 => 'error_too_long',
            415 => 'error_unsupported',
            416 => 'error_sending_failed',
            418 => 'error_analysis_failed',
            'Analyzed' => 'scored',
            'In queue' => 'queue',
            'pending' => 'error_sending_failed',
        ];

        foreach ($status as $oldstatus => $newstatus) {
            $DB->execute(
                "UPDATE {plagiarism_compilatio_files} SET status = ? WHERE statuscode = ?",
                [$newstatus, $oldstatus]
            );
        }

        $DB->execute("UPDATE {plagiarism_compilatio_files} SET reporturl = NULL WHERE LENGTH(reporturl) = 40");

        $updatemeta = new update_meta();
        $updatemeta->execute();

        upgrade_plugin_savepoint(true, 2024022600, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2024030100) {
        $compilatioid = $DB->get_field('plagiarism_compilatio_user', 'compilatioid', ['userid' => 0]);

        if (!preg_match('/^[a-f0-9]{40}$/', $compilatioid)) {
            $apikey = get_config('plagiarism_compilatio', 'apikey');
            if (!empty($apikey)) {
                require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');
                $compilatio = new CompilatioAPI(null, $apikey);

                $compilatioid = $compilatio->get_apikey_user_id(false);

                if (preg_match('/^[a-f0-9]{40}$/', $compilatioid)) {
                    $DB->delete_records('plagiarism_compilatio_user', ['userid' => 0]);
                    $DB->insert_record('plagiarism_compilatio_user', (object) ['userid' => 0, 'compilatioid' => $compilatioid]);
                }
            }
        }

        upgrade_plugin_savepoint(true, 2024030100, 'plagiarism', 'compilatio');
    }

    foreach ($tablestodelete as $table) {
        $dbman->drop_table($table);
    }

    foreach ($fieldstodelete as $field) {
        $dbman->drop_field($field['table'], $field['field']);
    }

    return true;
}

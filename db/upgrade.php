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
 * upgrade.php - Contains Plagiarism plugin class to upgrade the database between differents versions.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Method to upgrade the database between differents versions
 *
 * @param  int  $oldversion Old version
 * @return bool Return true if succeed, false otherwise
 */
function xmldb_plagiarism_compilatio_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    $schema = new xmldb_structure('db');
    $schema->setVersion($CFG->version);
    $xmldb_file = new xmldb_file($CFG->dirroot . '/plagiarism/compilatio/db/install.xml');
    $xmldb_file->loadXMLStructure();
    $structure = $xmldb_file->getStructure();
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
                    $dbman->drop_table($table);
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
                    $dbman->drop_field($table, $field);
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

    return true;
}

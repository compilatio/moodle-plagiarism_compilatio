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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Method to upgrade the database between differents versions
 *
 * @param  int  $oldversion Old version
 * @return bool Return true if succeed, false otherwise
 */
function xmldb_plagiarism_compilatio_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion <= 2015081400) {
        $DB->execute("UPDATE {plagiarism_compilatio_config} SET value='1' WHERE name='compilatio_analysistype' AND cm=0");

        upgrade_plugin_savepoint(true, 2015081400, 'plagiarism', 'compilatio');
    }

    if ($oldversion <= 2014111000) {

        // Define table plagiarism_compilatio_data to be created.
        $table = new xmldb_table('plagiarism_compilatio_data');

        // Adding fields to table plagiarism_compilatio_data.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table plagiarism_compilatio_data.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('name-unique', XMLDB_KEY_UNIQUE, array('name'));

        // Conditionally launch create table for plagiarism_compilatio_data.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table plagiarism_compilatio_news to be created.
        $table = new xmldb_table('plagiarism_compilatio_news');

        // Adding fields to table plagiarism_compilatio_news.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('id_compilatio', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message_fr', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('message_en', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('begin_display_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('end_display_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table plagiarism_compilatio_news.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('unique_id_compilatio', XMLDB_KEY_UNIQUE, array('id_compilatio'));

        // Conditionally launch create table for plagiarism_compilatio_news.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Compilatio savepoint reached.
        upgrade_plugin_savepoint(true, 2014111000, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2020111200) {
        $table = new xmldb_table('plagiarism_compilatio_files');
        $field = new xmldb_field('recyclebinid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $dbman->add_field($table, $field);

        upgrade_plugin_savepoint(true, 2020111200, 'plagiarism', 'compilatio');
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
                    print_error("Failed to set plagiarism:compilatio_use to 1");
                    return false;
                }
            } else {
                $newname = substr($k, 11);
            }
            if (!isset($newconfig[$newname])) {
                try {
                    set_config($newname, $v, 'plagiarism_compilatio');
                } catch (Exception $e) {
                    print_error("Failed to set plagiarism_compilatio:" . $newname . " to " . $v);
                    return false;
                }
                if ($k != 'compilatio_use' || $CFG->version >= 2020061500) {
                    if (!unset_config($k, 'plagiarism')) {
                        print_error("Failed to unset plagiarism:" . $k);
                        return false;
                    }
                }
            }
        }
    }

    if ($oldversion < 2021011100) {
        $table = new xmldb_table('plagiarism_compilatio_files');
        $field = new xmldb_field('apiconfigid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1);
        $dbman->add_field($table, $field);

        $table = new xmldb_table('plagiarism_compilatio_apicon');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('api_key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

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

        $table = new xmldb_table('plagiarism_compilatio_files');
        $field = new xmldb_field('idcourt', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $dbman->add_field($table, $field);

        upgrade_plugin_savepoint(true, 2021012500, 'plagiarism', 'compilatio');
    }

    if ($oldversion < 2021021800) {
        compilatio_update_meta();
    }

    return true;
}

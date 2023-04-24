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
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $tableschecks = [
        'plagiarism_compilatio_news' => [
            'field' => [
                'change_notnull' => [
                    ['type', XMLDB_TYPE_INTEGER, '1', null, null, null, null]
                ],
                'add' => [
                    ['id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null],
                    ['type', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null],
                    ['message_fr', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['message_en', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['message_pt', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['message_es', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['message_de', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['message_it', XMLDB_TYPE_TEXT, null, null, null, null, null],
                    ['begin_display_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null],
                    ['end_display_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null]
                ],
                'drop' => [
                    ['id_compilatio']
                ],
            ],
            'index' => [
                'drop' => [
                    ['mdl_plagcompnews_id__uix', XMLDB_INDEX_UNIQUE, array('id_compilatio')]
                ],
                'add' => [
                    ['primary', XMLDB_KEY_PRIMARY, array('id')]
                ]
            ],
        ],
        'plagiarism_compilatio_data' => [
            'field' => [
                'add' => [
                    ['id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null],
                    ['name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null],
                    ['value', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null],
                ]
                ],
            'index' => [
                'add' => [
                    ['primary', XMLDB_KEY_PRIMARY, array('id')],
                    ['name-unique', XMLDB_KEY_UNIQUE, array('name')]
                ]
            ]
        ],
        'plagiarism_compilatio_files' => [
            'field' => [
                'add' => [
                    ['recyclebinid', XMLDB_TYPE_INTEGER, '10', null, null, null, null],
                    ['apiconfigid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1],
                    ['idcourt', XMLDB_TYPE_CHAR, '10', null, null, null, null],
                    ['migrationstatus', XMLDB_TYPE_INTEGER, '10', null, null, null, null],
                    ['objectid', XMLDB_TYPE_INTEGER, '10', null, null, null, null]
                ]
            ],
            'index' => [
                'add' => [
                    ['mdl_cmp_files_extid', false, array('externalid')]
                ]
            ]
        ],
        'plagiarism_compilatio_apicon' => [
            'field' => [
                'add' => [
                   ['id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null],
                   ['url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null],
                   ['api_key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null],
                   ['startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0]
                ]
            ],
            'index' => [
                'add' => [
                   ['primary', XMLDB_KEY_PRIMARY, array('id')]
                ]
            ]
        ]
    ];

    // Check tables, fields and indexes.
    foreach ($tableschecks as $tablename => $tocheck) {
        $table = new xmldb_table($tablename);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        foreach ($tocheck as $elt => $actions) {
            foreach ($actions as $actionname => $targets) {
                foreach ($targets as $params) {
                    $xmleltname = 'xmldb_' . $elt;
                    $xmlelt = new $xmleltname(...$params);
                    $nameparts = explode('_', $actionname);
                    $method = (isset($nameparts[1])) ? $nameparts[0] . '_' . $elt . '_' . $nameparts[1] : $actionname . '_' . $elt;
                    $condition = ($actionname == 'add') ? false : true;
                    if ($dbman->{$elt . '_exists'}($table, $xmlelt) === $condition) {
                        $dbman->$method($table, $xmlelt);
                    }
                }
            }
        }
    }

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

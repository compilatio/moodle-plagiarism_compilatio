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
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_plagiarism_compilatio_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
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
        upgrade_plugin_savepoint(true, 2015052000, 'plagiarism', 'compilatio');
    }

    if ($oldversion <= 2015081400) {
        $DB->execute("UPDATE {plagiarism_compilatio_config} SET value='1' WHERE name='compilatio_analysistype' AND cm=0");
    }


    return true;
}

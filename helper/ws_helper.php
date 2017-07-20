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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Helper class to communicate with web services
 */
class ws_helper
{

    /**
     * Get a new instance of web service class
     *
     * @return object   Web service instance
     */
    public static function get_ws() {

        global $CFG;

        $ppc = new plagiarism_plugin_compilatio();
        $plagiarismsettings = $ppc->get_settings();

        return new compilatioservice(
            $plagiarismsettings['compilatio_password'],
            $plagiarismsettings['compilatio_api'],
            $CFG->proxyhost,
            $CFG->proxyport,
            $CFG->proxyuser,
            $CFG->proxypassword
        );

    }

    /**
     * Test connection to Compilatio webservice
     *
     * @return bool false if API key is invalid or if we cannot reach the webservice
     */
    public static function test_connection() {

        $compilatio = self::get_ws();
        $quotasarray = $compilatio->get_quotas();
        return $quotasarray['quotas'] != null;

    }

    /**
     * Get the allowed file max size by Compilatio service.
     *
     * @return AllowedFileMaxSize   Object that contains allowed file max size
     */
    public static function get_allowed_file_max_size() {

        $compilatio = self::get_ws();
        return $compilatio->get_allowed_file_max_size();

    }

    /**
     * Get all allowed file types by Compilatio service.
     *
     * @return CompilatioFileTypes  Object that contains all allowed file types
     */
    public static function get_allowed_file_types() {

        $compilatio = self::get_ws();
        return $compilatio->get_allowed_file_types();

    }

    /**
     * Get the indexing state of a document.
     *
     * @param  string $compid   Document ID
     * @return bool             Indexing state
     */
    public static function get_indexing_state($compid) {

        $compilatio = self::get_ws();
        return $compilatio->get_indexing_state($compid);

    }

    /**
     * Set the indexing state of a document.
     * @param  string $compid           Document ID
     * @param  bool   $indexingstate    Indexing state
     * @return bool                     Return true if the indexing succeed, false otherwise
     */
    public static function set_indexing_state($compid, $indexingstate) {

        $compilatio = self::get_ws();
        return $compilatio->set_indexing_state($compid, $indexingstate);

    }

}
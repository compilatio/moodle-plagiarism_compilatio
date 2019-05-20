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
 * ws_helper.php - Contains Plagiarism plugin helper methods for communicate with the web service.
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
 * Helper class to communicate with web service
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

        return new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);
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
        $filetypes = $compilatio->get_allowed_file_types();
        
        $filtered = array();
 
        // Check and remove duplicates filetypes.
        foreach ($filetypes as $ft) {

            $alreadyknown = false;
            foreach ($filtered as $f) {
                if ($f["type"] == $ft["type"]) {
                    $alreadyknown = true;
                    break;
                }
            }
            if ($alreadyknown === false) {
                $filtered[] = $ft;
            }

        }
        usort($filtered, function ($a, $b) {
            return strcmp($a["type"], $b["type"]);
        });
        
        return $filtered;
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
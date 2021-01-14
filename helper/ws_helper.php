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
     * @param string $compid Document ID
     * @return object Web service instance
     */
    public static function get_ws($compid = false) {

        global $CFG, $DB;

        $ppc = new plagiarism_plugin_compilatio();
        $plagiarismsettings = $ppc->get_settings();

        if (!$compid) {
            $config = $plagiarismsettings['apiconfigid'];
        } else {
            $config = $DB->get_field('plagiarism_compilatio_files', 'apiconfigid', array('externalid' => $compid));
        }

        return compilatioservice::getinstance($config,
            $CFG->proxyhost,
            $CFG->proxyport,
            $CFG->proxyuser,
            $CFG->proxypassword);

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

        global $SESSION;

        if (isset($SESSION->compilatio_allowed_file_types)) {
            return $SESSION->compilatio_allowed_file_types;
        }

        $compilatio = self::get_ws();
        $filetypes = $compilatio->get_allowed_file_types();
        $filtered = array();

        if (is_array($filetypes)) {
            // Check and remove duplicates filetypes.
            foreach ($filetypes as $ft) {
                $alreadyknown = false;
                foreach ($filtered as $f) {
                    if ($f->type == $ft->type) {
                        $alreadyknown = true;
                        break;
                    }
                }
                if ($alreadyknown === false) {
                    $filtered[] = $ft;
                }
            }
            usort($filtered, function ($a, $b) {
                return strcmp($a->type, $b->type);
            });
            $SESSION->compilatio_allowed_file_types = $filtered;
            return $SESSION->compilatio_allowed_file_types;

        } else {
            // Returns safe allowed file types.
            return array(
                (object) array(
                    'type' => 'doc',
                    'title' => 'Microsoft Word',
                    'mimetype' => 'application/msword'
                ),
                (object) array(
                    'type' => 'docx',
                    'title' => 'Microsoft Word',
                    'mimetype' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ),
                (object) array(
                    'type' => 'htm',
                    'title' => 'Web Page',
                    'mimetype' => 'text/html'
                ),
                (object) array(
                    'type' => 'pdf',
                    'title' => 'Adobe Portable Document Format',
                    'mimetype' => 'application/pdf'
                ),
                (object) array(
                    'type' => 'txt',
                    'title' => 'Text File',
                    'mimetype' => 'text/plain'
                ),
                (object) array(
                    'type' => 'odt',
                    'title' => 'OpenDocument Text',
                    'mimetype' => 'application/vnd.oasis.opendocument.text'
                )
            );
        }
    }

    /**
     * Get the indexing state of a document.
     *
     * @param  string $compid   Document ID
     * @return bool             Indexing state
     */
    public static function get_indexing_state($compid) {

        $compilatio = self::get_ws($compid);
        return $compilatio->get_indexing_state($compid);

    }

    /**
     * Set the indexing state of a document.
     * @param  string $compid           Document ID
     * @param  bool   $indexingstate    Indexing state
     * @return bool                     Return true if the indexing succeed, false otherwise
     */
    public static function set_indexing_state($compid, $indexingstate) {

        $compilatio = self::get_ws($compid);
        return $compilatio->set_indexing_state($compid, $indexingstate);

    }

}

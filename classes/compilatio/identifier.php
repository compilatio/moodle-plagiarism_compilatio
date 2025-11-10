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
 * identifier.php - Contains methods about identifier.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2025 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use stored_file;
use moodle_exception;

/**
 * Handle identifier generation.
 */
class identifier {

    /**
     * @var string $userid User id
     */
    public string $userid;

    /**
     * @var string $cmid Course module id
     */
    public string $cmid;

    /**
     * Class constructor
     * @param  string $userid User ID
     * @param  string $cmid Course module ID
     */
    public function __construct($userid, $cmid) {

        if (!isset($userid) || !isset($cmid)) {
            throw new \moodle_exception('No userid or cmid.');
        }

        $this->userid = (string) $userid;
        $this->cmid = (string) $cmid;
    }

    /**
     * create_from_linkarrays
     * @param  array $linkarray Array pass in document_frame containing userid, cmid and content
     *
     * @return string $identifier Generated identifier
     */
    public function create_from_linkarray($linkarray): string {
        if (!is_array($linkarray)) {
            throw new moodle_exception('Linkarray is not an array.');
        }

        if (!empty($linkarray['content'])) {
            return $this->create_from_string($linkarray['content']);
        }

        if (!empty($linkarray['file']) && $linkarray['file'] instanceof stored_file) {
            return $this->create_from_stored_file($linkarray['file']->get_content_file_handle());
        }

        throw new moodle_exception('Linkarray is not online text or stored file.');
    }

    /**
     * create_from_string
     * @param  array $content Content to generate identifier with
     *
     * @return string $identifier Generated identifier
     */
    public function create_from_string($content): string {
        return sha1($content . $this->userid . $this->cmid);
    }

    /**
     * create_from_file
     * @param  std_class $file File to generate identifier with
     *
     * @return string $identifier Generated identifier
     */
    public function create_from_file($file): string {
        if ($file instanceof stored_file) {
            return $this->create_from_stored_file($file->get_content_file_handle());
        }

        throw new moodle_exception('File is not stored file.');
    }

    /**
     * create_from_stored_file
     * @param resource|false $filestream
     *
     * @return string
     */
    private function create_from_stored_file($filestream): string {

        if (false === $filestream) {
            throw new moodle_exception('Canno\'t get stored file content.');
        }

        rewind($filestream);

        $hash = hash_init('sha1');
        hash_update_stream($hash, $filestream);
        hash_update($hash, $this->userid);
        hash_update($hash, $this->cmid);

        return hash_final($hash);
    }
}

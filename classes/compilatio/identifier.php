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
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use lib\​filestorage\stored_file;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * identifier class
 */
class identifier {

    public function create($linkarray): string {
        return !empty($linkarray['content'])
            ? $this->create_from_onlinetext($linkarray)
            : $this->create_from_stored_file($linkarray);
    }

    private function create_from_stored_file($linkarray): string {
        /**
         * @var stored_file $file The stored file object retrieved from the link array.
         */
        $file = $linkarray['file'];
        $filestream = $file->get_content_file_handle();
        rewind($filestream);

        $hash = hash_init('sha1');
        hash_update_stream($hash, $filestream);
        hash_update($hash, $linkarray['userid']);
        hash_update($hash, $linkarray['cmid']);

        $identifier = hash_final($hash);
        return $identifier;
    }

    private function create_from_onlinetext($linkarray): string {
        return sha1($linkarray['content'] . $linkarray['userid'] . $linkarray['cmid']);
    }
}
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
 * folder_detections.php - Maps local activity settings to Compilatio folder detection options.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

use stdClass;

/**
 * Builds detection options sent to the Compilatio folder API.
 */
class folder_detections {
    /**
     * Maps Compilatio detection process names to local course module config fields.
     */
    private const CONFIG_FIELDS = [
        'unrecognized_text_language' => 'utlenabled',
        'ai_detection' => 'ai_detectionenabled',
        'rewording' => 'rewordingenabled',
    ];

    /**
     * Builds folder detection options from persisted course module settings.
     *
     * @param stdClass $cmconfig Course module Compilatio configuration.
     * @return array Detection options for api::set_folder().
     */
    public static function from_course_module_config(stdClass $cmconfig): array {
        $detectionsenabled = [];

        foreach (self::CONFIG_FIELDS as $process => $field) {
            if (!property_exists($cmconfig, $field)) {
                continue;
            }

            $detectionsenabled[] = [
                'process' => $process,
                'enabled' => (bool) $cmconfig->{$field},
                'configurable' => true,
            ];
        }

        return $detectionsenabled;
    }
}

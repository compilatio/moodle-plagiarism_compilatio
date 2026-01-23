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
 * managed_bundle.php - Contains methods about managed bundle.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use moodle_database;
use stdClass;

/**
 * Handle managed bundle methods.
 */
class managed_bundle {

    public const DETECTIONSTYPE = [
        "similarity",
        "unrecognized_text_language",
        "ai_detection",
        "rewording",
    ];

    /**
     * @var stdClass $managedbundle User managed bundle.
     */
    public stdClass $managedbundle;

    /**
     * Class constructor
     * 
     * @param stdClass $compilatiouser User from compilatio to retreive managed bundle informations.
     */
    public function __construct($compilatiouser) {

        if (!isset($compilatiouser)) {
            throw new \moodle_exception('No user.');
        }

        $this->managedbundle = $compilatiouser->managed_bundle;
    }

    /**
     * Apply courses module folder detections options.
     * 
     * @param moodle_database $DB Moodle database.
     * @return void
     */
    public function set_all_course_module_to_folder_detections_options(moodle_database $DB): void {
        if (!$this->check_if_detections_configuration_as_been_changed($DB)) return;

        foreach($this->get_bundle_detections() as $detection) {
            if (!in_array($detection->process, self::DETECTIONSTYPE) 
            ) {
                continue; 
            }

            foreach ($DB->get_records('plagiarism_compilatio_cm_cfg') as $configuration) {
                $configuration->{$detection->process . 'enabled'} = 0;
                if ($detection->enabled) $configuration->{$detection->process . 'enabled'} = 1;

                $DB->update_record('plagiarism_compilatio_cm_cfg', $configuration);
            }
        }
    }

    /**
     * Retreive bundle detections.
     * 
     * @return array Return allowed detections for the bundle.
     */
    public function get_bundle_detections(): array {
        return $this->managedbundle->accesses[$this->get_bundle_detections_array_index()]->detections;
    }

    /**
     * Retreive bundle authorized features.
     * 
     * @return array Return authorized features for the bundle.
     */
    public function get_authorized_features(): array {
        return $this->managedbundle->accesses[$this->get_authorized_features_array_index()]->authorized_features;
    }

    /**
     * Check if the current recipe is anasim.
     * 
     * @return bool True if recipe is anasim, false otherwise.
     */
    public function is_anasim_recipe(): bool {
        return get_config('plagiarism_compilatio', 'recipe') === 'anasim';
    }

    /**
     * Check if the bundle has the specified feature.
     * 
     * @param string $feature Name of the feature to check.
     * @return bool Return true if the bundle has this feature, false otherwise.
     */
    public function is_bundle_authorized_to(string $feature): bool {
        return in_array($feature, $this->get_authorized_features());
    }

    /**
     * Return the index of authorized features in access.
     * 
     * @return int Index of authorized features in access.
     */
    private function get_authorized_features_array_index(): int {
        return $this->is_anasim_recipe() ? 5 : 4;
    }

    /**
     * Return the index of bundle detections in access.
     * 
     * @return int Index of bundle detections in access.
     */
    private function get_bundle_detections_array_index(): int {
        return $this->is_anasim_recipe() ? 3 : 2;
    }

    /**
     * Check if course module detections options need to be updated.
     * 
     * @param moodle_database $DB Moodle database.
     * @return bool Return true if courses modules need to be updated, false otherwise.
     */
    private function check_if_detections_configuration_as_been_changed(moodle_database $DB): bool {
        $record = $DB->get_record(
            'plagiarism_compilatio_cm_cfg',
            ['cmid' => 0],
            'similarityenabled,ai_detectionenabled,unrecognized_text_languageenabled,rewordingenabled'
        );
        if (!$record) {
            return false;
        }

        $bundle = [];
        foreach ($this->get_bundle_detections() as $detection) {
            $bundle[$detection->process . 'enabled'] = (int)$detection->enabled;
        }

        foreach ((array)$record as $field => $value) {
            if (array_key_exists($field, $bundle) && (int)$value !== $bundle[$field]) {
                return true;
            }
        }
        return false;
    }
}

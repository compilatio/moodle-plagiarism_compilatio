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
 * university_component.php - Contains methods about university component.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2025 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use moodle_database;

/**
 * University component class.
 */
class university_component {

    /**
     * Course module identifier linked to the component.
     *
     * @var string
     */
    public string $cmid;

    /**
     * Moodle database connection.
     *
     * @var moodle_database
     */
    public moodle_database $moodledb;

    /**
     * Name of the user field storing the university component.
     *
     * @var string|null
     */
    public string|null $universitycomponentfield;

    /**
     * Create the component helper.
     *
     * @param moodle_database $moodledb Moodle DB instance.
     */
    public function __construct(moodle_database $moodledb) {
        $this->moodledb = $moodledb;

        $configfield = get_config('plagiarism_compilatio', 'university_component_type');
        $this->universitycomponentfield = $configfield !== false
            ? $configfield
            : get_string('university_composable_none', 'plagiarism_compilatio');
    }

    /**
     * Retrieve the university component value for a given user.
     *
     * @param string $userid User identifier.
     * @return string|null Component value, null if not set or user field not found.
     */
    public function retreive_university_component_for_user(string $userid) {
        if ($this->universitycomponentfield === get_string('university_composable_none', 'plagiarism_compilatio')) {
            return null;
        }

        $uservalue = $this->moodledb->get_field('user', $this->universitycomponentfield, ['id' => $userid]);

        return $uservalue !== false ? $uservalue : null;
    }

    /**
     * Provide allowed custom user fields for component selection.
     * Commented values are the accepted values as fields.
     *
     * @return array<string> List of user field names not blacklisted.
     */
    public function user_field_provider() {
        $blacklist = [
            'id',
            'auth',
            'confirmed',
            'policyagreed',
            'deleted',
            'suspended',
            'mnethostid',
            'username',
            'password',
            'idnumber',
            'firstname',
            'lastname',
            'email',
            'emailstop',
            'phone1',
            'phone2',
            /* phpcs:ignore */
            // 'institution',
            // 'department',
            'address',
            /* phpcs:ignore */
            // 'city',
            'country',
            'lang',
            'calendartype',
            'theme',
            'timezone',
            'firstaccess',
            'lastaccess',
            'lastlogin',
            'currentlogin',
            'lastip',
            'secret',
            'picture',
            'description',
            'descriptionformat',
            'mailformat',
            'maildigest',
            'maildisplay',
            'autosubscribe',
            'trackforums',
            'timecreated',
            'timemodified',
            'trustbitmask',
            'imagealt',
            'lastnamephonetic',
            'firstnamephonetic',
            'middlename',
            'alternatename',
            'moodlenetprofile',
        ];

        $columns = array_keys($this->moodledb->get_columns('user'));
        array_unshift($columns, get_string('university_composable_none', 'plagiarism_compilatio'));

        $allowedfields = array_diff($columns, $blacklist);

        return array_combine($allowedfields, $allowedfields);
    }
}

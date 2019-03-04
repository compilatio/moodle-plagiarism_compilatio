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
 * observer.php - Contains Plagiarism plugin task manager.
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
 * Manager class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_compilatio_observer {

    /**
     * Upload a forum file
     * @param  \mod_forum\event\assessable_uploaded $event Event
     * @return void
     */
    public static function forum_file_uploaded(
    \mod_forum\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data());
    }

    /**
     * Upload a workshop file
     * @param  \mod_workshop\event\assessable_uploaded $event Event
     * @return void
     */
    public static function workshop_file_uploaded(
    \mod_workshop\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data());
    }

    /**
     * Upload a assign online text
     * @param  \assignsubmission_onlinetext\event\assessable_uploaded $event Event
     * @return void
     */
    public static function assignsubmission_onlinetext_uploaded(
    \assignsubmission_onlinetext\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data(), false, true);
    }

    /**
     * Upload a assign file
     * @param  \assignsubmission_file\event\assessable_uploaded $event Event
     * @return void
     */
    public static function assignsubmission_file_uploaded(
    \assignsubmission_file\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data(), true, false);
    }

    /**
     * Delete a post
     * @param  \mod_forum\event\post_deleted $event Event
     * @return void
     */
    public static function forum_post_deleted(
        \mod_forum\event\post_deleted $event) {
            global $CFG;
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            event_handler($event->get_data(), false, false);
    }

    /**
     * Delete a workshop file and/or content
     * @param  \mod_workshop\event\submission_deleted $event Event
     * @return void
     */
    public static function workshop_submission_deleted(
        \mod_workshop\event\submission_deleted $event) {
            global $CFG;
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            event_handler($event->get_data(), false, false);
    }

}

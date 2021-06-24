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
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data());
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Upload a workshop file
     * @param  \mod_workshop\event\assessable_uploaded $event Event
     * @return void
     */
    public static function workshop_file_uploaded(
    \mod_workshop\event\assessable_uploaded $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data());
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Upload a assign online text
     * @param  \assignsubmission_onlinetext\event\assessable_uploaded $event Event
     * @return void
     */
    public static function assignsubmission_onlinetext_uploaded(
    \assignsubmission_onlinetext\event\assessable_uploaded $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, true);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Upload a assign file
     * @param  \assignsubmission_file\event\assessable_uploaded $event Event
     * @return void
     */
    public static function assignsubmission_file_uploaded(
    \assignsubmission_file\event\assessable_uploaded $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), true, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Upload a file / online text in essay question in quiz
     * @param  \mod_quiz\event\attempt_submitted $event Event
     * @return void
     */
    public static function quiz_submitted(
        \mod_quiz\event\attempt_submitted $event) {
            global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete a attempt in quiz
     * @param  \mod_quiz\event\attempt_deleted $event Event
     * @return void
     */
    public static function quiz_attempt_deleted(
        \mod_quiz\event\attempt_deleted $event) {
            global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete a post
     * @param  \mod_forum\event\post_deleted $event Event
     * @return void
     */
    public static function forum_post_deleted(
        \mod_forum\event\post_deleted $event) {
            global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete a workshop file and/or content
     * @param  \mod_workshop\event\submission_deleted $event Event
     * @return void
     */
    public static function workshop_submission_deleted(
        \mod_workshop\event\submission_deleted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete all the coursemodule files
     * @param  \core\event\course_module_deleted $event Event
     * @return void
     */
    public static function core_course_module_deleted(
        \core\event\course_module_deleted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete all the user files
     * @param  \core\event\user_deleted $event Event
     * @return void
     */
    public static function core_user_deleted(
        \core\event\user_deleted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete a assign file
     * @param  \mod_assign\event\submission_status_updated $event Event
     * @return void
     */
    public static function assign_submission_status_updated(
        \mod_assign\event\submission_status_updated $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course module recycle bin restored
     * @param  \tool_recyclebin\event\course_bin_item_restored $event Event
     * @return void
     */
    public static function recyclebin_course_item_restored(
        \tool_recyclebin\event\course_bin_item_restored $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course module recycle bin deleted
     * @param  \tool_recyclebin\event\course_bin_item_deleted $event Event
     * @return void
     */
    public static function recyclebin_course_item_deleted(
        \tool_recyclebin\event\course_bin_item_deleted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course module recycle bin created
     * @param  \tool_recyclebin\event\course_bin_item_created $event Event
     * @return void
     */
    public static function recyclebin_course_item_created(
        \tool_recyclebin\event\course_bin_item_created $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course recycle bin restored
     * @param  \tool_recyclebin\event\category_bin_item_restored $event Event
     * @return void
     */
    public static function recyclebin_category_item_restored(
        \tool_recyclebin\event\category_bin_item_restored $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course recycle bin deleted
     * @param  \tool_recyclebin\event\category_bin_item_deleted $event Event
     * @return void
     */
    public static function recyclebin_category_item_deleted(
        \tool_recyclebin\event\category_bin_item_deleted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Course recycle bin created
     * @param  \tool_recyclebin\event\category_bin_item_created $event Event
     * @return void
     */
    public static function recyclebin_category_item_created(
        \tool_recyclebin\event\category_bin_item_created $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Student final submit on assign with draft
     * @param  \mod_assign\event\assessable_submitted $event Event
     * @return void
     */
    public static function assign_assessable_submitted(
        \mod_assign\event\assessable_submitted $event) {
        global $CFG;
        try {
            require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
            compilatio_event_handler($event->get_data(), false, false);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

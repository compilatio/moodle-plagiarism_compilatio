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
 * events.php - Contains Plagiarism plugin array who list event catched by the plugin.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$observers = array(
    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::assignsubmission_file_uploaded'
    ),
    array(
        'eventname' => '\mod_workshop\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::workshop_file_uploaded'
    ),
    array(
        'eventname' => '\mod_forum\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::forum_file_uploaded'
    ),
    array(
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::assignsubmission_onlinetext_uploaded'
    ),
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'plagiarism_compilatio_observer::quiz_submitted'
    ),
    array(
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback' => 'plagiarism_compilatio_observer::quiz_attempt_deleted'
    ),
    array(
        'eventname' => '\mod_forum\event\post_deleted',
        'callback' => 'plagiarism_compilatio_observer::forum_post_deleted'
    ),
    array(
        'eventname' => '\mod_workshop\event\submission_deleted',
        'callback' => 'plagiarism_compilatio_observer::workshop_submission_deleted'
    ),
    array(
        'eventname' => '\core\event\course_module_deleted',
        'callback' => 'plagiarism_compilatio_observer::core_course_module_deleted'
    ),
    array(
        'eventname' => '\core\event\user_deleted',
        'callback' => 'plagiarism_compilatio_observer::core_user_deleted'
    ),
    array(
        'eventname' => '\core\event\course_reset_started',
        'callback' => 'plagiarism_compilatio_observer::core_course_reset_started'
    ),
    array(
        'eventname' => '\mod_assign\event\submission_status_updated',
        'callback' => 'plagiarism_compilatio_observer::assign_submission_status_updated'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\course_bin_item_restored',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_restored'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\course_bin_item_deleted',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_deleted'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\course_bin_item_created',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_created'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\category_bin_item_restored',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_restored'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\category_bin_item_deleted',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_deleted'
    ),
    array(
        'eventname' => '\tool_recyclebin\event\category_bin_item_created',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_created'
    ),
    array(
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => 'plagiarism_compilatio_observer::assign_assessable_submitted'
    ),
);

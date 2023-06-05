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
 * events.php - Contains array who list event catched by the plugin.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$observers = [
    [
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::assignsubmission_file_uploaded'
    ],
    [
        'eventname' => '\mod_workshop\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::workshop_file_uploaded'
    ],
    [
        'eventname' => '\mod_forum\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::forum_file_uploaded'
    ],
    [
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => 'plagiarism_compilatio_observer::assignsubmission_onlinetext_uploaded'
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'plagiarism_compilatio_observer::quiz_submitted'
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback' => 'plagiarism_compilatio_observer::quiz_attempt_deleted'
    ],
    [
        'eventname' => '\mod_forum\event\post_deleted',
        'callback' => 'plagiarism_compilatio_observer::forum_post_deleted'
    ],
    [
        'eventname' => '\mod_workshop\event\submission_deleted',
        'callback' => 'plagiarism_compilatio_observer::workshop_submission_deleted'
    ],
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback' => 'plagiarism_compilatio_observer::core_course_module_deleted'
    ],
    [
        'eventname' => '\core\event\user_deleted',
        'callback' => 'plagiarism_compilatio_observer::core_user_deleted'
    ],
    [
        'eventname' => '\core\event\course_reset_started',
        'callback' => 'plagiarism_compilatio_observer::core_course_reset_started'
    ],
    [
        'eventname' => '\mod_assign\event\submission_status_updated',
        'callback' => 'plagiarism_compilatio_observer::assign_submission_status_updated'
    ],
    [
        'eventname' => '\tool_recyclebin\event\course_bin_item_restored',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_restored'
    ],
    [
        'eventname' => '\tool_recyclebin\event\course_bin_item_deleted',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_deleted'
    ],
    [
        'eventname' => '\tool_recyclebin\event\course_bin_item_created',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_course_item_created'
    ],
    [
        'eventname' => '\tool_recyclebin\event\category_bin_item_restored',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_restored'
    ],
    [
        'eventname' => '\tool_recyclebin\event\category_bin_item_deleted',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_deleted'
    ],
    [
        'eventname' => '\tool_recyclebin\event\category_bin_item_created',
        'callback' => 'plagiarism_compilatio_observer::recyclebin_category_item_created'
    ],
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => 'plagiarism_compilatio_observer::assign_assessable_submitted'
    ]
];

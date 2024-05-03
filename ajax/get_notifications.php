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
 * Get Compilatio marketing notifications
 *
 * @package   plagiarism_compilatio
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\output\icons;

require_login();

$userid = required_param('userid', PARAM_TEXT);
$read = optional_param_array('read', [], PARAM_TEXT);
$ignored = optional_param_array('ignored', [], PARAM_TEXT);

$compilatio = new api($userid);
$language = substr(current_language(), 0, 2);

$notifications = $compilatio->get_marketing_notifications($language);

$titles = $contents = $floatingnotification = '';
$countbadge = 0;
$notificationsids = [];

foreach ($notifications as $index => $notification) {
    $date = new DateTime($notification->activation_period->start);

    foreach ($notification->content_by_language as $content) {
        if ($content->language == $language) {
            $notificationsids[] = $notification->id;

            $body = str_replace('<a', '<a target="_blank" rel="noopener noreferrer"', $content->body);
            $body = str_replace('button', 'btn btn-primary', $body);

            $status = in_array($notification->id, $ignored) ? 'ignored' : 'unread';
            in_array($notification->id, $read) ? $status = 'read' : null;

            $status !== 'read' ? $countbadge++ : null;

            $titles .= "<div id='cmp-notifications-" . $notification->id . "' class='cmp-notifications-title cmp-cursor-pointer'>
                    <div class='text-bold " . ($status !== 'read' ? 'text-primary' : '') . "'>"
                    . $content->title .
                    "</div>"
                    . userdate($date->getTimestamp(), get_string('strftimedatetime', 'core_langconfig')) .
                "</div>";

            $titles .= $index !== (count($notifications) - 1) ? "<hr class='my-2'>" : '';

            $contents .= "
                <div id='cmp-notifications-content-" . $notification->id . "' class='cmp-notifications-content' style='display: none;'>
                    <div class='cmp-show-notifications mb-2 cmp-cursor-pointer'>"
                        . icons::arrow_left() . get_string('see_all_notifications', 'plagiarism_compilatio') . "
                    </div>
                    <div class='d-flex flex-column'>" . $body . "</div>
                </div>";

            if ($floatingnotification == '' && $status == 'unread') {
                $floatingnotification = "
                    <div class='d-flex cmp-alert cmp-alert-notifications'>
                        <i class='cmp-alert-icon text-primary fa-lg fa fa-bell'></i>
                        <span class='mr-2'>" . $content->title . "</span>
                        <span
                            id='cmp-notifications-" . $notification->id . "'
                            class='cmp-notifications-title ml-auto text-primary cmp-cursor-pointer'
                        >"
                            . get_string('open', 'plagiarism_compilatio') .
                        "</span>
                        <i id='cmp-ignore-notifications' class='my-auto ml-3 fa fa-times cmp-cursor-pointer'></i>
                    </div>";
            }
        }
    }
}

$titles = empty($titles) ? '<span>' . get_string('no_notification', 'plagiarism_compilatio') . '</span>' : $titles;

$result = [
    'floating' => $floatingnotification,
    'content' => "<div id='cmp-notifications-titles'><h4>" . get_string("notifications", "plagiarism_compilatio") . "</h4>"
                . $titles .
            "</div>" . $contents,
    'count' => $countbadge,
    'ids' => $notificationsids,
];

echo json_encode($result);

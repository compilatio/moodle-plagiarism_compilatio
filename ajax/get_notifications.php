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
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/api.php');

require_login();

$userid = required_param('userid', PARAM_TEXT);
$read = required_param('read', PARAM_RAW);
$ignored = required_param('ignored', PARAM_RAW);

error_log(var_export(json_decode(json_encode($read)),true));

$compilatio = new CompilatioAPI($userid);
$language = substr(current_language(), 0, 2);
$notifications = '';
$popup = null;

$n = $compilatio->get_marketing_notifications($language);

foreach ($n as $notif) {
    $date = new DateTime($notif->activation_period->start);

    foreach ($notif->content_by_language as $content) {
        if ($content->language == $language) {
            $jsp = str_replace('<a', '<a target="_blank" rel="noopener noreferrer"', $content->body);
            $jsp = str_replace('button', 'btn btn-primary', $jsp);

            /*$status = in_array($notif->id, $read) 
                ? 'read'
                : (in_array($notif->id, $ignored) ? 'ignored' : 'unread');*/
            $status = 'unread';

            


            $titles = $contents = $encoreuntruc = '';
            
            $titles .= "<div id='cmp-notif-" . $notif->id . "' class='cmp-notif-title'>
                    <div class='text-bold " . $status !== 'read' ? 'text-primary' : '' . "'>"
                    . $content->title .
                    "</div>"
                    . userdate($date->getTimestamp(), get_string('strftimedatetime', 'core_langconfig')) .
                "</div>";
            $titles .= $index !== (count($n) - 1) ? "<hr class='my-2'>" : '';
    
            $contents .= "<div id='cmp-notif-content-" . $notif->id . "' class='cmp-notif-content' style='display: none;'>
                    <div class='cmp-show-notifs mb-2'><i class='fa-solid fa-arrow-left mr-2'></i>Voir toutes les notifications</div><div class='d-flex flex-column'>" . $jsp . '</div>' .
                "</div>";
    
    
            if ($popup == null && $status == 'unread') {
                $popup = "<div class='d-flex cmp-alert cmp-alert-info'>
                        <i class='cmp-alert-icon fa-lg fa-regular fa-bell'></i>
                        <span class='text-secondary'>" . $content->title . "</span>
                        <span id='cmp-notif-" . $notif->id . "' class='cmp-notif-title ml-auto text-primary'>Ouvrir</span>
                        <i class='my-auto ml-3 fa fa-times'></i>
                    </div>";
            }
        }
    }
}

$notifications .= "<div id='cmp-notif-titles'><h4>" . get_string("notifications", "plagiarism_compilatio") . "</h4>"
        . $titles .
    "</div>";

$notifications .= $contents;

$result = [
    'popup' => $popup,
    'notifs' => $notifications,
    'count' => count($n)
];

echo json_encode($result);

/*
// Notification icon.
        if ($notificationscount !== 0) {
            $output .= "<span>
                <i
                    id='cmp-show-notifications'
                    title='" . get_string("display_notifications", "plagiarism_compilatio") . "'
                    class='cmp-icon fa fa-bell'
                >
                </i>
                <span id='cmp-count-alerts' class='badge badge-pill badge-danger'>" . $notificationscount . "</span>
            </span>";
        }
*/

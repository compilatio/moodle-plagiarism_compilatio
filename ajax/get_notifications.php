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
 * @copyright 2026 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use plagiarism_compilatio\compilatio\marketing_notification;

require_login();
if (isguestuser()) {
    redirect(new moodle_url('/'));
    die();
}

$userid = required_param('userid', PARAM_TEXT);
$read = optional_param_array('read', [], PARAM_TEXT);
$ignored = optional_param_array('ignored', [], PARAM_TEXT);

$compilatiomarketingnotification = new marketing_notification(substr(current_language(), 0, 2), $userid);
$notifications = $compilatiomarketingnotification->get();
$titles = $contents = $floatingnotification = '';
$countbadge = 0;
$notificationsids = [];

foreach ($notifications as $index => $notification) {
    $currentlanguagenotification = $compilatiomarketingnotification
        ->get_notification_current_language($notification->content_by_language);

    if ( $currentlanguagenotification === null) {
        continue;
    }

    $notificationsids[] = $notification->id;

    $body = $compilatiomarketingnotification->format_notification_body( $currentlanguagenotification->body);

    $status = in_array($notification->id, $ignored) ? 'ignored' : 'unread';
    in_array($notification->id, $read) ? $status = 'read' : null;

    $status !== 'read' ? $countbadge++ : null;

    $titles .= $compilatiomarketingnotification->get_notification_title_body(
        $notification->id,
        $status,
         $currentlanguagenotification->title,
        new DateTime($notification->activation_period->start),
        $index === (count($notifications) - 1)
    );

    $contents .= $compilatiomarketingnotification->get_notification_content_body($notification->id, $body);

    if ($floatingnotification == '' && $status == 'unread') {
        $floatingnotification = $compilatiomarketingnotification
            ->get_notification_floatingnotification_body($notification->id,  $currentlanguagenotification->title);
    }
}

$titles = empty($titles) ? '<span>' . get_string('no_notification', 'plagiarism_compilatio') . '</span>' : $titles;

echo json_encode(
    $compilatiomarketingnotification
        ->get_result(
            $floatingnotification,
            $titles,
            $contents,
            $countbadge,
            $notificationsids
        )
    );

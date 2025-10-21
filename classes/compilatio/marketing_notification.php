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
 * notification.php - Contains methods about Marketing Notifications.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plagiarism_compilatio\compilatio;

use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\output\icons;
use DateTime;

/**
 * Marketing notification handler class.
 * 
 * Handles the retrieval, formatting, and rendering of Compilatio marketing notifications
 * with support for multi-language content, image optimization, and responsive design.
 */
class marketing_notification {
    /** @var string The current language code (2 characters) */
    private string $language;

    /** @var api The Compilatio API instance */
    private $compilatioapi;

    /**
     * Constructor for marketing notification handler.
     *
     * @param string $language The language code (2 characters, e.g., 'en', 'fr')
     * @param string $userid The user ID for API authentication
     */
    function __construct(string $language, string $userid)
    {
        $this->language = $language;
        $this->compilatioapi = new api($userid);
    }

    /**
     * Get all marketing notifications.
     *
     * @return array Array of marketing notifications
     */
    public function get() {
        return $this->compilatioapi->get_marketing_notifications($this->language);
    }

    /**
     * Format notification body: add target to links, style buttons, limit image size.
     * 
     * This method performs several transformations:
     * - Adds target="_blank" and rel="noopener noreferrer" to all links for security
     * - Converts button elements to Bootstrap styled buttons (btn btn-primary)
     * - Adds responsive sizing and centering to images (max-width: 100%, max-height: 200px)
     * - Preserves existing image styling while ensuring responsiveness
     *
     * @param string $body The raw HTML content of the notification
     * @return string The formatted HTML content ready for display
     */
    public function format_notification_body(string $body): string {
        $body = str_replace('<a', '<a target="_blank" rel="noopener noreferrer"', $body);
        $body = str_replace('button', 'btn btn-primary', $body);

        $body = preg_replace_callback(
            '/<img([^>]*?)>/i',
            function($matches) {
                $imgattributes = $matches[1];

                $hasstyle = stripos($imgattributes, 'style=') !== false;
                $haswidth = stripos($imgattributes, 'width=') !== false;
                $hasheight = stripos($imgattributes, 'height=') !== false;

                if (!$hasstyle && !$haswidth && !$hasheight) {
                    $imgattributes .= ' style="max-width: 100%; max-height: 200px; height: auto; display: block; margin: 0 auto;"';
                } else if ($hasstyle && stripos($imgattributes, 'max-width') === false) {
                    $imgattributes = preg_replace('/style="([^"]*)"/', 'style="$1 max-width: 100%; max-height: 200px; display: block; margin: 0 auto;"', $imgattributes);
                }

                return '<img' . $imgattributes . '>';
            },
            $body
        );

        return $body;
    }

    /**
     * Get notification content for the current language.
     *
     * @param array $translated_notifications Array of translated notification objects
     * @return object|null The notification content for current language or null if not found
     */
    public function get_notification_current_language(array $translated_notifications): ?object {
        return array_values(array_filter($translated_notifications, fn($n) => $n->language === $this->language))[0] ?? null;
    }

    /**
     * Generate HTML for notification content body with scroll and navigation.
     *
     * @param string $notification_id The unique identifier of the notification
     * @param string $body The formatted HTML content of the notification
     * @return string The complete HTML structure for notification content
     */
    public function get_notification_content_body(string $notification_id, string $body): string {
        return "
        <div id='cmp-notifications-content-" . $notification_id
            . "' class='cmp-notifications-content' style='display: none; max-height: 70vh; overflow-y: auto;'>
            <div class='cmp-show-notifications mb-2 cmp-cursor-pointer'>"
                . icons::arrow_left() . \get_string('see_all_notifications', 'plagiarism_compilatio') . "
            </div>
            <div class='d-flex flex-column'>" . $body . "</div>
        </div>";
    }

    /**
     * Generate HTML for notification title with status styling and optional separator.
     *
     * @param string $notification_id The unique identifier of the notification
     * @param string $status The notification status ('read', 'unread', 'ignored')
     * @param string $title The notification title text
     * @param DateTime $date The activation date of the notification
     * @return string The complete HTML structure for notification title with optional HR separator
     */
    public function get_notification_title_body(string $notification_id, string $status, string $title, DateTime $date, bool $is_last=false) {
        $html = "<div id='cmp-notifications-" . $notification_id . "' 
                    class='cmp-notifications-title cmp-cursor-pointer p-2 mb-1 cmp-notification-hover rounded' 
                    style='max-height: 120px; overflow-y: auto; transition: all 0.3s ease; border-color: #dee2e6;'
                    onmouseover='this.style.backgroundColor=\"#f8f9fa\"; this.style.borderColor=\"#007bff\"; this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0,123,255,0.15)\";'
                    onmouseout='this.style.backgroundColor=\"\"; this.style.borderColor=\"#dee2e6\"; this.style.transform=\"\"; this.style.boxShadow=\"\";'>
            <div class='fw-bold " . ($status !== 'read' ? 'text-primary' : '') . "'>"
            . $title .
            "</div>"
            . "<small class='text-muted'>" . \userdate($date->getTimestamp(), \get_string('strftimedatetime', 'core_langconfig')) . "</small>" .
        "</div>";

        $html .= !$is_last ? "<hr>" : "";
        
        return $html;
    }

    /**
     * Generate HTML for floating notification alert.
     *
     * @param string $notification_id The unique identifier of the notification
     * @param string $title The notification title text
     * @return string The complete HTML structure for floating notification with bell icon and actions
     */
    public function get_notification_floatingnotification_body(string $notification_id, string $title): string {
        return "
            <div class='d-flex cmp-alert cmp-alert-notifications'>
                <i class='cmp-alert-icon text-primary fa-lg fa fa-bell'></i>
                <span class='mr-2'>" . $title . "</span>
                <span
                    id='cmp-notifications-" . $notification_id . "'
                    class='cmp-notifications-title ml-auto text-primary cmp-cursor-pointer'
                    style='transition: all 0.3s ease;'
                    onmouseover='this.style.backgroundColor=\"#007bff\"; this.style.color=\"white\"; this.style.padding=\"4px 8px\"; this.style.borderRadius=\"4px\";'
                    onmouseout='this.style.backgroundColor=\"\"; this.style.color=\"#007bff\"; this.style.padding=\"\"; this.style.borderRadius=\"\";'
                >"
                    . \get_string('open', 'plagiarism_compilatio') .
                "</span>
                <i id='cmp-ignore-notifications' 
                   class='my-auto ml-3 fa fa-times cmp-cursor-pointer'
                   style='transition: all 0.3s ease;'
                   onmouseover='this.style.color=\"#dc3545\"; this.style.transform=\"scale(1.1)\";'
                   onmouseout='this.style.color=\"\"; this.style.transform=\"\";'></i>
            </div>";
    }

    /**
     * Build the final result array for JSON response.
     *
     * @param string $floatingnotification HTML for floating notification or empty string
     * @param string $titles HTML for all notification titles
     * @param string $contents HTML for all notification contents
     * @param int $countbadge Number of unread notifications for badge display
     * @param array $notificationsids Array of all notification IDs
     * @return array Complete result array ready for JSON encoding with floating, content, count and ids keys
     */
    public function get_result(string $floatingnotification, string $titles, string $contents, int $countbadge, array $notificationsids): array {
        return [
            'floating' => $floatingnotification,
            'content' => "<div id='cmp-notifications-titles' style='max-height: 60vh; overflow-y: auto;'><h4>" . \get_string("notifications", "plagiarism_compilatio") . "</h4>"
                        . $titles .
                    "</div>" . $contents,
            'count' => $countbadge,
            'ids' => $notificationsids,
        ];
    }
}
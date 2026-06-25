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
 * alerts.php - Contains methods about Compilatio alerts.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plagiarism_compilatio\compilatio;

use plagiarism_compilatio\compilatio\api;

/**
 * Alerts handler class.
 *
 * Handles the retrieval, formatting, and rendering of Compilatio alerts
 */
class alerts {
    /** @var string The current language code (2 characters) */
    private string $language;

    /** @var api The Compilatio API instance */
    private api $compilatioapi;

    /** @var string The module name (e.g., 'assign', 'quiz') */
    private string $module;

    /**
     * Constructor for alerts handler.
     *
     * @param string $language The language code (2 characters, e.g., 'en', 'fr')
     * @param string $userid The user ID for the@ Compilatio API instance
     * @param string $module The module name (e.g., 'assign', 'quiz')
     */
    public function __construct(string $language, string $userid, string $module) {
        $this->language = $language;
        $this->compilatioapi = new api($userid);
        $this->module = $module;
    }

    /**
     * Get all alerts.
     *
     * @param object $SESSION The global session object to retrieve and manage alerts
     * @param int $cmid The course module ID to check for specific alerts (e.g., 'assign', 'quiz')
     * @return array Array of alerts
     */
    public function get($SESSION, $cmid) {
        $compilatioalerts = $this->compilatioapi->get_alerts($this->language);

        $alerts = [];

        foreach ($compilatioalerts as $alert) {
            if (
                $alert->text === 'DONT_DISPLAY' ||
                time() <= strtotime($alert->activation_period->start) ||
                time() >= strtotime($alert->activation_period->end)
            ) {
                continue;
            }

            $alerts[] = [
                'class' => 'info',
                'content' => "<span class='cmp-md'>" . $alert->text . '</span>',
            ];
        }

        $alerts = array_merge($alerts, $this->get_internal_moodle_alerts($SESSION, $cmid));
        return $alerts;
    }

    /**
     * Get HTML body for an alert.
     *
     * @param array $alert The alert data containing 'class' and 'content'
     * @param int $index The index of the alert (used for unique IDs)
     * @return string The HTML string for the alert
     */
    public function get_alert_body($alert, $index) {
         return "<div class='cmp-alert cmp-alert-" . $alert['class'] . "'>
                <span class='mr-1 d-flex'>
                    <i class='cmp-alert-icon fa-lg fa " . $this->get_icon($alert['class']) . "'></i>" . $alert['content'] .
                "</span>
                <i id='cmp-alert-" . $index . "'
                    class='cmp-close cmp-cursor-pointer ml-auto my-auto fa fa-times'
                    style='transition: all 0.3s ease;'
                    onmouseover='this.style.color=\"#dc3545\"; this.style.transform=\"scale(1.1)\";'
                    onmouseout='this.style.color=\"\"; this.style.transform=\"\";'
                />
            </div>";
    }

    /**
     * Get icon for alert.
     *
     * @param string $typealert The type of alert (e.g., 'info', 'warning', 'danger', 'maintenance', 'success')
     * @return string The icon class for the alert
     */
    private function get_icon($typealert) {
        switch ($typealert) {
            case 'info':
                $icon = 'fa-bell';
                break;
            case 'warning':
                $icon = 'fa-exclamation-circle';
                break;
            case 'danger':
            case 'maintenance':
                $icon = 'fa-exclamation-triangle text-danger';
                break;
            case 'success':
                $icon = 'fa-check-circle';
                break;
        }
        return $icon;
    }

    /**
     * Get all internal Moodle alerts.
     *
     * @param object $SESSION The global session object to retrieve and manage alerts
     * @param int $cmid The course module ID to check for specific alerts (e.g., 'assign', 'quiz')
     * @return array Array of alerts
     */
    private function get_internal_moodle_alerts($SESSION, $cmid) {
        $alerts = [];

        if (isset($SESSION->compilatio_alerts)) {
            $alerts = $SESSION->compilatio_alerts;
            unset($SESSION->compilatio_alerts);
        }

        // Check if compilatio is under maintenance.
        if ($this->compilatioapi->is_in_maintenance()) {
            $alerts[] = [
                'class'   => 'maintenance',
                'content' => '
                    <div id="maintenance-modal" class="mt-3">
                        ' . get_string('compilatio_maintenance_title', 'plagiarism_compilatio') . '
                        <p class="cmp-alert-description">
                        ' . get_string('compilatio_maintenance_content', 'plagiarism_compilatio', current_language()) . '
                        </p>
                    </div>',
            ];
        }

        $webservicestatus = get_config('plagiarism_compilatio', 'connection_webservice');

        if ($webservicestatus != null && $webservicestatus === '0' && !$this->compilatioapi->is_in_maintenance()) {
            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('webservice_unreachable', 'plagiarism_compilatio'),
            ];
        }

        if (get_config('plagiarism_compilatio', 'read_only_apikey') === '1') {
            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('read_only_apikey', 'plagiarism_compilatio'),
            ];
        }

        // Check for unsend documents.
        if ($this->module === 'assign') {
            if (count(compilatio_get_unsent_documents($cmid)) === 0) {
                return $alerts;
            }

            $alerts[] = [
                'class' => 'danger',
                'content' => get_string('unsent_docs', 'plagiarism_compilatio'),
            ];
        }

        return $alerts;
    }
}

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
 * hook_callbacks.php - Contains hook callbacks.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2024 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio;

use core\hook\output\before_standard_top_of_body_html_generation;

class hook_callbacks {

    /**
     * Hook callback to insert a chunk of html at the start of the html document.
     * This allow us to display the Compilatio frame with statistics, alerts,
     * author search tool and buttons to launch all analyses and update submitted files status.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {
        global $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/frame.php');

        $output = \CompilatioFrame::get_frame();

        $hook->add_html($output);
    }
}

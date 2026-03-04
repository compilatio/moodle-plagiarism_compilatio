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
 * compilatio_defaults_form.php - Contains the default settings form for Compilatio plagiarism plugin.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio\form;
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.

use plagiarism_compilatio\compilatio\course_module_settings;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Class
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatio_defaults_form extends \moodleform {

    /**
     * Define the form
     * @return void
     */
    protected function definition() {
        $mform = & $this->_form;
        course_module_settings::get_form_elements($mform, true);
        $this->add_action_buttons(true);
    }
}

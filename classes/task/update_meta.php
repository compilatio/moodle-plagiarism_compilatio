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

namespace plagiarism_compilatio\task;

defined('MOODLE_INTERNAL') || die();

class update_meta extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('update_meta', 'plagiarism_compilatio');
    }

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        compilatio_update_meta();
    }

}

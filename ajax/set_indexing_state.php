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
 * Set document indexing state via Compilatio SOAP API
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param   string $_POST['idDoc']
 * @param   string $_POST['indexingState']
 * @return  boolean
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/api.class.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

// Get helper class.
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/output_helper.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');

// Get constants.
require_once($CFG->dirroot . '/plagiarism/compilatio/constants.php');

require_login();

// Get global Compilatio settings.
$plagiarismsettings = (array) get_config('plagiarism');

if (isset($_POST['idDoc']) && compilatio_valid_md5($_POST['idDoc']) && isset($_POST['indexingState'])) {

    $indexingstate = (int) ((boolean) $_POST['indexingState']);
    $compilatio = new compilatioservice($plagiarismsettings['compilatio_password'], $plagiarismsettings['compilatio_api']);

    $call = $compilatio->set_indexing_state($_POST['idDoc'], $indexingstate);
    if ($call === true) {
        echo ('true');
    } else {
        echo ('false');
    }
}

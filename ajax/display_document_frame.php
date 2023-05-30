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
 * Get Compilatio document frame
 *
 * This script is called by amd/build/ajax_api.js
 *
 * @copyright  2022 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/classes/compilatio/documentFrame.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

require_login();

$cantriggeranalysis = required_param('cantriggeranalysis', PARAM_BOOL);
$isstudentanalyse = required_param('isstudentanalyse', PARAM_BOOL);
$cmpfileid = required_param('cmpfileid', PARAM_RAW);
$canviewreport = required_param('canviewreport', PARAM_BOOL);
$isteacher = required_param('isteacher', PARAM_BOOL);
$url = required_param('url', PARAM_RAW);
$filename = required_param('filename', PARAM_RAW);

echo CompilatioDocumentFrame::display_document_frame(
    $cantriggeranalysis,
    $isstudentanalyse,
    $cmpfileid,
    $canviewreport,
    $isteacher,
    $url,
    $filename
);

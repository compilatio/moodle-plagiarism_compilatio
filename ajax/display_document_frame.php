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
 * @package   plagiarism_compilatio
 * @copyright 2025 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../../config.php');

use plagiarism_compilatio\output\document_frame;

require_login();
if (isguestuser()) {
    redirect(new moodle_url('/'));
    die();
}

$cmpfileid = required_param('cmpfileid', PARAM_RAW);
$cantriggeranalysis = required_param('cantriggeranalysis', PARAM_BOOL);
$isstudentanalyse = required_param('isstudentanalyse', PARAM_BOOL);
$canviewreport = required_param('canviewreport', PARAM_BOOL);
$isteacher = required_param('isteacher', PARAM_BOOL);
$url = required_param('url', PARAM_RAW);

echo document_frame::display_document_frame(
    $cantriggeranalysis,
    $isstudentanalyse,
    $cmpfileid,
    $canviewreport,
    $isteacher,
    $url
);

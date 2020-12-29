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
 * reset.php - resets an Compilatio submission
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2011 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');

$cmid = required_param('cmid', PARAM_INT);  // Course Module ID.
$pf  = required_param('pf', PARAM_INT);   // Plagiarism file id.
require_sesskey();
$url = new moodle_url('/plagiarism/compilatio/reset.php');
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);

$PAGE->set_url($url);
require_login($cm->course, true, $cm);

$modulecontext = context_module::instance($cmid);
require_capability('plagiarism/compilatio:resetfile', $modulecontext);

$plagiarismfile = $DB->get_record('plagiarism_compilatio_files', array('id' => $pf), '*', MUST_EXIST);

// Reset db entry.
$plagiarismfile->statuscode = 'pending';
$plagiarismfile->attempt = 0;
$plagiarismfile->timesubmitted = time();
$DB->update_record('plagiarism_compilatio_files', $plagiarismfile);

// Now trigger event to process the file.

$urlparams = array('id' => $cmid, 'action' => "grading", 'page' => optional_param('page', null, PARAM_INT));
$redirect = new moodle_url('/mod/assign/view.php', $urlparams);
redirect($redirect, get_string('filereset', 'plagiarism_compilatio'));

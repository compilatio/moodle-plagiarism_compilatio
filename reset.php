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
$pf  = required_param('pf', PARAM_INT);   // plagiarism file id.
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

// This is hardcoded to assignment mod.
if ($cm->modname == 'assignment') {
    $submission = $DB->get_record('assignment_submissions',
                                  array('assignment' => $cm->instance, 'userid' => $plagiarismfile->userid));
    $fs = get_file_storage();
    $files = $fs->get_area_files($modulecontext->id, 'mod_assignment', 'submission', $submission->id);
    if (!empty($files)) {
        $eventdata = new stdClass();
        $eventdata->modulename   = $cm->modname;
        $eventdata->cmid         = $cm->id;
        $eventdata->courseid     = $cm->course;
        $eventdata->userid       = $plagiarismfile->userid;
        $eventdata->files        = $files;

        events_trigger('assessable_file_uploaded', $eventdata);
    } else if (!empty($submission->data1)) {
        $eventdata = new stdClass();
        $eventdata->modulename   = $cm->modname;
        $eventdata->cmid         = $cm->id;
        $eventdata->courseid     = $cm->course;
        $eventdata->userid       = $plagiarismfile->userid;
        $eventdata->content      = trim(strip_tags(format_text($submission->data1, $submission->data2)));
        events_trigger('assessable_content_uploaded', $eventdata);
    }
}

$redirect = new moodle_url('/mod/assign/view.php', array('id' => $cmid, 'action' => "grading"));
redirect($redirect, get_string('filereset', 'plagiarism_compilatio'));
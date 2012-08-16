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
 *
 * @package   plagiarism_compilatio
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Compilatio plagiarism plugin';
$string['studentdisclosuredefault']  ='All files uploaded here will be submitted to the plagiarism detection service Compilatio';
$string['studentdisclosure'] = 'Student Disclosure';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['compilatioexplain'] = 'For more information on this plugin see: <a href="http://compilatio.net" target="_blank">http://compilatio.net</a>';
$string['compilatio'] = 'Compilatio plagiarism plugin';
$string['compilatio_api'] = 'Compilatio Integration Address';
$string['compilatio_api_help'] = 'This is the address of the Compilatio API';
$string['compilatio_password'] = 'Institute code';
$string['compilatio_password_help'] = 'Institute code provided by Compilatio to access the API';
$string['usecompilatio'] ='Enable Compilatio';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['savedconfigfailed'] = 'An incorrect integration address/institution code has been entered, Compilatio is disabled, please try again.';
$string['compilatio_show_student_score'] = 'Show similarity score to student';
$string['compilatio_show_student_score_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['compilatio_show_student_report'] = 'Show similarity report to student';
$string['compilatio_show_student_report_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location that Compilatio first saw this content';
$string['compilatio_draft_submit'] = 'When should the file be submitted to Compilatio';
$string['showwhenclosed'] = 'When Activity closed';
$string['submitondraft'] = 'Submit file when first uploaded';
$string['submitonfinal'] = 'Submit file when student sends for marking';
$string['defaultupdated'] = 'Default values updated';
$string['defaultsdesc'] = 'The following settings are the defaults set when enabling Compilatio within an Activity Module';
$string['compilatiodefaults'] = 'Compilatio defaults';
$string['similarity'] = 'Compilatio';
$string['processing'] = 'This file has been submitted to Compilatio, now waiting for the analysis to be available';
$string['pending'] = 'This file is pending submission to Compilatio';
$string['previouslysubmitted'] = 'Previously submitted as';
$string['report'] = 'report';
$string['unknownwarning'] = 'An error occurred trying to send this file to Compilatio';
$string['unsupportedfiletype'] = 'This filetype is not supported by Compilatio';
$string['toolarge'] = 'This file is too large for Compilatio to process';
$string['optout'] = 'Opt-out';
$string['compilatio_studentemail'] = 'Send Student email';
$string['compilatio_studentemail_help'] = 'This will send an e-mail to the student when a file has been processed to let them know that a report is available.';
$string['studentemailsubject'] = 'File processed by Compilatio';
$string['studentemailcontent'] = 'The file you submitted to {$a->modulename} in {$a->coursename} has now been processed by the Plagiarism tool Compilatio.
{$a->modulelink}';

$string['filereset'] = 'A file has been reset for re-submission to Compilatio';
$string['analysistype'] = 'Analysis Type';
$string['analysistype_help'] = '<p>There are 3 possible options:</p>
<ul>
	<li><strong>Instant: </strong>The document is sent to Compilatio and analyzed straight away.</li>
	<li><strong>Manual:</strong> The document is sent to Compilatio but the teacher must manually trigger the anaylyis of documents.</li>
	<li><strong>Timed: </strong>The document is sent to Compilatio and analysed at the time/date set.</li>
</ul>
<p>To enable all documents to be compared with each other choose the manual mode and wait until all work is submitted by students then trigger the analyis.</p>
';
$string['analysistypeauto'] = 'Instant';
$string['analysistypemanual'] = 'Manual';
$string['analysistypeprog'] = 'Timed';
$string['analysisdate'] = 'Analysis Date';
$string['enabledandworking'] = 'The Compilatio plugin is enabled and working.';
$string['usedcredits'] = '<strong>You have used {$a->used} credit(s) of {$a->credits} and have {$a->remaining} credit(s) remaining</strong>';
$string['startanalysis'] = 'Start analysis';
$string['failedanalysis'] = 'Compilatio failed to analyse your document: ';
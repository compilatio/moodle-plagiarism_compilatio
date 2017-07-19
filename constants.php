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
 * constants.php - Contains Plagiarism plugin constants.
 *
 * @since 2.4
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Balleydier Loïc <loic@compilatio.net>
 * @copyright  2016 Balleydier Loïc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

define('COMPILATIO_MAX_SUBMISSION_ATTEMPTS', 6); // Max num to try and send a submission to Compilatio.
define('COMPILATIO_MAX_SUBMISSION_DELAY', 60);   // Max time to wait between submissions (defined in minutes).
define('COMPILATIO_SUBMISSION_DELAY', 15);       // Initial wait time, doubled each time until max_submission_delay is met.
define('COMPILATIO_MAX_STATUS_ATTEMPTS', 10);    // Maximum number of times to try and obtain the status of a submission.
define('COMPILATIO_MAX_STATUS_DELAY', 1440);     // Maximum time to wait between checks (defined in minutes).
define('COMPILATIO_STATUS_DELAY', 10);           // Initial wait time, doubled each time a until the max_status_delay is met.

define('COMPILATIO_STATUSCODE_ACCEPTED', '202');
define('COMPILATIO_STATUSCODE_ANALYSING', '203');
define('COMPILATIO_STATUSCODE_BAD_REQUEST', '400');
define('COMPILATIO_STATUSCODE_NOT_FOUND', '404');
define('COMPILATIO_STATUSCODE_UNSUPPORTED', '415');
define('COMPILATIO_STATUSCODE_UNEXTRACTABLE', '416');
define('COMPILATIO_STATUSCODE_TOO_LARGE', '413');
define('COMPILATIO_STATUSCODE_COMPLETE', 'Analyzed');
define('COMPILATIO_STATUSCODE_IN_QUEUE', 'In queue');

define('COMPILATIO_ANALYSISTYPE_AUTO', 0);   // File shoud be processed as soon as the file is sent.
define('COMPILATIO_ANALYSISTYPE_MANUAL', 1); // File processed when teacher manually decides to.
define('COMPILATIO_ANALYSISTYPE_PROG', 2);   // File processed on set time/date.

define('PLAGIARISM_COMPILATIO_SHOW_NEVER', 0);
define('PLAGIARISM_COMPILATIO_SHOW_ALWAYS', 1);
define('PLAGIARISM_COMPILATIO_SHOW_CLOSED', 2);

define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_IMMEDIATE', 0);
define('PLAGIARISM_COMPILATIO_DRAFTSUBMIT_FINAL', 1);

define('PLAGIARISM_COMPILATIO_NEWS_UPDATE', 1);
define('PLAGIARISM_COMPILATIO_NEWS_INCIDENT', 2);
define('PLAGIARISM_COMPILATIO_NEWS_MAINTENANCE', 3);
define('PLAGIARISM_COMPILATIO_NEWS_ANALYSIS_PERTURBATED', 4);
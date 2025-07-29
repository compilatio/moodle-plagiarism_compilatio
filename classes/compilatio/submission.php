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
 * submission.php - Contains methods about submission.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

use stored_file;
use moodle_database;
use moodle_exception;

/**
 * Submission class.
 */
class submission {

    /**
     * @var moodle_database $moodledatabase represente Moodle global $DB
     */
    public moodle_database $moodledatabase;

    /**
     * Class constructor
     * @param  moodle_database $moodledatabase represente Moodle global $DB.
     */
    public function __construct($moodledatabase) {
        $this->moodledatabase = $moodledatabase;
    }

    /**
     * Get submission records
     *
     * @param int $cmid Course module ID
     * @param object $content Storedfile or onlinetext object
     * @param string $userid Userid of the author of the document
     * @param string $filename Filename of the document
     *
     * @return object Submission record or throw exception
     */
    public function get($cmid, $content, $userid, $filename) {

        $cm = get_coursemodule_from_id(null, $cmid);
        if (!$cm) {
            throw new moodle_exception("Course module not found");
        }

        $moduleinstance = $this->moodledatabase->get_record($cm->modname, ['id' => $cm->instance]);
        if (!$moduleinstance) {
            throw new moodle_exception("moduleinstance not found");
        }

        switch ($cm->modname) {
            case 'assign':
                return $this->get_from_assignment($content, $moduleinstance, $userid, $filename);
            case 'workshop':
                return $this->get_from_workshop($content, $moduleinstance, $userid, $filename);
            case 'forum':
                return $this->get_from_forum($content, $moduleinstance, $userid, $filename);
            default:
                throw new moodle_exception("Course module not valid");
        }
    }

    /**
     * Get submission records from an assignment
     *
     * @param object|string $content Storedfile object or onlinetext
     * @param object $moduleinstance Module instance
     * @param string $userid Userid of the author of the document
     * @param string $filename Filename of the document
     *
     * @return object|null Submission record or null if not found
     */
    private function get_from_assignment($content, $moduleinstance, $userid, $filename) {
        $submission = null;
        $onlinetext = true;

        if (is_object($content) && $content instanceof stored_file && !empty($content->get_id())) {
            $onlinetext = false;
            $submission = $this->get_by_id($content, 'assign_submission');
        }

        if (!$submission && $onlinetext && $content && is_string($content)) {
            $sql = "SELECT ass.*, assot.onlinetext
                    FROM {assign_submission} ass
                    JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                    WHERE ass.assignment = ?";

            $submission = $this->get_by_content($content, $moduleinstance, $sql, 'onlinetext');
        }

        // Search by content using fallback methods.
        if (!$submission) {
            $submission = $this->get_by_filename($filename, 'assign_submission');

            if (!$submission) {
                // Individual submission.
                $submission = $this->moodledatabase->get_record_sql(
                    "SELECT ass.*
                        FROM {assign_submission} ass
                        JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                        WHERE ass.assignment = ? AND ass.userid = ?",
                    [$moduleinstance->id, $userid]
                );

                // Group submission where user is member.
                if (!$submission) {
                    $submission = $this->moodledatabase->get_record_sql(
                        "SELECT ass.*
                            FROM {assign_submission} ass
                            JOIN {assignsubmission_onlinetext} assot ON assot.submission = ass.id
                            JOIN {groups_members} gm ON gm.groupid = ass.groupid
                            WHERE ass.assignment = ? AND gm.userid = ? AND ass.groupid != 0",
                        [$moduleinstance->id, $userid]
                    );
                }
            }

            if (!$submission) {
                throw new moodle_exception('Submission not found');
            }
        }
        return $submission;
    }

    /**
     * Get submission records from a workshop
     *
     * @param object|string $content Storedfile object or onlinetext
     * @param object $moduleinstance Module instance
     * @param string $userid Userid of the author of the document
     * @param string $filename Filename of the document
     *
     * @return object|null Submission record or null if not found
     */
    private function get_from_workshop($content, $moduleinstance, $userid, $filename) {
        $submission = null;
        $onlinetext = true;

        // Search by id for files.
        if (is_object($content) && $content instanceof stored_file && !empty($content->get_id())) {
            $onlinetext = false;
            $submission = $this->get_by_id($content, 'workshop_submissions');
        }

        // Search for onlinetext.
        if (!$submission && $onlinetext && $content && is_string($content)) {
            $submission = $this->get_by_content(
                $content,
                $moduleinstance,
                "SELECT * FROM {workshop_submissions} WHERE workshopid = ?",
                'content'
            );
        }

        // Search by content using fallback methods.
        if (!$submission) {
            $submission = $this->get_by_filename($filename, 'workshop_submissions');

            if (!$submission) {
                // Individual submission.
                $submission = $this->moodledatabase->get_record('workshop_submissions', [
                    'workshopid' => $moduleinstance->id,
                    'authorid' => $userid,
                ]);
            }

            if (!$submission) {
                throw new moodle_exception('Submission not found');
            }
        }

        return $submission;
    }

    /**
     * Get submission records from a forum
     *
     * @param object|string $content Storedfile object or onlinetext
     * @param object $moduleinstance Module instance
     * @param string $userid Userid of the author of the document
     * @param string $filename Filename of the document
     *
     * @return object|null Submission record or null if not found
     */
    private function get_from_forum($content, $moduleinstance, $userid, $filename) {
        $submission = null;
        $onlinetext = true;

        // Search by id for files.
        if (is_object($content) && $content instanceof stored_file && !empty($content->get_id())) {
            $onlinetext = false;
            $submission = $this->get_by_id($content, 'forum_posts');
        }

        // Search for onlinetext.
        if (!$submission && $onlinetext && $content && is_string($content)) {
            $sql = "SELECT fp.* FROM {forum_posts} fp
                    JOIN {forum_discussions} fd ON fp.discussion = fd.id
                    WHERE fd.forum = ?";

            $submission = $this->get_by_content($content, $moduleinstance, $sql, 'message');
        }

        // Search by content using fallback methods.
        if (!$submission) {
            $submission = $this->get_by_filename($filename, 'forum_posts');

            if (!$submission) {
                // Find posts by user in this forum.
                $sql = "SELECT fp.* FROM {forum_posts} fp
                        JOIN {forum_discussions} fd ON fp.discussion = fd.id
                        WHERE fd.forum = ? AND fp.userid = ?
                        ORDER BY fp.created DESC";
                $posts = $this->moodledatabase->get_records_sql($sql, [$moduleinstance->id, $userid], 0, 1);
                if (!empty($posts)) {
                    $submission = reset($posts);
                }
            }

            if (!$submission) {
                throw new moodle_exception('Submission not found');
            }
        }
        return $submission;
    }

    /**
     * Get submission records by its identifier
     *
     * @param object $content Storedfile object
     * @param string $table table where to find the submission
     *
     * @return object|null Submission record or null if not found
     */
    private function get_by_id($content, $table) {
        $filerecord = $this->moodledatabase->get_record('files', ['id' => $content->get_id()]);
        if ($filerecord) {
            $submission = $this->moodledatabase->get_record($table, ['id' => $filerecord->itemid]);
            return $submission === false ? null : $submission;
        }
        return null;
    }

    /**
     * Get submission records by its content
     *
     * @param string $content Document content
     * @param object $moduleinstance Module instance
     * @param string $sql SQL query
     * @param string $text Key of the text property in the submission
     *
     * @return object|null Submission record or null if not found
     */
    private function get_by_content($content, $moduleinstance, $sql, $text) {
        $contentidentifier = sha1($content);

        $submissions = $this->moodledatabase->get_records_sql($sql, [$moduleinstance->id]);
        foreach ($submissions as $sub) {
            $subidentifier = sha1($sub->$text);
            if ($subidentifier === $contentidentifier) {
                return $sub;
            }
        }
        return null;
    }

    /**
     * Get submission records by its filename
     *
     * @param string $filename Filename of the Document
     * @param string $table table where to find the submission
     *
     * @return object|null Submission record or null if not found
     */
    private function get_by_filename($filename, $table) {
        // Extract submission ID from filename based on the table type.
        $pattern = '';

        switch ($table) {
            case 'assign_submission':
                $pattern = '/^assign-(\d+)\.htm$/';
                break;
            case 'workshop_submissions':
                $pattern = '/^workshop-(\d+)\.htm$/';
                break;
            case 'forum_posts':
                $pattern = '/^forum-(\d+)\.htm$/';
                break;
            default:
                return null;
        }

        if (!empty($filename) && preg_match($pattern, $filename, $matches)) {
            $submissionid = $matches[1];
            $submission = $this->moodledatabase->get_record($table, ['id' => $submissionid]);
            return $submission === false ? null : $submission;
        }
        return null;
    }
}

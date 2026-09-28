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

namespace plagiarism_compilatio;

use plagiarism_compilatio\compilatio\file;
use plagiarism_compilatio\compilatio\identifier;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

/**
 * Tests of the code run when the assignment submissions page is displayed.
 *
 * @package    plagiarism_compilatio
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class submissions_page_test extends \advanced_testcase {
    /** @var \stdClass Course */
    private $course;

    /** @var \stdClass Student */
    private $student;

    /**
     * Create a course with an enrolled student.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, 'student');
    }

    /**
     * Create an assignment.
     *
     * @param  bool $teamsubmission Whether students submit in groups
     * @return \assign
     */
    private function create_assign(bool $teamsubmission = false): \assign {
        $record = $this->getDataGenerator()->create_module('assign', [
            'course' => $this->course->id,
            'assignsubmission_file_enabled' => 1,
            'assignsubmission_file_maxfiles' => 1,
            'assignsubmission_file_maxsizebytes' => 1024 * 1024,
            'assignsubmission_onlinetext_enabled' => 1,
            'teamsubmission' => (int) $teamsubmission,
        ]);
        $cm = get_coursemodule_from_instance('assign', $record->id);

        return new \assign(\context_module::instance($cm->id), $cm, $this->course);
    }

    /**
     * Add a file to a submission.
     *
     * @param  \assign   $assign     Assignment
     * @param  \stdClass $submission Submission record
     * @param  string    $filename   File name
     * @return \stored_file
     */
    private function add_submission_file(\assign $assign, \stdClass $submission, string $filename): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => $assign->get_context()->id,
            'component' => 'assignsubmission_file',
            'filearea' => 'submission_files',
            'itemid' => $submission->id,
            'filepath' => '/',
            'filename' => $filename,
            'userid' => $this->student->id,
        ], 'Content of ' . $filename . ' ' . random_string(20));
    }

    /**
     * Remove the content of a stored file from moodledata, as after a failed migration or restore.
     *
     * @param \stored_file $file Stored file
     */
    private function remove_file_content(\stored_file $file): void {
        unlink(get_file_storage()->get_file_system()->get_local_path_from_storedfile($file));
    }

    /**
     * Add a Compilatio document record.
     *
     * @param \assign $assign   Assignment
     * @param array   $fields   Record fields
     * @return \stdClass
     */
    private function add_compilatio_record(\assign $assign, array $fields): \stdClass {
        global $DB;

        $record = (object) array_merge([
            'cm' => $assign->get_course_module()->id,
            'userid' => $this->student->id,
            'identifier' => sha1(random_string(20)),
            'externalid' => sha1(random_string(20)),
            'status' => 'sent',
            'globalscore' => 0,
            'ignoredscores' => '',
            'timesubmitted' => time(),
        ], $fields);
        $record->id = $DB->insert_record('plagiarism_compilatio_files', $record);

        return $record;
    }

    /**
     * A document is still found from its readable file content.
     *
     * @covers \plagiarism_compilatio\compilatio\file::compilatio_get_document
     */
    public function test_get_document_with_readable_file(): void {
        $assign = $this->create_assign();
        $cmid = $assign->get_course_module()->id;
        $storedfile = $this->add_submission_file($assign, $assign->get_user_submission($this->student->id, true), 'essay.pdf');

        $identifier = (new identifier($this->student->id, $cmid))->create_from_file($storedfile);
        $record = $this->add_compilatio_record($assign, ['identifier' => $identifier, 'filename' => 'essay.pdf']);

        $document = (new file())->compilatio_get_document($cmid, $storedfile, $this->student->id);

        $this->assertEquals($record->id, $document->id);
    }

    /**
     * A file missing from moodledata no longer throws an exception.
     *
     * @covers \plagiarism_compilatio\compilatio\file::compilatio_get_document
     */
    public function test_get_document_with_missing_file_content(): void {
        $assign = $this->create_assign();
        $cmid = $assign->get_course_module()->id;
        $storedfile = $this->add_submission_file($assign, $assign->get_user_submission($this->student->id, true), 'essay.pdf');
        $this->remove_file_content($storedfile);

        $document = (new file())->compilatio_get_document($cmid, $storedfile, $this->student->id);
        $this->assertDebuggingCalled();
        $this->assertFalse($document);

        // A legacy record, identified by the Moodle content hash, is still found.
        $record = $this->add_compilatio_record($assign, [
            'identifier' => $storedfile->get_contenthash(),
            'filename' => 'essay.pdf',
        ]);
        $document = (new file())->compilatio_get_document($cmid, $storedfile, $this->student->id);
        $this->assertDebuggingCalled();
        $this->assertEquals($record->id, $document->id);
    }

    /**
     * Unsent files and online texts of individual submissions are detected without reading file contents.
     *
     * @covers ::compilatio_has_unsent_documents
     */
    public function test_has_unsent_documents_individual_submissions(): void {
        global $DB;

        $assign = $this->create_assign();
        $cmid = $assign->get_course_module()->id;
        $this->assertFalse(compilatio_has_unsent_documents($cmid));

        $submission = $assign->get_user_submission($this->student->id, true);
        $storedfile = $this->add_submission_file($assign, $submission, 'essay.pdf');
        $this->remove_file_content($storedfile); // The check must not need the file content.
        $this->assertTrue(compilatio_has_unsent_documents($cmid));

        $this->add_compilatio_record($assign, ['filename' => 'essay.pdf']);
        $this->assertFalse(compilatio_has_unsent_documents($cmid));

        $DB->insert_record('assignsubmission_onlinetext', (object) [
            'assignment' => $assign->get_instance()->id,
            'submission' => $submission->id,
            'onlinetext' => '<p>Online text</p>',
            'onlineformat' => FORMAT_HTML,
        ]);
        $this->assertTrue(compilatio_has_unsent_documents($cmid));

        $this->add_compilatio_record($assign, ['filename' => 'assign-' . $submission->id . '.htm']);
        $this->assertFalse(compilatio_has_unsent_documents($cmid));
    }

    /**
     * Submissions of users who are not enrolled any more are ignored, as in compilatio_get_unsent_documents().
     *
     * @covers ::compilatio_has_unsent_documents
     */
    public function test_has_unsent_documents_ignores_unenrolled_users(): void {
        $assign = $this->create_assign();
        $cmid = $assign->get_course_module()->id;
        $this->add_submission_file($assign, $assign->get_user_submission($this->student->id, true), 'essay.pdf');
        $this->assertTrue(compilatio_has_unsent_documents($cmid));

        $instances = array_filter(
            enrol_get_instances($this->course->id, true),
            function ($instance) {
                return $instance->enrol === 'manual';
            }
        );
        enrol_get_plugin('manual')->unenrol_user(reset($instances), $this->student->id);

        $this->assertFalse(compilatio_has_unsent_documents($cmid));
    }

    /**
     * Group submissions are matched on the group, as Compilatio stores them.
     *
     * @covers ::compilatio_has_unsent_documents
     */
    public function test_has_unsent_documents_team_submissions(): void {
        $group = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, 'userid' => $this->student->id]);

        $assign = $this->create_assign(true);
        $cmid = $assign->get_course_module()->id;
        $submission = $assign->get_group_submission($this->student->id, $group->id, true);
        $this->add_submission_file($assign, $submission, 'report.pdf');
        $this->assertTrue(compilatio_has_unsent_documents($cmid));

        // A record for another owner does not count.
        $this->add_compilatio_record($assign, ['filename' => 'report.pdf']);
        $this->assertTrue(compilatio_has_unsent_documents($cmid));

        $this->add_compilatio_record($assign, ['userid' => 0, 'groupid' => $group->id, 'filename' => 'report.pdf']);
        $this->assertFalse(compilatio_has_unsent_documents($cmid));
    }

    /**
     * The submissions page can be displayed even when a submitted file is missing from moodledata,
     * including when the teacher asks to send it (it must not be sent again endlessly).
     *
     * @covers \plagiarism_plugin_compilatio::get_links
     * @covers \plagiarism_compilatio\output\document_frame::get_document_frame
     */
    public function test_get_links_with_missing_file_content(): void {
        global $DB;

        set_config('enabled', 1, 'plagiarism_compilatio');
        set_config('apikey', 'test', 'plagiarism_compilatio');
        set_config('enable_mod_assign', 1, 'plagiarism_compilatio');

        $assign = $this->create_assign();
        $cmid = $assign->get_course_module()->id;
        $DB->insert_record('plagiarism_compilatio_cm_cfg', (object) [
            'cmid' => $cmid,
            'activated' => 1,
            'analysistype' => 'manual',
            'ignoredscores' => '',
        ]);

        $storedfile = $this->add_submission_file($assign, $assign->get_user_submission($this->student->id, true), 'essay.pdf');
        $this->remove_file_content($storedfile);

        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->setUser($teacher);

        $linkarray = ['cmid' => $cmid, 'userid' => $this->student->id, 'file' => $storedfile];
        $plugin = new \plagiarism_plugin_compilatio();

        $this->assertStringContainsString("id='cmp-", $plugin->get_links($linkarray));
        $this->assertNotEmpty($this->getDebuggingMessages());
        $this->resetDebugging();

        $_GET['sendfile'] = $storedfile->get_id();
        $this->assertStringContainsString("id='cmp-", $plugin->get_links($linkarray));
        $this->assertNotEmpty($this->getDebuggingMessages());
        $this->resetDebugging();
        unset($_GET['sendfile']);

        $this->assertEquals(0, $DB->count_records('plagiarism_compilatio_files', ['cm' => $cmid]));
    }
}

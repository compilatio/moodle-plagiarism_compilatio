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
 * provider.php - Privacy class for requesting and deleting user data
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\privacy;

defined('MOODLE_INTERNAL') || die();

if (interface_exists('\core_plagiarism\privacy\plagiarism_user_provider')) {
    interface user_provider extends \core_plagiarism\privacy\plagiarism_user_provider {

    }
} else {
    interface user_provider {

    }
}

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\writer;
use plagiarism_compilatio\compilatio\api;

/**
 * Class provider for exporting or deleting data
 */
class provider implements
    // This plugin has data and must therefore define the metadata provider in order to describe it.
    \core_privacy\local\metadata\provider,

    // This is a plagiarism plugin. It interacts with the plagiarism subsystem rather than with core.
    \core_plagiarism\privacy\plagiarism_provider,

    user_provider {

    // This trait must be included to provide the relevant polyfill for the metadata provider.
    use \core_privacy\local\legacy_polyfill;

    // This trait must be included to provide the relevant polyfill for the plagirism provider.
    use \core_plagiarism\privacy\legacy_polyfill;

    // The required methods must be in this format starting with an underscore.
    /**
     * Return the fields where personal data is stored
     *
     * @param   collection  $collection The initialised collection to add items to.
     * @return  collection  $collection The updated collection of user data.
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:core_files'
        );
        $collection->add_subsystem_link(
            'core_plagiarism',
            [],
            'privacy:metadata:core_plagiarism'
        );

        $collection->add_database_table('plagiarism_compilatio_files', [
            'userid'   => 'privacy:metadata:plagiarism_compilatio_files:userid',
            'filename' => 'privacy:metadata:plagiarism_compilatio_files:filename',
        ], 'privacy:metadata:plagiarism_compilatio_files');

        $collection->add_database_table('plagiarism_compilatio_user', [
            'userid'       => 'privacy:metadata:plagiarism_compilatio_user:userid',
            'compilatioid' => 'privacy:metadata:plagiarism_compilatio_user:compilatioid',
        ], 'privacy:metadata:plagiarism_compilatio_user');

        $collection->add_external_location_link('External Compilatio Document', [
            'authors'   => 'privacy:metadata:external_compilatio_document:authors',
            'depositor' => 'privacy:metadata:external_compilatio_document:depositor',
            'filename'  => 'privacy:metadata:external_compilatio_document:filename',
        ], 'privacy:metadata:external_compilatio_document');

        $collection->add_external_location_link('External Compilatio User', [
            'firstname' => 'privacy:metadata:external_compilatio_user:firstname',
            'lastname'  => 'privacy:metadata:external_compilatio_user:lastname',
            'email'     => 'privacy:metadata:external_compilatio_user:email',
            'username'  => 'privacy:metadata:external_compilatio_user:username',
        ], 'privacy:metadata:external_compilatio_user');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int         $userid         The user to search.
     * @return  contextlist $contextlist    The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {

        $sql = "SELECT c.id
                FROM {context} c
                JOIN {course_modules} cm ON c.instanceid = cm.id
                JOIN {plagiarism_compilatio_files} pcf ON cm.id = pcf.cm
                WHERE pcf.userid = ? AND c.contextlevel = ?";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [$userid, CONTEXT_MODULE]);

        return $contextlist;
    }

    // This is one of the polyfilled methods from the plagiarism provider.
    /**
     * Export all data for the specified userid and context.
     *
     * @param   int         $userid     The user to export.
     * @param   \context    $context    The context to export.
     * @param   array       $subcontext The subcontext within the context to export this information to.
     * @param   array       $linkarray  The weird and wonderful link array used to display information for a specific item.
     */
    public static function export_plagiarism_user_data(int $userid, \context $context, array $subcontext, array $linkarray) {

        global $DB;

        $submissions = $DB->get_records('plagiarism_compilatio_files', ['userid' => $userid, 'cm' => $context->instanceid]);

        foreach ($submissions as $submission) {
            $data["plagiarism_compilatio_files"][] = (object)$submission;
        }

        if (isset($data)) {
            writer::with_context($context)->export_data([], (object)$data);
        }
    }

    /**
     * Delete all data for all users for the specified context.
     *
     * @param   \context    $context    The context to delete in.
     */
    public static function delete_plagiarism_for_context(\context $context) {

        global $DB, $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        $files = $DB->get_records('plagiarism_compilatio_files', ["cm" => $context->instanceid]);
        compilatio_delete_files($files);

        $DB->delete_records('plagiarism_compilatio_files', ['cm' => $context->instanceid]);
    }

    /**
     * Delete all data for the specified user in the specified context.
     *
     * @param   int         $userid     The user to delete.
     * @param   \context    $context    The context to refine the deletion.
     */
    public static function delete_plagiarism_for_user(int $userid, \context $context) {

        global $DB, $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        $ownerfile = get_config('plagiarism_compilatio', 'owner_file');

        // If the student owns the document (and not the school), we can delete everything from the databases.
        if ($ownerfile === '0') {
            $files = $DB->get_records('plagiarism_compilatio_files', ["userid" => $userid]);
            compilatio_delete_files($files);

            $DB->delete_records('plagiarism_compilatio_files', ['userid' => $userid]);
        }
    }

    /**
     * Deletes all user content information for the provided users and context.
     *
     * @param  array    $userids   The users to delete.
     * @param  \context $context   The context to refine the deletion.
     */
    public static function delete_plagiarism_for_users(array $userids, \context $context) {

        global $DB, $CFG;

        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

        $cmid = $context->instanceid;

        foreach ($userids as $userid) {
            $files = $DB->get_records('plagiarism_compilatio_files', ["userid" => $userid, "cm" => $cmid]);
            compilatio_delete_files($files);
        }

        foreach ($userids as $userid) {
            $DB->delete_records('plagiarism_compilatio_files', ['userid' => $userid, 'cm' => $cmid]);
        }
    }
}

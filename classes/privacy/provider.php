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
 * provider.php - privacy class for requesting and deleting user data
 *
 * @package    plagiarism_compilatio
 * @copyright  2019 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\privacy;

if (interface_exists('\core_plagiarism\privacy\plagiarism_user_provider')) {
    interface user_provider extends \core_plagiarism\privacy\plagiarism_user_provider{}
} else {
    interface user_provider {};
}

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\writer;

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
    public static function _get_metadata(collection $collection) : collection {

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
            'id'                => 'privacy:metadata:plagiarism_compilatio_files:id',
            'cm'                => 'privacy:metadata:plagiarism_compilatio_files:cm',
            'userid'            => 'privacy:metadata:plagiarism_compilatio_files:userid',
            'identifier'        => 'privacy:metadata:plagiarism_compilatio_files:identifier',
            'filename'          => 'privacy:metadata:plagiarism_compilatio_files:filename',
            'timesubmitted'     => 'privacy:metadata:plagiarism_compilatio_files:timesubmitted',
            'statuscode'        => 'privacy:metadata:plagiarism_compilatio_files:statuscode',
            'externalid'        => 'privacy:metadata:plagiarism_compilatio_files:externalid',
            'reporturl'         => 'privacy:metadata:plagiarism_compilatio_files:reporturl',
            'similarityscore'   => 'privacy:metadata:plagiarism_compilatio_files:similarityscore',
            'attempt'           => 'privacy:metadata:plagiarism_compilatio_files:attempt',
            'errorresponse'     => 'privacy:metadata:plagiarism_compilatio_files:errorresponse'
        ], 'privacy:metadata:plagiarism_compilatio_files');

        $collection->add_external_location_link('External Compilatio Document', [
            'lastname'          => 'privacy:metadata:external_compilatio_document:lastname',
            'firstname'         => 'privacy:metadata:external_compilatio_document:firstname',
            'email_adress'      => 'privacy:metadata:external_compilatio_document:email_adress',
            'user_id'           => 'privacy:metadata:external_compilatio_document:user_id',
            'filename'          => 'privacy:metadata:external_compilatio_document:filename',
            'upload_date'       => 'privacy:metadata:external_compilatio_document:upload_date',
            'id'                => 'privacy:metadata:external_compilatio_document:id',
            'indexed'           => 'privacy:metadata:external_compilatio_document:indexed'
        ], 'privacy:metadata:external_compilatio_document');

        $collection->add_external_location_link('External Compilatio Report', [
            'id'                    => 'privacy:metadata:external_compilatio_report:id',
            'doc_id'                => 'privacy:metadata:external_compilatio_report:doc_id',
            'user_id'               => 'privacy:metadata:external_compilatio_report:user_id',
            'start'                 => 'privacy:metadata:external_compilatio_report:start',
            'end'                   => 'privacy:metadata:external_compilatio_report:end',
            'state'                 => 'privacy:metadata:external_compilatio_report:state',
            'plagiarism_percent'    => 'privacy:metadata:external_compilatio_report:plagiarism_percent'
        ], 'privacy:metadata:external_compilatio_report');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int         $userid         The user to search.
     * @return  contextlist $contextlist    The list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid(int $userid) : contextlist {

        $sql = "SELECT DISTINCT c.id
                FROM {context} c
                JOIN {course_modules} cm ON c.instanceid = cm.id
                JOIN {plagiarism_compilatio_files} pcf ON cm.id = pcf.cm
                WHERE pcf.userid = ? AND c.contextlevel = ?";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, array($userid, CONTEXT_MODULE));

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
    public static function _export_plagiarism_user_data(int $userid, \context $context, array $subcontext, array $linkarray) {

        global $DB;

        $submissions = $DB->get_records('plagiarism_compilatio_files', array('userid' => $userid, 'cm' => $context->instanceid));

        foreach ($submissions as $submission) {
            $data["plagiarism_compilatio_files"][] = (object)$submission;
        }

        writer::with_context($context)->export_data([], (object)$data);
    }

    /**
     * Delete all data for all users for the specified context.
     *
     * @param   \context    $context    The context to delete in.
     */
    public static function _delete_plagiarism_for_context(\context $context) {

        global $DB;

        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/api.class.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');

        $ws = new \ws_helper();
        $compilatio = $ws->get_ws();

        if (isset($compilatio->key, $compilatio->urlrest)) {
            $compids = $DB->get_fieldset_select('plagiarism_compilatio_files', 'externalid', 'cm = '.$context->instanceid);
            foreach ($compids as $compid) {
                $compilatio->set_indexing_state($compid, false);
                $compilatio->del_doc($compid);
            }
        }

        $DB->delete_records('plagiarism_compilatio_files', array('cm' => $context->instanceid));
    }

    /**
     * Delete all data for the specified user in the specified context.
     *
     * @param   int         $userid     The user to delete
     * @param   \context    $context    The context to refine the deletion.
     */
    public static function _delete_plagiarism_for_user(int $userid, \context $context) {

        global $DB;

        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/api.class.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');

        $ws = new \ws_helper();
        $compilatio = $ws->get_ws();

        // Actuellement, Moodle ne nous donne pas le contexte des discussions...
        // Donc on ne peut pas supprimer les informations des messages dans les forums qui sont stockés dans la BDD de Compilatio
        // Comme cette fonction est appelée lorsqu'un utilisateur souhaite supprimer toutes ses données
        // On va supprimer ses données de la BDD de Compilatio indépendamment du contexte donné (ce paramètre est-il utile au final ?)

        // Si la classe compilatioservice est valide
        if (isset($compilatio->key, $compilatio->urlrest)) {
            // On récupère tous les documents d'un utilisateur
            $compids = $DB->get_fieldset_select('plagiarism_compilatio_files', 'externalid', 'userid = '.$userid);
            // Pour chaque document
            foreach ($compids as $compid) {
                // On le désindexe et on le supprime
                $compilatio->set_indexing_state($compid, false);
                $compilatio->del_doc($compid);
            }
        }

        $DB->delete_records('plagiarism_compilatio_files', array('userid' => $userid));
    }

    /**
     * Deletes all user content information for the provided users and context.
     *
     * @param  array    $userids   The users to delete
     * @param  \context $context   The context to refine the deletion.
     */
    public static function delete_plagiarism_for_users(array $userids, \context $context) {
        
        global $DB;

        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/api.class.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        require_once($CFG->dirroot . '/plagiarism/compilatio/helper/ws_helper.php');

        $cmid = $context->instanceid;

        // Si la classe compilatioservice est valide
        if (isset($compilatio->key, $compilatio->urlrest)) {
            // Pour chaque utilisateur
            foreach($userids as $userid) {
                // On récupère les ID des documents qu'il a soumis à Compilatio dans ce contexte
                $compids = $DB->get_fieldset_select('plagiarism_compilatio_files', 'externalid', 'userid = '.$userid.' AND cm = '.$cmid);
                // Pour chaque document
                foreach ($compids as $compid) {
                    // On le désindexe et on le supprime de la base de données de Compilatio
                    $compilatio->set_indexing_state($compid, false);
                    $compilatio->del_doc($compid);
                }
            }
        }

        foreach($userids as $userid) {
            $DB->delete_records('plagiarism_compilatio_files', array('userid' => $userid, 'cm' => $cmid));
        }
    }
}
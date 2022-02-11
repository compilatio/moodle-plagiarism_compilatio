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
 * provider_test.php - Test class of the privacy provider
 *
 * @package    plagiarism_compilatio
 * @copyright  2019 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use plagiarism_compilatio\privacy\provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Class plagiarism_compilatio_privacy_provider_testcase
 *
 * @copyright  2019 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plagiarism_compilatio_privacy_provider_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Test fonction _get_metadata
     */
    public function test_get_metadata() {

        $this->resetAfterTest();

        // On charge la liste des données personnelles que Compilatio stocke.
        $collection = new collection('plagiarism_compilatio');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();

        // On vérifie qu'il y a bien cinq items.
        $this->assertCount(5, $itemcollection);

        // On vérifie que core_files est retourné.
        $this->assertEquals('core_files', $itemcollection[0]->get_name());
        $this->assertEquals('privacy:metadata:core_files', $itemcollection[0]->get_summary());

        // On vérifie que core_plagiarism est retourné.
        $this->assertEquals('core_plagiarism', $itemcollection[1]->get_name());
        $this->assertEquals('privacy:metadata:core_plagiarism', $itemcollection[1]->get_summary());

        // On vérifie que plagiarism_compilatio_files est retourné.
        $this->assertEquals('plagiarism_compilatio_files', $itemcollection[2]->get_name());

        // On vérifie que le tableau des champs retournés possède bien les bonnes clés.
        $privacyfields = $itemcollection[2]->get_privacy_fields();
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('cm', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('identifier', $privacyfields);
        $this->assertArrayHasKey('filename', $privacyfields);
        $this->assertArrayHasKey('externalid', $privacyfields);
        $this->assertArrayHasKey('reporturl', $privacyfields);
        $this->assertArrayHasKey('statuscode', $privacyfields);
        $this->assertArrayHasKey('similarityscore', $privacyfields);
        $this->assertArrayHasKey('attempt', $privacyfields);
        $this->assertArrayHasKey('timesubmitted', $privacyfields);
        $this->assertArrayHasKey('recyclebinid', $privacyfields);
        $this->assertArrayHasKey('indexed', $privacyfields);

        // On vérifie que External Compilatio Document est retourné.
        $this->assertEquals('External Compilatio Document', $itemcollection[3]->get_name());

        // On vérifie que le tableau des champs retournés possède bien les bonnes clés.
        $privacyfields = $itemcollection[3]->get_privacy_fields();
        $this->assertArrayHasKey('lastname', $privacyfields);
        $this->assertArrayHasKey('firstname', $privacyfields);
        $this->assertArrayHasKey('email_adress', $privacyfields);
        $this->assertArrayHasKey('user_id', $privacyfields);
        $this->assertArrayHasKey('filename', $privacyfields);
        $this->assertArrayHasKey('upload_date', $privacyfields);
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('indexed', $privacyfields);

        // On vérifie que External Compilatio Report est retourné.
        $this->assertEquals('External Compilatio Report', $itemcollection[4]->get_name());

        // On vérifie que le tableau des champs retournés possède bien les bonnes clés.
        $privacyfields = $itemcollection[4]->get_privacy_fields();
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('doc_id', $privacyfields);
        $this->assertArrayHasKey('user_id', $privacyfields);
        $this->assertArrayHasKey('start', $privacyfields);
        $this->assertArrayHasKey('end', $privacyfields);
        $this->assertArrayHasKey('state', $privacyfields);
        $this->assertArrayHasKey('plagiarism_percent', $privacyfields);
    }

    /**
     * Test fonction _get_contexts_for_userid
     */
    public function test_get_contexts_for_userid() {

        $this->resetAfterTest();

        // On crée un étudiant.
        $student = $this->getDataGenerator()->create_user();

        // On crée cinq modules de cours, cinq contexts et cinq plagiarism files.
        for ($i = 0; $i < 5; $i++) {
            $coursemodule = $this->create_partial_coursemodule();
            $context = $this->create_partial_context($coursemodule->id);
            $this->create_partial_plagiarismfile($coursemodule->id, $student->id);
        }

        // On vérifie que la liste des contextes retournée est bien égale à 5.
        $contextlist = provider::get_contexts_for_userid($student->id);
        $this->assertCount(5, $contextlist);
    }

    /**
     * Test fonction _export_plagiarism_user_data
     */
    public function test_export_plagiarism_user_data() {

        $this->resetAfterTest();

        // On crée un étudiant.
        $student = $this->getDataGenerator()->create_user();

        // On crée cinq modules de cours, cinq contexts et cinq plagiarismfiles...
        // Et on vérifie que la liste des contextes retournée est bien égale à 5.
        for ($i = 0; $i < 5; $i++) {
            $coursemodule = $this->create_partial_coursemodule();
            $context = $this->create_partial_context($coursemodule->id);
            $this->create_partial_plagiarismfile($coursemodule->id, $student->id);
        }

        $context = context_module::instance($coursemodule->id);

        // On vérifie que, à l'exportation des données, il y a bien quelque chose à visualiser pour l'utilisateur.
        provider::export_plagiarism_user_data($student->id, $context, array(), array());
        $writer = writer::with_context($context);

        $this->assertTrue($writer->has_any_data());
    }

    /**
     * Test fonction _delete_plagiarism_for_context
     */
    public function test_delete_plagiarism_for_context() {

        $this->resetAfterTest();
        global $DB;

        // On crée un module de cours et un contexte.
        $coursemodule = $this->create_partial_coursemodule();
        $context = $this->create_partial_context($coursemodule->id);

        // On crée cinq plagiarismfiles, un par étudiant, dans un contexte précis.
        for ($i = 0; $i < 5; $i++) {
            $student = $this->getDataGenerator()->create_user();
            $this->create_partial_plagiarismfile($coursemodule->id, $student->id);
        }

        // On vérifie qu'on a bien cinq plagiarismfiles dans la tablea plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(5, $nbplagiarismfiles);

        // On supprime les plagiarismfiles dans ce contexte précis.
        $context = context_module::instance($coursemodule->id);
        $this->create_partial_webservice();
        provider::delete_plagiarism_for_context($context);

        // On vérifie qu'on a bien vidé la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(0, $nbplagiarismfiles);
    }

    /**
     * Test fonction _delete_plagiarism_for_user (cas où les fichiers appartiennent à l'établissement)
     */
    public function test_delete_plagiarism_for_user_owner_school() {

        $this->resetAfterTest();
        global $DB;

        // On crée un étudiant.
        $student = $this->getDataGenerator()->create_user();

        // On crée deux contextes différents.
        $coursemodule1 = $this->create_partial_coursemodule();
        $context1 = $this->create_partial_context($coursemodule1->id);
        $coursemodule2 = $this->create_partial_coursemodule();
        $context2 = $this->create_partial_context($coursemodule2->id);

        // On crée cinq plagiarismfiles pour chaque contexte.
        for ($i = 0; $i < 5; $i++) {
            $this->create_partial_plagiarismfile($coursemodule1->id, $student->id);
            $this->create_partial_plagiarismfile($coursemodule2->id, $student->id);
        }

        // On vérifie qu'on a bien dix plagiarismfiles dans la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(10, $nbplagiarismfiles);

        // On lance la suppression des fichiers de l'étudiant.
        $context = context_module::instance($coursemodule1->id);
        $this->create_partial_webservice('1'); // Les fichiers appartiennent bien à l'établissement.
        provider::delete_plagiarism_for_user($student->id, $context);

        // On vérifie qu'on a toujours les dix plagiarismfiles dans la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(10, $nbplagiarismfiles);
    }

    /**
     * Test fonction _delete_plagiarism_for_user (cas où les fichiers appartiennent à l'étudiant)
     */
    public function test_delete_plagiarism_for_user_owner_student() {

        $this->resetAfterTest();
        global $DB;

        // On crée un étudiant.
        $student = $this->getDataGenerator()->create_user();

        // On crée deux contextes différents.
        $coursemodule1 = $this->create_partial_coursemodule();
        $context1 = $this->create_partial_context($coursemodule1->id);
        $coursemodule2 = $this->create_partial_coursemodule();
        $context2 = $this->create_partial_context($coursemodule2->id);

        // On crée cinq plagiarismfiles pour chaque contexte.
        for ($i = 0; $i < 5; $i++) {
            $this->create_partial_plagiarismfile($coursemodule1->id, $student->id);
            $this->create_partial_plagiarismfile($coursemodule2->id, $student->id);
        }

        // On vérifie qu'on a bien dix plagiarismfiles dans la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(10, $nbplagiarismfiles);

        // On lance la suppression des fichiers de l'étudiant.
        $context = context_module::instance($coursemodule1->id);
        $this->create_partial_webservice('0'); // Les fichiers appartiennent bien à l'étudiant.
        provider::delete_plagiarism_for_user($student->id, $context);

        // On vérifie qu'on a bien vidé la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(0, $nbplagiarismfiles);
    }

    /**
     * Test fonction delete_plagiarism_for_users
     */
    public function test_delete_plagiarism_for_users() {

        $this->resetAfterTest();
        global $DB;

        // On crée trois étudiants.
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $student3 = $this->getDataGenerator()->create_user();

        // On crée un module de cours et un contexte.
        $coursemodule = $this->create_partial_coursemodule();
        $context = $this->create_partial_context($coursemodule->id);

        // On crée quinze plagiarismfiles, cinq par étudiant, dans un contexte précis.
        for ($i = 0; $i < 5; $i++) {
            $this->create_partial_plagiarismfile($coursemodule->id, $student1->id);
            $this->create_partial_plagiarismfile($coursemodule->id, $student2->id);
            $this->create_partial_plagiarismfile($coursemodule->id, $student3->id);
        }

        // On vérifie que la table plagiarism_compilatio_files contient bien quinze plagiarismfiles.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(15, $nbplagiarismfiles);

        // On crée la liste d'IDs avec les étudiants 1 et 2.
        $userids = array($student1->id, $student2->id);

        // On supprime les plagiarismfiles pour ces deux étudiants.
        $context = context_module::instance($coursemodule->id);
        $this->create_partial_webservice();
        provider::delete_plagiarism_for_users($userids, $context);

        // On vérifie qu'il ne reste plus que cinq plagiarismfiles dans la table plagiarism_compilatio_files.
        $nbplagiarismfiles = $DB->count_records('plagiarism_compilatio_files');
        $this->assertEquals(5, $nbplagiarismfiles);
    }

    /**
     * Fonction qui insère seulement quelques champs dans la table course_modules
     */
    private function create_partial_coursemodule() {

        global $DB;

        $coursemodule = new stdClass();
        $coursemodule->visible = 1;
        $id = $DB->insert_record('course_modules', $coursemodule);
        $coursemodule->id = $id;

        return $coursemodule;
    }

    /**
     * Fonction qui insère seulement quelques champs dans la table context
     *
     * @param   int         $cmid       Course module's ID
     * @return  \context    $context    The context just created
     */
    private function create_partial_context($cmid) {

        global $DB;

        $context = new stdClass();
        $context->contextlevel = CONTEXT_MODULE;
        $context->instanceid = $cmid;
        $id = $DB->insert_record('context', $context);
        $context->id = $id;

        return $context;
    }

    /**
     * Fonction qui insère seulement quelques champs dans la table plagiarism_compilatio_files
     *
     * @param   int         $cmid               Course module's ID
     * @param   int         $userid             User's ID
     * @return  \stdClass   $plagiarismfile     The plagiarism file just created
     */
    private function create_partial_plagiarismfile($cmid, $userid) {

        global $DB;

        $plagiarismfile = new stdClass();
        $plagiarismfile->cm = $cmid;
        $plagiarismfile->userid = $userid;
        $plagiarismfile->externalid = rand(0, 100);
        $id = $DB->insert_record('plagiarism_compilatio_files', $plagiarismfile);
        $plagiarismfile->id = $id;

        return $plagiarismfile;
    }

    /**
     * Fonction qui insère seulement quelques champs dans la table config_plugins
     * Autrement, il y a une erreur au moment de faire les calls API à la suppression
     *
     * @param   string  $owner  Declare who owns the file (0 -> the student, 1 -> the school)
     */
    private function create_partial_webservice($owner = '0') {

        global $DB;

        $ownerfile = (object) array('plugin' => 'plagiarism_compilatio', 'name' => 'owner_file', 'value' => $owner);
        $apiconfig = (object) array('plugin' => 'plagiarism_compilatio', 'name' => 'apikey', 'value' => "abcdef");
        $DB->insert_records('config_plugins', array($ownerfile, $apiconfig));
    }
}

/*

    Commande pour exécuter tous les tests unitaires :
        vendor/bin/phpunit plagiarism/compilatio/tests/privacy/provider_test.php

    Commande pour exécuter un seul test unitaire :
        vendor/bin/phpunit --filter test_get_metadata  plagiarism/compilatio/tests/privacy/provider_test.php

*/

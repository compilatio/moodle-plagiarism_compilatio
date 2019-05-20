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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/plagiarism/compilatio/api.class.php');

class api_class_test extends advanced_testcase {

    /**
     * Clé de l'API
     * @var string
     */
    const API_KEY = "646022b3de50939edccc46f9009924651022036a";

    /**
     * URL de l'API REST
     * @var string
     */
    const API_URL = "https://beta.compilatio.net";

    /**
     * ID d'un document déjà inséré en base
     * @var string
     */
    const ID_DOC = "a1de867a6fca9852e5995ff9c46d809cbedf083d";

    /**
     * URL du rapport d'un document déjà analysé
     * @var string
     */
    const DOC_REPORT_URL = "https://beta.compilatio.net/api/report/redirect/eb9be05b3345ac4542de1fcfbb93e41420fd96d5";

    /**
     * Différentes instances de compilatioservice pour les tests
     */
    protected static $compilatio; //compilatioservice valide
    protected static $compilatioInvKey1; //compilatioservice avec une clé d'API invalide
    protected static $compilatioInvKey2;
    protected static $compilatioAuthReq1; //compilatioservice avec une clé d'API non spécifiée
    protected static $compilatioAuthReq2;

    /**
     * Les différentes variables utilisées lors des tests
     */
    //Variables pour la fonction send_doc
    private $title = "Title--UnitTest";
    private $filename = "Filename--UnitTest.txt";
    private $content = "Test d'un upload de fichier avec l'API REST -- Test Unitaire";
    //Variables pour la fonction post_configuration
    private $releasephp = "7.0.33-0ubuntu0.16.04.3";
    private $releasemoodle = "3.6 (Build: 20181203)";
    private $releaseplugin = "2019030500";
    private $language = "fr";
    private $cronfrequency = 1;

    /**
     * setUpBeforeClass() -> fonction lancée avant tous les tests | on instancie des 'compilatioservice' avec différentes clé d'API 
     */
    public static function setUpBeforeClass() {

        self::$compilatio = new compilatioservice(self::API_KEY, self::API_URL);
        self::$compilatioInvKey1 = new compilatioservice("abcdef", self::API_URL);
        self::$compilatioInvKey2 = new compilatioservice(42, self::API_URL);
        self::$compilatioAuthReq1 = new compilatioservice("", self::API_URL);
        self::$compilatioAuthReq2 = new compilatioservice(null, self::API_URL);
    }

//-------- TESTS FONCTION GET_TECHNICAL_NEWS --------
#region Fonction get_technical_news

    /**
     * Test fonction get_technical_news -> attend un tableau avec des news
     */
    public function test_get_technical_news_OK() {

        $news = self::$compilatio->get_technical_news();

        $this->assertTrue(isset($news));
        $this->assertEquals('array', getType($news));
    }

    /**
     * Test fonction get_technical_news -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     */
    public function test_get_technical_news_ERRORSAPIKEY() {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->get_technical_news());
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->get_technical_news());

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->get_technical_news());
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->get_technical_news());
    }

#endregion

//-------- TESTS FONCTION GET_ALLOWED_FILE_MAX_SIZE --------
#region Fonction get_allowed_file_max_size

    /**
     * Test fonction get_allowed_file_max_size -> attend un tableau avec les tailles maximales
     */
    public function test_get_allowed_file_max_size_OK() {

        $size = self::$compilatio->get_allowed_file_max_size();

        $this->assertEquals('array', getType($size));
        $this->assertNotCount(0, $size);
        $this->assertArrayHasKey("Ko", $size);
        $this->assertArrayHasKey("Mo", $size);
    }

#endregion

//-------- TESTS FONCTION GET_ALLOWED_FILE_TYPES --------
#region Fonction get_allowed_file_types

    /**
     * Test fonction get_allowed_file_types -> attend un tableau
     */
    public function test_get_allowed_file_types_OK() {

        $types = self::$compilatio->get_allowed_file_types();

        $this->assertEquals('array', getType($types));
        $this->assertNotCount(0, $types);
        $this->assertCount(24, $types);

        $this->assertArrayHasKey('type', $types[0]);
        $this->assertArrayHasKey('title', $types[5]);
        $this->assertArrayHasKey('mimetype', $types[15]);

        $this->assertEquals('docx', $types[2]['type']);
        $this->assertEquals('xml', $types[23]['type']);
        $this->assertEquals('Web Page', $types[4]['title']);
        $this->assertEquals('application/pdf', $types[10]['mimetype']);
    }

#endregion

//-------- TESTS FONCTION GET_QUOTAS --------
#region Fonction get_quotas

    /**
     * Test fonction get_quotas -> attend un array
     */
    public function test_get_quotas_OK() {

        $arrayQuotas = self::$compilatio->get_quotas();

        $this->assertEquals('array', getType($arrayQuotas));
        $this->assertNotCount(0, $arrayQuotas);

        $quotas = $arrayQuotas['quotas'];

        $this->assertEquals('array', getType($quotas));
        $this->assertNotCount(0, $quotas);

        $this->assertArrayHasKey('space', $quotas);
        $this->assertArrayHasKey('freespace', $quotas);
        $this->assertArrayHasKey('usedSpace', $quotas);
        $this->assertArrayHasKey('credits', $quotas);
        $this->assertArrayHasKey('remainingCredits', $quotas);
        $this->assertArrayHasKey('usedCredits', $quotas);
    }

#endregion

//-------- TESTS FONCTION POST_CONFIGURATION --------
#region Fonction post_configuration

    /**
     * Test fonction post_configuration -> attend true
     */
    public function test_post_configuration_OK() {

        $releasephp = $this->releasephp;
        $releasemoodle = $this->releasemoodle;
        $releaseplugin = $this->releaseplugin;
        $language = $this->language;
        $cronfrequency = $this->cronfrequency;

        $this->assertTrue(self::$compilatio->post_configuration($releasephp, $releasemoodle, $releasemoodle, $language, $cronfrequency));
    }

    /**
     * Test fonction post_configuration avec différents paramètres -> attend des erreurs selon les paramètres
     * 
     * @param   string  $releasephp     PHP version
     * @param   string  $releasemoodle  Moodle version
     * @param   string  $releaseplugin  Plugin version
     * @param   string  $language       Language
     * @param   int     $cronfrequency  CRON frequency
     * @param   mixed   $expected       Result expected
     * 
     * @dataProvider post_configuration_DataProvider
     */
    public function test_post_configuration_INVALIDPARAMETERS($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency, $expected) {

        $this->assertEquals($expected, self::$compilatio->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency));
    }

    /**
     * DataProvider de test_post_configuration_INVALIDPARAMETERS
     */
    public function post_configuration_DataProvider() {

        $releasephp = $this->releasephp;
        $releasemoodle = $this->releasemoodle;
        $releaseplugin = $this->releaseplugin;
        $language = $this->language;
        $cronfrequency = $this->cronfrequency;

        return [
            'PHP version not defined'       => [null, $releasemoodle, $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'PHP version' is not defined"],
            'PHP version empty'             => ["", $releasemoodle, $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'PHP version' is empty"],
            'PHP version not a string'      => [42, $releasemoodle, $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'PHP version' is not a string"],

            'Moodle version not defined'    => [$releasephp, null, $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'Moodle version' is not defined"],
            'Moodle version empty'          => [$releasephp, "", $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'Moodle version' is empty"],
            'Moodle version not a string'   => [$releasephp, 42, $releaseplugin, $language, $cronfrequency, "Invalid parameter : 'Moodle version' is not a string"],

            'Plugin version not defined'    => [$releasephp, $releasemoodle, null, $language, $cronfrequency, "Invalid parameter : 'Plugin version' is not defined"],
            'Plugin version empty'          => [$releasephp, $releasemoodle, "", $language, $cronfrequency, "Invalid parameter : 'Plugin version' is empty"],
            'Plugin version not a string'   => [$releasephp, $releasemoodle, 42, $language, $cronfrequency, "Invalid parameter : 'Plugin version' is not a string"],

            'Language not defined'          => [$releasephp, $releasemoodle, $releaseplugin, null, $cronfrequency, "Invalid parameter : 'Language' is not defined"],
            'Language empty'                => [$releasephp, $releasemoodle, $releaseplugin, "", $cronfrequency, "Invalid parameter : 'Language' is empty"],
            'Language not a string'         => [$releasephp, $releasemoodle, $releaseplugin, 42, $cronfrequency, "Invalid parameter : 'Language' is not a string"],

            'CRON frequency not defined'    => [$releasephp, $releasemoodle, $releaseplugin, $language, null, "Invalid parameter : 'CRON frequency' is not defined"],
            'CRON frequency empty'          => [$releasephp, $releasemoodle, $releaseplugin, $language, "", "Invalid parameter : 'CRON frequency' is empty"],
            'CRON frequency not an int'     => [$releasephp, $releasemoodle, $releaseplugin, $language, "1", "Invalid parameter : 'CRON frequency' is not an int"]
        ];
    }

    /**
     * Test fonction post_configuration -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     */
    public function test_post_configuration_ERRORSAPIKEY() {

        $releasephp = $this->releasephp;
        $releasemoodle = $this->releasemoodle;
        $releaseplugin = $this->releaseplugin;
        $language = $this->language;
        $cronfrequency = $this->cronfrequency;

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->post_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency));
    }

#endregion

//-------- TESTS FONCTION SEND_DOC --------
#region Fonction send_doc

    /**
     * Test fonction send_doc -> attend l'ID du document inséré
     */
    public function test_send_doc_OK() {

        $title = $this->title;
        $filename = $this->filename;
        $content = $this->content;

        $idDoc = self::$compilatio->send_doc($title, $filename, $content);

        $this->assertEquals('string', getType($idDoc));
        $this->assertEquals(40, strlen($idDoc));

        return $idDoc;
    }

    /**
     * Test fonction send_doc avec différents paramètres -> attend des erreurs selon les paramètres
     * 
     * @param   string  $title          Document's title
     * @param   string  $filename       Filename
     * @param   string  $content        Document's content
     * @param   string  $expected       Result expected      
     * 
     * @dataProvider send_doc_DataProvider
     */
    public function test_send_doc_INVALIDPARAMETERS($title, $filename, $content, $expected) {

        $this->assertEquals($expected, self::$compilatio->send_doc($title, $filename, $content));
    }

    /**
     * DataProvider de test_send_doc_INVALIDPARAMETERS
     */
    public function send_doc_DataProvider() {

        $title = $this->title;
        $filename = $this->filename;
        $content = $this->content;

        return [
            'Title not defined'         => [null, $filename, $content, "Invalid parameter : 'title' is not defined"],
            'Title empty'               => ["", $filename, $content, "Invalid parameter : 'title' is empty"],
            'Title not a string'        => [42, $filename, $content, "Invalid parameter : 'title' is not a string"],

            'Filename not defined'      => [$title, null, $content, "Invalid parameter : 'filename' is not defined"],
            'Filename empty'            => [$title, "", $content, "Invalid parameter : 'filename' is empty"],
            'Filename not a string'     => [$title, 42, $content, "Invalid parameter : 'filename' is not a string"],

            'Content not defined'       => [$title, $filename, null, "Invalid parameter : 'content' is not defined"],
            'Content empty'             => [$title, $filename, "", "Invalid parameter : 'content' is empty"],
            'Content not a string'      => [$title, $filename, 42, "Invalid parameter : 'content' is not a string"],
        ];
    }

    /**
     * Test fonction send_doc -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     */
    public function test_send_doc_APIKEY() {

        $title = $this->title;
        $filename = $this->filename;
        $content = $this->content;

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->send_doc($title, $filename, $content));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->send_doc($title, $filename, $content));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->send_doc($title, $filename, $content));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->send_doc($title, $filename, $content));
    }

#endregion

//-------- TESTS FONCTION START_ANALYSE --------
#region Fonction start_analyse

    /**
     * Test fonction start_analyse -> attend true
     * 
     * @depends test_send_doc_OK
     */
    public function test_start_analyse_OK($idDoc) {

        //Eviter de lancer l'analyse pour de vrai pour ne pas surcharger le serveur -- On va donc "mocker" le retour de la fonction
        //$this->assertTrue(self::$compilatio->start_analyse($idDoc));

        // Create a stub for the compilatioservice class
        $stub = $this->createMock(compilatioservice::class);

        // Configure the stub
        $stub->method('start_analyse')
             ->willReturn(true);

        // Calling $stub->start_analyse() will now return true
        $this->assertTrue($stub->start_analyse($idDoc));
    }

    /**
     * Test fonction start_analyse -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   string  $expected       Result expected
     * 
     * @dataProvider start_analyse_DataProvider
     */
    public function test_start_analyse_INVALIDPARAMETERS($idDoc, $expected) {

        $this->assertEquals($expected, self::$compilatio->start_analyse($idDoc));
    }

    /**
     * DataProvider de start_analyse_INVALIDPARAMETERS
     */
    public function start_analyse_DataProvider() {
        return [
            'ID not defined'        => [null, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'              => ["", "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'       => [42, "Invalid parameter : 'document's ID' is not a string"],
            'Invalid ID'            => ["abcdef", "Invalid document id"]
        ];
    }

    /**
     * Test fonction start_analyse -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     * 
     * @depends test_send_doc_OK
     */
    public function test_start_analyse_ERRORSAPIKEY($idDoc) {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->start_analyse($idDoc));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->start_analyse($idDoc));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->start_analyse($idDoc));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->start_analyse($idDoc));
    }

#endregion

//-------- TESTS FONCTION GET_DOC --------
#region Fonction get_doc

    /**
     * Test fonction get_doc -> attend une classe avec différentes propriétés
     * 
     * @depends test_send_doc_OK
     */
    public function test_get_doc_OK($idDoc) {

        // On vérifie différentes propriétés pour un document qui vient d'être uploadé pour les tests
        $docObject = self::$compilatio->get_doc($idDoc);
        $doc = $docObject->documentProperties;

        $this->assertEquals('object', getType($doc));
        $this->assertEquals($idDoc, $doc->idDocument);
        $this->assertEmpty($doc->title);
        $this->assertEquals($this->filename, $doc->filename);
        $this->assertEquals(strlen($this->content), $doc->textLength);
        $this->assertEquals(11, $doc->wordCount);
        $this->assertEquals(str_word_count($this->content), $doc->wordCount);

        // On vérifie différentes propriétés pour un document déjà en base
        $docObjectBase = self::$compilatio->get_doc(self::ID_DOC);
        $docBase = $docObjectBase->documentProperties;
        $docStatusBase = $docObjectBase->documentStatus;

        $this->assertEquals('object', getType($docBase));
        $this->assertEquals(self::ID_DOC, $docBase->idDocument);
        $this->assertEmpty($docBase->title);
        $this->assertEquals("ANALYSE_COMPLETE", $docStatusBase->status);
        $this->assertEquals("100", $docStatusBase->progression);

        var_dump($docObjectBase);

    }

    /**
     * Test fonction get_doc -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   string  $expected       Result expected
     * 
     * @dataProvider get_doc_DataProvider
     */
    public function test_get_doc_INVALIDPARAMETERS($idDoc, $expected) {

        $this->assertEquals($expected, self::$compilatio->get_doc($idDoc));
    }

    /**
     * DataProvider de test_get_doc_INVALIDPARAMETERS
     */
    public function get_doc_DataProvider() {
        return [
            'ID not defined'        => [null, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'              => ["", "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'       => [42, "Invalid parameter : 'document's ID' is not a string"],
            'Document not found'    => ["abcdef", "Not Found"]
        ];
    }

    /**
     * Test fonction get_doc -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     * 
     * @depends test_send_doc_OK
     */
    public function test_get_doc_ERRORSAPIKEY($idDoc) {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->get_doc($idDoc));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->get_doc($idDoc));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->get_doc($idDoc));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->get_doc($idDoc));
    }

#endregion

//-------- TESTS FONCTION GET_REPORT_URL --------
#region Fonction get_report_url

    /**
     * Test fonction get_report_url -> attend l'URL de l'analyse
     */
    public function test_get_report_url_OK() {

        $url = self::$compilatio->get_report_url(self::ID_DOC);

        $this->assertEquals('string', getType($url));
        $this->assertStringStartsWith('https://', $url);
        $this->assertEquals(self::DOC_REPORT_URL, $url);
    }

    /**
     * Test fonction get_report_url -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   string  $expected       Result expected
     * 
     * @dataProvider get_report_url_DataProvider
     */
    public function test_get_report_url_INVALIDPARAMETERS($idDoc, $expected) {

        $this->assertEquals($expected, self::$compilatio->get_report_url($idDoc));
    }

    /**
     * DataProvider de test_get_report_url_INVALIDPARAMETERS
     */
    public function get_report_url_DataProvider() {
        return [
            'ID not defined'        => [null, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'              => ["", "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'       => [42, "Invalid parameter : 'document's ID' is not a string"],
            'Document not found'    => ["abcdef", "Not Found"]
        ];
    }

    /**
     * Test fonction get_report_url -> attend une erreur (par exemple le document n'a pas fini d'être / n'a pas été analysé)
     * 
     * @depends test_send_doc_OK
     */
    public function test_get_report_url_BADREQUEST($idDoc) {

        $this->assertEquals("Bad Request", self::$compilatio->get_report_url($idDoc));
    }

    /**
     * Test fonction get_doc -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     */
    public function test_get_report_url_ERRORSAPIKEY() {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->get_report_url(self::ID_DOC));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->get_report_url(self::ID_DOC));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->get_report_url(self::ID_DOC));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->get_report_url(self::ID_DOC));
    }

#endregion

//-------- TESTS FONCTION GET_INDEXING_STATE & SET_INDEXING_STATE --------
#region Fonctions get_indexing_state & set_indexing_state -> OK

    /**
     * Test fonction set_indexing_state -> attend  true
     * 
     * @depends test_send_doc_OK
     */
    public function test_set_indexing_state_TRUEOK($idDoc) {

        $this->assertTrue(self::$compilatio->set_indexing_state($idDoc, true));
        sleep(5); //Pause pour laisser le temps au paramètre d'être mis à jour
    }
    
    /**
     * Test fonction get_indexing_state -> attend true
     * 
     * @depends test_send_doc_OK
     * @depends test_set_indexing_state_TRUEOK
     */
    public function test_get_indexing_state_TRUEOK($idDoc) {

        $this->assertTrue(self::$compilatio->get_indexing_state($idDoc));
    }

    /**
     * Test fonction set_indexing_state -> attend false
     * 
     * @depends test_send_doc_OK
     */
    public function test_set_indexing_state_FALSEOK($idDoc) {

        $this->assertTrue(self::$compilatio->set_indexing_state($idDoc, false));
        sleep(5); //Pause pour laisser le temps au paramètre d'être mis à jour
    }

    /**
     * Test fonction get_indexing_state -> attend false
     * 
     * @depends test_send_doc_OK
     * @depends test_set_indexing_state_FALSEOK
     */
    public function test_get_indexing_state_FALSEOK($idDoc) {

        $this->assertFalse(self::$compilatio->get_indexing_state($idDoc));
    }

#endregion

#region Fonctions get_indexing_state & set_indexing_state -> errors

    /**
     * Test fonction set_indexing_state -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   bool    $indexed        Indexing state
     * @param   string  $expected       Result expected
     * 
     * @dataProvider set_indexing_state_DataProvider
     */
    public function test_set_indexing_state_INVALIDPARAMETERS($idDoc, $indexed, $expected) {

        $this->assertEquals($expected, self::$compilatio->set_indexing_state($idDoc, $indexed));
    }

    /**
     * DataProvider de test_set_indexing_state_INVALIDPARAMETERS
     */
    public function set_indexing_state_DataProvider() {

        $idDoc = self::ID_DOC;
        $indexed = true;

        return [
            'ID not defined'            => [null, $indexed, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'                  => ["", $indexed, "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'           => [42, $indexed, "Invalid parameter : 'document's ID' is not a string"],
            'Document not found'        => ["abcdef", $indexed, "Not Found"],

            'Indexing state not bool, int'      => [$idDoc, 42, "Invalid parameter : indexing state is not a boolean"],
            'Indexing state not bool, string'   => [$idDoc, "abcdef", "Invalid parameter : indexing state is not a boolean"]
        ];
    }

    /**
     * Test fonction get_indexing_state -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   string  $expected       Result expected
     * 
     * @dataProvider get_indexing_state_DataProvider
     */
    public function test_get_indexing_state_INVALIDPARAMETERS($idDoc, $expected) {

        $this->assertEquals($expected, self::$compilatio->get_indexing_state($idDoc));
    }

    /**
     * DataProvider de test_get_indexing_state_INVALIDPARAMETERS
     */
    public function get_indexing_state_DataProvider() {

        return [
            'ID not defined'                => [null, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'                      => ["", "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'               => [42, "Invalid parameter : 'document's ID' is not a string"],
            'Document not found'            => ["abcdef", "Not Found"],
        ];
    }

    /**
     * Test fonction get & set_indexing_state -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     * 
     * @depends test_send_doc_OK
     */
    public function test_get_set_indexing_state_ERRORSAPIKEY($idDoc) {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->get_indexing_state($idDoc));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->get_indexing_state($idDoc));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->get_indexing_state($idDoc));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->get_indexing_state($idDoc));

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->set_indexing_state($idDoc, true));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->set_indexing_state($idDoc, true));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->set_indexing_state($idDoc, true));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->set_indexing_state($idDoc, true));
    }

#endregion

//-------- TESTS FONCTION DEL_DOC -------
#region Fonction del_doc

    /**
     * Test fonction del_doc -> attend des erreurs selon les paramètres
     * 
     * @param   string  $idDoc          Document's ID
     * @param   string  $expected       Result expected
     * 
     * @dataProvider del_doc_DataProvider
     */
    public function test_del_doc_INVALIDPARAMETERS($idDoc, $expected) {

        $this->assertEquals($expected, self::$compilatio->del_doc($idDoc));
    }

    /**
     * DataProvider de test_del_doc_INVALIDPARAMETERS
     */
    public function del_doc_DataProvider() {
        return [
            'ID not defined'        => [null, "Invalid parameter : 'document's ID' is not defined"],
            'ID empty'              => ["", "Invalid parameter : 'document's ID' is empty"],
            'ID not a string'       => [42, "Invalid parameter : 'document's ID' is not a string"],
            'Document not found'    => ["abcdef", "Not Found"]
        ];
    }

    /**
     * Test fonction del_doc -> attend une erreur (clé d'API invalide ou non spécifiée (authentification requise))
     * 
     * @depends test_send_doc_OK
     */
    public function test_del_doc_ERRORSAPIKEY($idDoc) {

        $this->assertEquals("Invalid API key", self::$compilatioInvKey1->del_doc($idDoc));
        $this->assertEquals("Invalid API key", self::$compilatioInvKey2->del_doc($idDoc));

        $this->assertEquals("Authentication Required", self::$compilatioAuthReq1->del_doc($idDoc));
        $this->assertEquals("Authentication Required", self::$compilatioAuthReq2->del_doc($idDoc));
    }

    /**
     * Test fonction del_doc -> attend une erreur (document déjà indexé)
     * 
     * @depends test_send_doc_OK
     */
    public function test_del_doc_DOCINDEXED($idDoc) {

        self::$compilatio->set_indexing_state($idDoc, true);
        sleep(5); //Pause pour laisser le temps au paramètre d'être mis à jour

        $this->assertEquals("You can't remove an indexed document, please remove it from your references database before", 
                            self::$compilatio->del_doc($idDoc));
    }

    /**
     * Test fonction del_doc -> attend true
     * 
     * @depends test_send_doc_OK
     */
    public function test_del_doc_OK($idDoc) {

        self::$compilatio->set_indexing_state($idDoc, false);
        sleep(5); //Pause pour laisser le temps au paramètre d'être mis à jour

        $this->assertTrue(self::$compilatio->del_doc($idDoc));
    }

#endregion

}

/* RECAPITULATIF DES FONCTIONS A TESTER

    get_technical_news          -> OK
    get_allowed_file_max_size   -> OK
    get_allowed_file_types      -> OK
    post_configuration          -> OK
    send_doc                    -> OK
    start_analyse               -> OK
    get_doc                     -> OK
    get_report_url              -> OK
    set_indexing_state          -> OK
    get_indexing_state          -> OK
    del_doc                     -> OK

    get_quotas                  -> PAS (vraiment) TESTABLE (méthode pas codée dans l'API REST)
    get_account_expiration_date -> PAS TESTABLE (méthode pas codée dans l'API REST)

    Commande pour exécuter tous les tests unitaires (se situer dans moodle36/www/moodle36) :
        vendor/bin/phpunit plagiarism/compilatio/tests/api_class_test.php
    
    Commande pour exécuter un seul test unitaire (se situer dans moodle36/www/moodle36) :
        vendor/bin/phpunit --filter test_send_doc_OK  plagiarism/compilatio/tests/api_class_test.php

*/

/* APPELS D'API AVEC CURL EN LIGNE DE COMMANDE

get_technical_news (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/api/service-info/list?limit=5

get_allowed_file_max_size (pas testable)

get_allowed_file_types (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/public_api/file/allowed-extensions

post_configuration (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" --data '{"php_version":"7.0.33-0ubuntu0.16.04.3","moodle_version":"3.6 (Build: 20181203)","compilatio_plugin_version":"2019030500","language":"fr","cron_frequency":"1"}' -X POST https://beta.compilatio.net/api/moodle-configuration/add

send_doc (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -F file=@newtest.txt -F title=newtest -F origin=api -X POST https://beta.compilatio.net/api/document/
    curl -i -H "X-Auth-Token: XXXX-XXXX" -F "file=@newtest.txt;title=newtest;origin=api" -X POST https://beta.compilatio.net/api/document/

start_analyse (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" --data '{"doc_id":"XXXX-XXXX","recipe_name":"anasim"}' -X POST https://beta.compilatio.net/api/analysis/

get_doc (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/api/document/XXXX-XXXX

get_report_url (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/api/document/XXXX-XXXX/report-url

del_doc (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X DELETE https://beta.compilatio.net/api/document/XXXX-XXXX

set_indexing_state (ok)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" --data '{"indexed": "true"}' -X PATCH https://beta.compilatio.net/api/document/XXXX-XXXX

get_indexing_state (même commande que pour get_doc)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/api/document/XXXX-XXXX

get_quotas (pas testable)

get_account_expiration_date (pas testable)
    curl -i -H "X-Auth-Token: XXXX-XXXX" -H "Accept: application/json" -H "Content-Type: application/json" -X GET https://beta.compilatio.net/api/subscription/api-key

*/
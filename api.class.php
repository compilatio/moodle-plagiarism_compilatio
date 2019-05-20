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
 * ws_helper.php - Contains Plagiarism plugin helper methods for communicate with the web service.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * compilatioservice class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatioservice {

    /**
     * Clé d'identification pour le compte Compilatio
     * @var string
     */
    public $key;

    /**
     * URL du webservice REST
     * @var string
     */
    public $urlrest;

    /**
     * Constructor : Create the connexion with the webservice
     * MODIF 2009-03-19: passage des paramètres
     * MODIF 2017-06-23: MAJ PHP 7
     * MODIF 2019-04-08: passage API SOAP à API REST
     *
     * @param   string  $key        API key
     * @param   string  $urlrest    URL of the REST webservice
     */
    public function __construct($key, $urlrest) {

        $this->key = null;
        $this->urlrest = $urlrest;

        if (!empty($key)) {
            $this->key = $key;
        }
        else {
            return "API key not available";
        }
    }

    /* API Functions -------------------------------------------------------------*/

    /**
     * Load document on Compilation account
     *
     * @param   string  $title          Document's title
     * @param   string  $filename       Filename
     * @param   string  $content        Document's content
     * @return  string                  Return the document's ID, an error message otherwise
     */
    public function send_doc($title, $filename, $content) {

        $valid_title = $this->validateStringParameter($title, "title");
        if($valid_title != "Valid string") {
            return $valid_title;
        }

        $valid_filename = $this->validateStringParameter($filename, "filename");
        if($valid_filename != "Valid string") {
            return $valid_filename;
        }

        $valid_content = $this->validateStringParameter($content, "content");
        if($valid_content != "Valid string") {
            return $valid_content;
        }

        $handle = fopen('/tmp/' . date('Y-m-d H:i:s') . ".txt", 'w+');
        fwrite($handle, $content);

        $endpoint = "/api/document/";
        $params = array(
            'file' => new CurlFile(realpath(stream_get_meta_data($handle)['uri'])),
            'filename' => $filename,
            'title' => $title
        );

        $response = json_decode($this->curlPOSTUpload($endpoint, $params));

        if ($response->status->code == 201) {
            return $response->data->document->id;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Get back information about a document
     *
     * @param  string   $compihash  External ID of the document
     * @return mixed               Return the document in an object if succeed, an error message otherwise
     */
    public function get_doc($compihash) {

        $valid_compihash = $this->validateStringParameter($compihash, "document's ID");
        if($valid_compihash != "Valid string") {
            return $valid_compihash;
        }

        $endpoint = "/api/document/".$compihash;
        $response = json_decode($this->curlGET($endpoint));

        if ($response->status->code == 200) {

            $document = $response->data->document;

            $documentProperties = new \stdClass();
            $documentProperties->idDocument = $document->id;
            $documentProperties->title = "";
            $documentProperties->description = "";
            $documentProperties->filename = $document->filename;
            $documentProperties->filetype = explode('.', $document->filename)[1];
            $documentProperties->date = $document->upload_date;
            $documentProperties->textBeginning = '';
            $documentProperties->textLength = $document->length;
            $documentProperties->filesize = 0;
            $documentProperties->idFolder = "";
            $documentProperties->parts = 0;
            $documentProperties->Shortcut = "";
            $documentProperties->idParent = $document->id;
            $documentProperties->wordCount = $document->words_count;
            $documentStatus = new \stdClass();
            if(isset($document->analyses)) {
                $analysisBSON = (array) $document->analyses;
            }
            else {
                $document->analyses = array();
            }

            // status : ANALYSE_NOT_STARTED
            if (!isset($analysisBSON['anasim'])) {
                $documentStatus->cost = "1";
                $documentStatus->status = "ANALYSE_NOT_STARTED";
                $documentStatus->indice = "";
                $documentStatus->progression = "";
                $documentStatus->startDate = "";
                $documentStatus->finishDate = "";
            } else {
                $analysis = $analysisBSON['anasim'];

                if ($analysis->state === 'waiting') {
                    // status : ANALYSE_IN_QUEUE --> waiting
                    $documentStatus->cost = "1";
                    $documentStatus->status = "ANALYSE_IN_QUEUE";
                    $documentStatus->indice = "";
                    $documentStatus->progression = "";
                    $documentStatus->startDate = "";
                    $documentStatus->finishDate = "";
                } else if ($analysis->state === 'running' || $analysis->state === 'degraded') {
                    // status : ANALYSE_PROCESSING --> running
                    $documentStatus->cost = "1";
                    $documentStatus->status = "ANALYSE_PROCESSING";
                    $documentStatus->indice = "";
                    $documentStatus->progression = "";
                    $documentStatus->startDate = $analysis->metrics->start;
                    $documentStatus->finishDate = "";
                } else if ($analysis->state === 'crashed' || $analysis->state === 'aborted' || $analysis->state === 'canceled') {
                    // status : ANALYSE_CRASHED --> stopped
                    $documentStatus->cost = "0";
                    $documentStatus->status = "ANALYSE_CRASHED";
                    $documentStatus->indice = "";
                    $documentStatus->progression = "";
                    $documentStatus->startDate = $analysis->metrics->start;
                    $documentStatus->finishDate = "";
                } else if ($analysis->state === 'finished') {
                    // status : ANALYSE_COMPLETE --> finished
                    $documentStatus->cost = "1";
                    $documentStatus->status = "ANALYSE_COMPLETE";
                    $reportBSON = (array) $document->light_reports;
                    $lightReports = $reportBSON['anasim'];
                    $documentStatus->indice = (string) $lightReports->plagiarism_percent;
                    $documentStatus->progression = "100";
                    $documentStatus->startDate = $analysis->metrics->start;
                    $documentStatus->finishDate = $analysis->metrics->end;
                }
            }
            $compilatioDocument = new \stdClass();
            $compilatioDocument->documentProperties = $documentProperties;
            $compilatioDocument->documentStatus = $documentStatus;

            return $compilatioDocument;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Get back the URL of a report document
     *
     * @param  string $compihash External ID of the document
     * @return string            Return the URL if succeed, an error message otherwise
     */
    public function get_report_url($compihash) {

        $valid_compihash = $this->validateStringParameter($compihash, "document's ID");
        if($valid_compihash != "Valid string") {
            return $valid_compihash;
        }

        $endpoint = "/api/document/".$compihash."/report-url";
        $response = json_decode($this->curlGET($endpoint));

        if ($response->status->code == 200) {
            return $response->data->url;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Delete a document on the Compilatio account
     *
     * @param  string   $compihash  External ID of the document
     * @return boolean              Return true if succeed, an error message otherwise
     */
    public function del_doc($compihash) {

        $valid_compihash = $this->validateStringParameter($compihash, "document's ID");
        if($valid_compihash != "Valid string") {
            return $valid_compihash;
        }

        $endpoint = "/api/document/".$compihash;
        $response = json_decode($this->curlDELETE($endpoint));
        
        if ($response->status->code == 200) {
            return true;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Start an analyse of a document
     *
     * @param  string   $compihash  External ID of the document
     * @return mixed                Return true if succeed, an error message otherwise
     */
    public function start_analyse($compihash) {

        $valid_compihash = $this->validateStringParameter($compihash, "document's ID");
        if($valid_compihash != "Valid string") {
            return $valid_compihash;
        }

        $endpoint = "/api/analysis/";
        $params = array(
            'doc_id' => $compihash,
            'recipe_name' => 'anasim'
        );

        $response = json_decode($this->curlPOST($endpoint, json_encode($params)));

        if ($response->status->code == 201) {
            return true;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Get back Compilatio account's quotas
     *
     * @return  array   Informations about quotas
     */
    public function get_quotas() {
        //Méthode pas encore codée dans l'API REST
        /*
        $accountQuotas = new AccountQuotas();
        $accountQuotas->space = 100000000;
        $accountQuotas->freeSpace = 100000000;
        $accountQuotas->usedSpace = 0;
        $accountQuotas->credits = 100000;
        $accountQuotas->remainingCredits = 100000;
        $accountQuotas->usedCredits = 0;
        */

        $accountQuotas = array(
            "quotas" => array(
                "space" => 100000000,
                "freespace" => 100000000,
                "usedSpace" => 0,
                "credits" => 100000,
                "remainingCredits" => 100000,
                "usedCredits" => 0
            )
        );

        return $accountQuotas;
    }

    /**
     * Get expiration date of an account
     *
     * @return mixed Return the expiration date if succeed, false otherwise
     */
    public function get_account_expiration_date() {

        // Cette fonction semble renvoyer une erreur 404... Erreur dans le endpoint ?
        $endpoint = "/api/subscription/api-key";
        $response = json_decode($this->curlGET($endpoint));

        if ($response->status->code == 200)
            return $response->data->subscription->validity_period->end;
        else
            return $response->status->message;
    }

    /**
     * Post Moodle Configuration to Compilatio
     *
     * @param  string   $releasephp     PHP version
     * @param  string   $releasemoodle  Moodle version
     * @param  string   $releaseplugin  Plugin version
     * @param  string   $language       Language
     * @param  int      $cronfrequency  CRON frequency
     * @return mixed                    Return true if succeed, an error message otherwise
     */
    public function post_configuration($releasephp,
                                       $releasemoodle,
                                       $releaseplugin,
                                       $language,
                                       $cronfrequency) {

        $valid_releasephp = $this->validateStringParameter($releasephp, "PHP version");
        if($valid_releasephp != "Valid string") {
            return $valid_releasephp;
        }

        $valid_releasemoodle = $this->validateStringParameter($releasemoodle, "Moodle version");
        if($valid_releasemoodle != "Valid string") {
            return $valid_releasemoodle;
        }

        $valid_releaseplugin = $this->validateStringParameter($releaseplugin, "Plugin version");
        if($valid_releaseplugin != "Valid string") {
            return $valid_releaseplugin;
        }

        $valid_language = $this->validateStringParameter($language, "Language");
        if($valid_language != "Valid string") {
            return $valid_language;
        }

        $valid_cronfrequency = $this->validateIntParameter($cronfrequency, "CRON frequency");
        if($valid_cronfrequency != "Valid int") {
            return $valid_cronfrequency;
        }

        $endpoint = "/api/moodle-configuration/add";
        $params = array(
            'php_version' => $releasephp,
            'moodle_version' => $releasemoodle,
            'compilatio_plugin_version' => $releaseplugin,
            'language' => $language,
            'cron_frequency' => $cronfrequency
        );

        $response = json_decode($this->curlPOST($endpoint, json_encode($params)));
        
        if ($response->status->code == 200) {
            return true;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Get a list of the current Compilatio news.
     *
     * @return array    serviceInfos    Return an array of news, an error message otherwise
     */
    public function get_technical_news() {

        $endpoint = "/api/service-info/list?limit=5";
        $response = json_decode($this->curlGET($endpoint));

        if($response->status->code == 200) {

            $serviceInfos = [];
            $languages = ['fr', 'es', 'en', 'it', 'de'];

            foreach ($response->data->service_infos as $info) {

                $serviceInfo = new \stdClass();
                $serviceInfo->id = $info->id;

                switch ($info->level) {
                    case '1':
                        $serviceInfo->type = 'info';
                        break;
                    case '4':
                        $serviceInfo->type = 'critical';
                        break;
                    default:
                        $serviceInfo->type = 'warning';
                        break;
                }

                foreach ($languages as $language) {
                    $serviceInfo->{'message_' . $language} = $info->message->{$language};
                }

                $serviceInfo->begin_display_on = strtotime($info->metrics->start);
                $serviceInfo->end_display_on = strtotime($info->metrics->end);

                array_push($serviceInfos, $serviceInfo);
            }

            return $serviceInfos;
        }
        else
            return $response->status->message;
    }

    /**
     * Get the maximum size authorized by Compilatio.
     *
     * @return array    Return an array of the max size
     */
    public function get_allowed_file_max_size() {

        //Fonction pas encore codée dans l'API REST -- On renvoie un ce tableau-ci de toutes manières
        $sizeMo = 20;
        $allowedFileMaxSize = [
            'bits' => $sizeMo * 10**6 * 8,
            'octets' => $sizeMo * 10**6,
            'Ko' => $sizeMo * 10**3,
            'Mo' => $sizeMo
        ];

        return $allowedFileMaxSize;
    }

    /**
     * Get a list of the allowed file types by Compilatio.
     *
     * @return  array   Return an array of the different allowed file types
     */
    public function get_allowed_file_types() {

        $endpoint = "/public_api/file/allowed-extensions";
        $response = json_decode($this->curlGET($endpoint), true);

        $extensionNameMapping = [
            "doc" => "Microsoft Word",
            "docx" => "Microsoft Word",
            "xls" => "Microsoft Excel",
            "xlsx" => "Microsoft Excel",
            "ppt" => "Microsoft Powerpoint",
            "pptx" => "Microsoft Powerpoint",

            "xml" => "XML File",
            "xhtml" => "Web Page",
            "htm" => "Web Page",
            "html" => "Web Page",

            "csv" => "Comma Separated Values File",

            "odt" => "OpenDocument Text",
            "ods" => "OpenDocument Sheet",
            "odp" => "OpenDocument Presentation",

            "pdf" => "Adobe Portable Document File",
            "rtf" => "Rich Text File",
            "txt" => "Plain Text File",
            "tex" => "LaTeX source File",
        ];

        $list = [];

        foreach ($response as $extension => $mimeContentTypes) {
            foreach ($mimeContentTypes as $mimeContentType) {
                $filetype = [];
                $filetype['type'] = $extension;
                $filetype['title'] = $extensionNameMapping[$extension];
                $filetype['mimetype'] = $mimeContentType;
                $list[] = $filetype;
            }
        }

        sort($list);

        return $list;
    }

    /**
     * Get back the indexing state of a document
     *
     * @param   string      $compid     Document ID
     * @return  mixed                   Return the indexing state if succeed, an error message otherwise
     */
    public function get_indexing_state($compid) {

        $valid_compid = $this->validateStringParameter($compid, "document's ID");
        if($valid_compid != "Valid string") {
            return $valid_compid;
        }

        $endpoint = "/api/document/".$compid;
        $response = json_decode($this->curlGET($endpoint));

        if ($response->status->code == 200) {
            return $response->data->document->indexed;
        }
        else {
            return $response->status->message;
        }
    }

    /**
     * Set the indexing state of a document
     *
     * @param   string  $compid     Document ID
     * @param   bool    $indexed    Indexing state
     * @return  mixed               Return true if succeed, an error message otherwise
     */
    public function set_indexing_state($compid, $indexed) {

        $valid_compid = $this->validateStringParameter($compid, "document's ID");
        if($valid_compid != "Valid string") {
            return $valid_compid;
        }
        
        $valid_indexes = array("0", "1", "false", "true");
        if(!in_array($indexed, $valid_indexes)) {
            return "Invalid parameter : indexing state is not a boolean";
        }

        $endpoint = "/api/document/".$compid;
        $params = array(
            'indexed' => $indexed
        );
        $response = json_decode($this->curlPATCH($endpoint, json_encode($params)));

        if(!isset($response->status->code, $response->status->message)) {
            return "Error in function set_indexing_state() : request response's status not found";
        }

        if ($response->status->code == 200) {
            return true;
        }
        else {
            return $response->status->message;
        }
    }

#region Fonctions de validation

    /**
     * Verify is the parameter is a valid string (defined, not empty and a string type)
     *
     * @param   mixed   $var    The parameter to verify (usually a string)
     * @param   string  $name   The name of the parameter (to have a nice message error just in case)
     * @return  string          Return a message
     */
    private function validateStringParameter($var, $name) {

        $errorMessage = "Invalid parameter : '".$name."' is ";
        if(!isset($var)) {
            return $errorMessage."not defined";
        }
        elseif(empty($var)) {
            return $errorMessage."empty";
        }
        elseif(!is_string($var)) {
            return $errorMessage."not a string";
        }
        else {
            return "Valid string";
        }
    }

    /**
     * Verify is the parameter is a valid int (defined, not empty and an int type)
     *
     * @param   mixed   $var    The parameter to verify (usually an int)
     * @param   string  $name   The name of the parameter (to have a nice message error just in case)
     * @return  string          Return a message
     */
    private function validateIntParameter($var, $name) {

        $errorMessage = "Invalid parameter : '".$name."' is ";
        if(!isset($var)) {
            return $errorMessage."not defined";
        }
        elseif(empty($var)) {
            return $errorMessage."empty";
        }
        elseif(!is_int($var)) {
            return $errorMessage."not an int";
        }
        else {
            return "Valid int";
        }
    }

#endregion

#region CURL FUNCTIONS

    /**
     * Send a GET request with cURL
     *
     * @param   string  $endpoint   URL of the ressource
     * @param   string  $params     Parameters of the request
     * @return  string              Return the result of the request
     */
    private function curlGET($endpoint, $params="") {

        $url = $this->urlrest.$endpoint."?".$params;
        $token = $this->key;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$token, 'Content-Type: application/json'),
        );

        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Send a POST request with cURL
     *
     * @param   string  $endpoint   URL of the ressource
     * @param   string  $load       Parameters of the request
     * @return  string              Return the result of the request
     */
    private function curlPOST($endpoint, $load) {

        $url = $this->urlrest.$endpoint;
        $token = $this->key;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$token, 'Content-Type: application/json'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $load,
        );

        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Send a POST request with cURL for uploading a document
     *
     * @param   string  $endpoint   URL of the ressource
     * @param   string  $load       Parameters of the request
     * @return  string              Return the result of the request
     */
    private function curlPOSTUpload($endpoint, $load) {

        $url = $this->urlrest.$endpoint;
        $token = $this->key;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$token),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $load,
        );

        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }

    /**
     * Send a PATCH request with cURL
     *
     * @param   string  $endpoint   URL of the ressource
     * @param   string  $load       Parameters of the request
     * @return  string              Return the result of the request
     */
    private function curlPATCH($endpoint, $load) {

        $url = $this->urlrest.$endpoint;
        $token = $this->key;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$token, 'Content-Type: application/json'),
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => $load,
        );

        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Send a DELETE request with cURL
     *
     * @param   string  $endpoint   URL of the ressource
     * @param   string  $params     Parameters of the request
     * @return  string              Return the result of the request
     */
    private function curlDELETE($endpoint, $params="") {
        
        $url = $this->urlrest.$endpoint."?".$params;
        $token = $this->key;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$token, 'Content-Type: application/json'),
        );

        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

#endregion

}
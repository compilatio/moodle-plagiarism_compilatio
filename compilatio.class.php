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
 * @copyright  2020 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * compilatioservice class
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compilatioservice {

    /**
     * Clef d'identification pour le compte Compilatio
     * @var string
     */
    public $key;

    /**
     * Connexion au Webservice
     * @var SoapClient
     */
    public $soapcli;

    /**
     * Identifier of the configuration containing the API key and URL of the SOAP webservice
     * @var int
     */
    public $apiconfigid;

    /**
     * Retourne l'instance unique.
     *
     * @param string $apiconfigid   API configuration Id
     * @param string $proxyhost     Proxy host
     * @param string $proxyport     Proxy port
     * @param string $proxyusername Proxy username
     * @param string $proxypassword Proxy password
     * @return compilatioservice
     */
    public static function getinstance($apiconfigid,
                                        $proxyhost='',
                                        $proxyport='',
                                        $proxyusername='',
                                        $proxypassword='') {
        if (self::$instance === null || self::$instance->apiconfigid != $apiconfigid) {
            self::$instance = new compilatioservice($apiconfigid,
                                                    $proxyhost,
                                                    $proxyport,
                                                    $proxyusername,
                                                    $proxypassword);
        }
        return self::$instance;
    }

    /**
     * Instance unique compilatioservice
     * @var compilatioservice
     */
    private static $instance;

    /**
     * Constructor : Create the connexion with the webservice
     * MODIF 2009-03-19: passage des paramètres
     * MODIF 2017-06-23: MAJ PHP 7
     *
     * @param string $apiconfigid   API configuration Id
     * @param string $proxyhost     Proxy host
     * @param string $proxyport     Proxy port
     * @param string $proxyusername Proxy username
     * @param string $proxypassword Proxy password
     */
    private function __construct($apiconfigid,
                                $proxyhost='',
                                $proxyport='',
                                $proxyusername='',
                                $proxypassword='') {

        global $DB;

        $this->key = null;
        $this->apiconfigid = null;

        try {
            $apiconfig = $DB->get_record('plagiarism_compilatio_apicon', array('id' => $apiconfigid));

            if ($apiconfig) {
                $this->apiconfigid = $apiconfigid;
                $key = $apiconfig->api_key;
                $urlsoap = $apiconfig->url;
                ini_set("soap.wsdl_cache_enabled", 0);
                if (!empty($key)) {
                    $this->key = $key;
                    if (!empty($urlsoap)) {
                        $param = array(
                            'trace' => false,
                            'soap_version' => SOAP_1_2,
                            'exceptions' => true
                        );
                        if (!empty($proxyhost)) {
                            $param['proxy_host'] = $proxyhost;
                            if (!empty($proxyport)) {
                                $param['proxy_port'] = $proxyport;
                            }
                            if (!empty($proxyusername) && !empty($proxypassword)) {
                                $param['proxy_login'] = $proxyusername;
                                $param['proxy_password'] = $proxypassword;
                            }
                        }
                        $this->soapcli = new SoapClient($urlsoap, $param);
                    } else {
                        $this->soapcli = 'WS urlsoap not available';
                    }
                } else {
                    $this->soapcli = 'API key not available';
                }
            } else {
                $this->soapcli = 'API config not available';
            }
        } catch (SoapFault $fault) {
            $this->soapcli = "Error constructor compilatio " . $fault->faultcode . " " .$fault->faultstring;
        } catch (Exception $e) {
            $this->soapcli = "Error constructor compilatio with urlsoap" . $urlsoap;
        }
    }

    /**
     * Load a document on Compilatio account
     *
     * @param  string $title       Title
     * @param  string $description Description
     * @param  string $filename    Filename
     * @param  string $mimetype    MIME type
     * @param  string $content     Content
     * @return string              Return the document ID if succeed, an error otherwise
     */
    public function send_doc($title, $description, $filename, $mimetype, $content) {

        try {

            if (!is_object($this->soapcli)) {
                return "Error in constructor compilatio() " . $this->soapcli;
            }

            $iddocument = $this->soapcli->__call(
                'addDocumentBase64',
                array(
                    $this->key,
                    $title,
                    $description,
                    $filename,
                    $mimetype,
                    base64_encode($content)
                ));

            return $iddocument;

        } catch (SoapFault $fault) {
            return 'Erreur send_doc() : ' . $fault->faultcode . " " .$fault->faultstring;
        }
    }

    public function send_doc_v5($title, $filename, $content, $indexingstate) {
        global $CFG;

        if (!check_dir_exists($CFG->dataroot . "/temp/compilatio", true, true)) {
            mkdir($CFG->dataroot . "/temp/compilatio", 0700);
        }

        $filepath = $CFG->dataroot . "/temp/compilatio/" . date('Y-m-d H:i:s') . ".txt";
        $handle = fopen($filepath, "w+");
        fwrite($handle, $content);

        $params = array(
            'file' => new \CURLFile($filepath),
            'filename' => $filename,
            'title' => $title,
            'indexed' => boolval($indexingstate),
            'origin' => 'moodle'
        );

        $ch = curl_init();

        $curloptions = array(
            CURLOPT_URL => "https://app.compilatio.net/api/private/document/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Auth-Token: ' . $this->key),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        );

        curl_setopt_array($ch, $curloptions);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        unlink($filepath);

        if (!isset($response->status->code, $response->status->message)) {
            return "Error in function send_doc() : request response's status not found";
        }

        if ($response->status->code == 201) {
            return $response->data->document->id;
        } else {
            return $response->status->message;
        }
    }

    /**
     * Get back information about a document
     *
     * @param  string $compihash External ID of the document
     * @return string            Return the document if succeed, an error message otherwise
     */
    public function get_doc($compihash) {

        try {
            if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }

            $param = array($this->key, $compihash);
            $document = $this->soapcli->__call('getDocument', $param);

            return $document;

        } catch (SoapFault $fault) {
            return("Erreur GetDoc()" . $fault->faultcode ." " .$fault->faultstring);
        }
    }

    /**
     * Get back the URL of a report document
     *
     * @param  string $compihash External ID of the document
     * @return string            Return the URL if succeed, an error message otherwise
     */
    public function get_report_url($compihash) {

        try {
            if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }

            $param = array($this->key, $compihash);
            $url = $this->soapcli->__call('getDocumentReportUrl', $param);
            return $url;

        } catch (SoapFault $fault) {
             return("Erreur  GetReportUrl()" . $fault->faultcode ." " .$fault->faultstring);
        }
    }

    /**
     * Delete a document on the Compilatio account
     *
     * @param  string $compihash External ID of the document
     * @return mixed             Return an error message if not succeed
     */
    public function del_doc($compihash) {

        try {
            if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }

            $param = array($this->key, $compihash);
            $this->soapcli->__call('deleteDocument', $param);

        } catch (SoapFault $fault) {
            return("Erreur  DelDoc()" . $fault->faultcode ." " .$fault->faultstring);
        }
    }

    /**
     * Start an analyse of a document
     *
     * @param  string $compihash External ID of the document
     * @return mixed             Return true if succeed, an error object otherwise
     */
    public function start_analyse($compihash) {

        try {
            if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }

            $param = array($this->key, $compihash);
            $this->soapcli->__call('startDocumentAnalyse', $param);

        } catch (SoapFault $fault) {
            $error = new stdClass();
            $error->code = $fault->faultcode;
            $error->string = $fault->faultstring;
            return $error;
        }

        return true;
    }

    /**
     * Get back Compilatio account's quotas
     *
     * @return array Informations about quotas
     */
    public function get_quotas() {

        try {
            if (!is_object($this->soapcli)) {
                return array("quotas" => null, "error" => $this->soapcli);
            }

            $param  = array($this->key);
            $resultat = $this->soapcli->__call('getAccountQuotas', $param);
            return array("quotas" => $resultat, "error" => null);

        } catch (SoapFault $fault) {
             return array("quotas" => null, "error" => $fault);
        }
    }

    /**
     * Get expiration date of an account
     *
     * @return mixed Return the expiration date if succeed, false otherwise
     */
    public function get_account_expiration_date() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $param = array($this->key);
            return $this->soapcli->__call('getSubscriptionEndDate', $param);

        } catch (SoapFault $fault) {
            return false;
        }
    }

    /**
     * Post Moodle Configuration to Compilatio
     *
     * @param  string $releasephp    PHP version
     * @param  string $releasemoodle Moodle version
     * @param  string $releaseplugin PLugin version
     * @param  string $language      Language
     * @param  string $cronfrequency CRON frequency
     * @return boolean               Return true if succeed, false otherwise
     */
    public function post_configuration($releasephp,
                                       $releasemoodle,
                                       $releaseplugin,
                                       $language,
                                       $cronfrequency) {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $param = array(
                $this->key,
                $releasephp,
                $releasemoodle,
                $releaseplugin,
                $language,
                $cronfrequency
            );

            $resultat = $this->soapcli->__call('postMoodleConfiguration', $param);
            return $resultat == 1;

        } catch (SoapFault $fault) {
             return false;
        }
    }

    /**
     * Get a list of the current Compilatio news.
     *
     * @return mixed    return a TechnicalNews object if succeed, false otherwise.
     */
    public function get_technical_news() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $param = array($this->key);
            return $this->soapcli->__call('getTechnicalNews', $param);

        } catch (SoapFault $fault) {
            return false;
        }
    }

    /**
     * Get a list of the current Compilatio alerts.
     *
     * @return mixed    return a Alert object if succeed, false otherwise.
     */
    public function get_alerts() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $param = array($this->key);
            return $this->soapcli->__call('getAlerts', $param);

        } catch (SoapFault $fault) {
            return false;
        }
    }

    /**
     * Get the maximum size authorized by Compilatio.
     *
     * @return mixed return an int of size if succeed, false otherwise.
     */
    public function get_allowed_file_max_size() {

        global $SESSION;

        try {
            if (isset($SESSION->compilatio_allowed_file_max_size)) {
                return $SESSION->compilatio_allowed_file_max_size;
            }
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key);
            $SESSION->compilatio_allowed_file_max_size = $this->soapcli->__call('getAllowedFileMaxSize', $params);
            return $SESSION->compilatio_allowed_file_max_size;

        } catch (SoapFault $fault) {
            return false;
        }

    }

    /**
     * Get a list of the allowed file types by Compilatio.
     *
     * @return mixed    return a CompilatioFileTypes object if succeed, false otherwise.
     */
    public function get_allowed_file_types() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key);
            return $this->soapcli->__call('getAllowedFileTypes', $params);

        } catch (SoapFault $fault) {
            return false;
        }
    }

    /**
     * Get back the indexing state of a document
     *
     * @param  string $compid   Document ID of the document
     * @return boolean          Return a boolean if succeed, null otherwise
     */
    public function get_indexing_state($compid) {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key, $compid);
            return $this->soapcli->__call('getIndexRefLibrary', $params);

        } catch (SoapFault $fault) {
            return false;
        }

    }

    /**
     * Set the indexing state of a document
     *
     * @param   string  $compid     Document ID
     * @param   bool    $indexed    Indexing state
     * @return  bool                Return true if succeed, false otherwise
     */
    public function set_indexing_state($compid, $indexed) {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key, $compid, $indexed);

            $result = $this->soapcli->__call('setIndexRefLibrary', $params);

            if ($result['status'] ?? "" == 200) {
                return true;
            } else {
                return false;
            }

        } catch (SoapFault $fault) {
            return "Error set_indexing_state() " . $fault->faultcode . " " . $fault->faultstring;
        }

    }

    /**
     * Get the id_groupe for helpcenter login
     *
     * @return string return the id_groupe if succeed, false otherwise.
     */
    public function get_id_groupe() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key);
            return $this->soapcli->__call('getIdGroupe', $params);

        } catch (SoapFault $fault) {
            return false;
        }
    }

    /**
     * Get the waiting time for analysis begins
     *
     * @return mixed return the magister waiting time if succeed, false otherwise.
     */
    public function get_waiting_time() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key);
            return $this->soapcli->__call('getWaitingTime', $params);

        } catch (SoapFault $fault) {
            return false;
        }

    }

    /**
     * Check if the API key has access rights to the analyses by students.
     *
     * @return bool return true if api key has access to student analyses, false otherwise.
     */
    public function check_allow_student_analyses() {

        try {
            if (!is_object($this->soapcli)) {
                return false;
            }

            $params = array($this->key);
            return $this->soapcli->__call('checkAllowStudentAnalyses', $params);

        } catch (SoapFault $fault) {
            return false;
        }

    }
}

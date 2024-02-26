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
 * api.php - Contains methods to communicate with Compilatio REST API.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompilatioAPI class
 */
class CompilatioAPI {
    private $apikey;
    private $urlrest;
    private $userid;
    private $recipe;

    public function set_user_id($userid) {
        $this->userid = $userid;
    }

    public function __construct($userid = null, $apikey = null) {
        if (null === $apikey) {
            $apikey = get_config('plagiarism_compilatio', 'apikey');
        }

        $this->urlrest = 'https://app.compilatio.net';
        $this->userid = $userid;

        if (!empty($apikey)) {
            $this->apikey = $apikey;
        } else {
            return 'API key not available';
        }
    }

    public function get_config() {
        $endpoint = '/api/public/config/config';
        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data;
        }
        return false;
    }

    /**
     * Check if the api key is valid
     *
     * @return boolean Return true if valid, an error message otherwise
     */
    public function check_apikey() {
        $endpoint = '/api/private/authentication/check-api-key';
        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            $readonly = $response->data->user->current_api_key->read_only ?? false;
            set_config('read_only_apikey', (int) $readonly, 'plagiarism_compilatio');

            $recipe = $response->data->user->managed_bundle->name === 'magister-premium' ? 'anasim-premium' : 'anasim';
        }

        set_config('recipe', $recipe ?? 'anasim', 'plagiarism_compilatio');

        $endpoint = '/api/private/user/lms/23a3a6980c0f49d98c5dc1ec03478e9161ad5d352cb4651b14865d21d0e81be';

        $response = json_decode($this->build_curl($endpoint));

        $error = $this->get_error_response($response, 404);
        if ($error === false) {
            return true;
        }
        return $error;
    }

    public function get_apikey_user_id() {
        $endpoint = '/api/private/authentication/check-api-key';

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            $oldmoodleownerid = $response->data->user->current_api_key->old_moodle_owner_id ?? null;

            if (!empty($oldmoodleownerid)) {
                return $oldmoodleownerid;
            }
            
            $this->update_apikey();

            return $response->data->user->id;
        }
        return false;
    }

    public function update_apikey() {
        $endpoint = '/api/private/moodle-configuration/update-api-key';

        $response = json_decode($this->build_curl($endpoint, 'post'));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }

        return false;
    }

    /**
     * Check if the API key has access rights to the analyses by students.
     *
     * @return bool return true if api key has access to student analyses, false otherwise.
     */
    public function check_allow_student_analyses() {
        $endpoint = '/api/private/authentication/check-api-key';

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            $bundle = $response->data->user->current_bundle;

            foreach ($bundle->accesses as $access) {
                if ($access->resource == 'api') {
                    if (isset($access->config, $access->config->allow_student_analysis_from_lms)) {
                        return $access->config->allow_student_analysis_from_lms;
                    }
                }
            }
        }
        return false;
    }

    public function get_or_create_user($teacher = null) {
        global $USER, $DB;

        if (!isset($teacher)) {
            $teacher = $USER;
        }

        // Check if user already exists in Compilatio.
        $compilatioid = $this->get_user_by_email($teacher->email);

        // Create the user if doesn't exists.
        if ($compilatioid == 404) {
            $lang = substr(current_language(), 0, 2);
            $compilatioid = $this->set_user($teacher->firstname, $teacher->lastname, $teacher->email, $lang);
        }

        if (!preg_match('/^[a-f0-9]{40}$/', $compilatioid)) {
            return null;
        }

        $user = new stdClass();
        $user->compilatioid = $compilatioid;
        $user->userid = $teacher->id;
        $user->id = $DB->insert_record('plagiarism_compilatio_user', $user);

        return $user;
    }

    /**
     * Create Elastisafe user
     *
     * @param   string  $firstname      User's firstname
     * @param   string  $lastname       User's lastname
     * @param   string  $email          User's email
     * @return  string                  Return the user's ID, an error message otherwise
     */
    private function set_user($firstname, $lastname, $email, $lang) {
        $endpoint = '/api/private/user/create';
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'locale' => [
                'timezone' => date_default_timezone_get(),
                'lang' => $lang,
            ],
            'origin' => 'LMS-Moodle',
        ];

        $response = json_decode($this->build_curl($endpoint, 'post', json_encode($params)));

        if ($this->get_error_response($response, 201) === false) {
            return $response->data->user->id;
        }
        return false;
    }

    /**
     * Get user
     *
     * @param   string  $userid
     * @return  mixed   Return the user if succeed, an error message otherwise
     */
    public function get_user($userid) {
        $endpoint = '/api/private/user/' . $userid;

        $response = json_decode($this->build_curl($endpoint));

        $error = $this->get_error_response($response, 200);
        if ($error === false) {
            return $response->data->user;
        }
        return false;
    }

    /**
     * Get user if exist for email
     *
     * @param   string  $email          Teacher's moodle email
     * @return  string                  Return the user's ID if exist, an error message otherwise
     */
    private function get_user_by_email($email) {
        $endpoint = '/api/private/user/lms/' . strtolower($email);

        $response = json_decode($this->build_curl($endpoint));

        $error = $this->get_error_response($response, 200);
        if ($error === false) {
            return $response->data->user->id;
        }
        return $response->status->code ?? false;
    }

    /**
     * Update Elastisafe user
     *
     * @param   string  $firstname      User's firstname
     * @param   string  $lastname       User's lastname
     * @param   string  $email          User's email
     * @return  string                  Return true if succeed, false otherwise
     */
    public function update_user($userid, $firstname, $lastname, $email) {
        $endpoint = '/api/private/user/' . $userid;
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
        ];

        $response = json_decode($this->build_curl($endpoint, 'patch', json_encode($params)));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Load document on Compilatio account
     *
     * @param   string  $filename       Filename
     * @param   string  $content        Document's content
     * @param   string  $folderid       Document's folder ID
     * @return  string                  Return the document's ID, an error message otherwise
     */
    public function set_document($filename, $folderid, $filepath, $indexed, $depositor, $authors) {
        $endpoint = '/api/private/document/';
        $params = [
            'file' => new \CURLFile($filepath),
            'filename' => $filename,
            'title' => $filename,
            'folder_id' => $folderid,
            'indexed' => $indexed,
            'origin' => 'moodle',
        ];

        $params['depositor'] = [
            'firstname' => $this->sanitize($depositor->firstname),
            'lastname' => $this->sanitize($depositor->lastname),
            'email_address' => $this->validate_email($depositor->email),
        ];

        foreach ($authors as $author) {
            $params['authors'][] = [
                'firstname' => $this->sanitize($author->firstname),
                'lastname' => $this->sanitize($author->lastname),
                'email_address' => $this->validate_email($author->email),
            ];
        }

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'upload', $params));

        $error = $this->get_error_response($response, 201);
        if ($error === false) {
            if (in_array('extraction_error', $response->data->document->tags, true)) {
                return false;
            }
            return $response->data->document->id;
        }
        return $error;
    }

    private function sanitize($value) {
        $forbiddencharacters = [
            ".", "!", "?", " => ", "%", "&", "*", "=", "#", "$", "@", "/", "\\", "<", ">", "(", ")", "[", "]", "{", "}",
        ];

        if (!is_string($value) || '' === $value) {
            return null;
        }

        $value = trim($value, " \n\r\t\v\x00" . implode('', $forbiddencharacters));

        return str_replace($forbiddencharacters, '_', $value);
    }

    private function validate_email($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Get back information about a document
     *
     * @param string   $docid  Document ID
     * @return mixed           Return the document if succeed, an error message otherwise
     */
    public function get_document($docid) {
        $endpoint = '/api/private/document/' . $docid;
        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint));
        $error = $this->get_error_response($response, 200);
        if ($error === false) {
            return $response->data->document;
        }
        return $error;
    }

    /**
     * Delete a document on the Compilatio account
     *
     * @param  string   $docid  Document ID
     * @return boolean          Return true if succeed, an error message otherwise
     */
    public function delete_document($docid) {
        $endpoint = '/api/private/document/' . $docid;
        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'delete'));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Create folder on Compilatio account
     *
     * @param   string  $name          Folder's name
     * @return  string                  Return the folder's ID, an error message otherwise
     */
    public function set_folder(
        $name,
        $defaultindexing,
        $analysistype,
        $analysistime,
        $warningthreshold = 10,
        $criticalthreshold = 25
    ) {
        $endpoint = '/api/private/folder/create';
        $params = [
            'name' => $name,
            'thresholds' => [
                'warning' => $warningthreshold,
                'critical' => $criticalthreshold,
            ],
            'default_indexing' => $defaultindexing,
            'auto_analysis' => false,
            'scheduled_analysis_enabled' => false,
            'origin' => 'LMS-Moodle',
        ];

        if ($analysistype == 'auto') {
            $params['auto_analysis'] = true;
        } else if ($analysistype == 'planned') {
            $params['scheduled_analysis_enabled'] = true;
            $params['scheduled_analysis_date'] = $analysistime;
        }

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'post', json_encode($params)));

        if ($this->get_error_response($response, 201) === false) {
            return $response->data->folder->id;
        }
        return false;
    }

    /**
     * Update folder on Compilatio account
     *
     * @param   int  $folderid          Folder ID
     * @return  string                  Return true if succeed, an error message otherwise
     */
    public function update_folder(
        $folderid,
        $name,
        $defaultindexing,
        $analysistype,
        $analysistime,
        $warningthreshold = 10,
        $criticalthreshold = 25
    ) {
        $endpoint = '/api/private/folder/'.$folderid;

        $params = [
            'name' => $name,
            'thresholds' => [
                'warning' => $warningthreshold,
                'critical' => $criticalthreshold,
            ],
            'default_indexing' => $defaultindexing,
            'auto_analysis' => false,
            'scheduled_analysis_enabled' => false,
        ];

        if ($analysistype == 'auto') {
            $params['auto_analysis'] = true;
        } else if ($analysistype == 'planned') {
            $params['scheduled_analysis_enabled'] = true;
            $params['scheduled_analysis_date'] = $analysistime;
        }

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'patch', json_encode($params)));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Delete a folder on the Compilatio account
     *
     * @param string   $folderid  Folder ID
     * @return boolean            Return true if succeed, an error message otherwise
     */
    public function delete_folder($folderid) {
        $endpoint = '/api/private/folder/' . $folderid;
        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'delete'));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Set the indexing state of a document
     *
     * @param   string  $docid      Document ID
     * @param   bool    $indexed    Indexing state
     * @return  mixed               Return true if succeed, an error message otherwise
     */
    public function set_indexing_state($docid, $indexed) {
        $endpoint = '/api/private/document/' . $docid;

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'patch', json_encode(['indexed' => $indexed])));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Get JWT to access a document report.
     *
     * @param  string $docid Document ID
     * @return string Return a JWT if succeed, an error otherwise
     */
    public function get_report_token($docid) {
        $endpoint = '/api/private/documents/' . $docid . '/report/jwt';

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'post'));

        if ($this->get_error_response($response, 201) === false) {
            return $response->data->jwt;
        }
        return false;
    }

    /**
     * Get back the PDF of a report
     *
     * @param  string $idreport Report ID
     * @param  string $lang     Language
     * @param  string $type     Report type
     * @return string           Return the PDF if succeed, an error message otherwise
     */
    public function get_pdf_report($idreport, $lang = 'en', $type = 'detailed') {
        global $CFG;

        $endpoint = '/api/private/report/anasim/' . $idreport . '/pdf/' . $lang . '/' . $type . '/';
        $filepath = $CFG->dataroot . '/temp/compilatio/' . $idreport . '_' . $lang . '_' . $type . '.pdf';

        $handle = fopen($filepath, 'wb');

        if ($this->build_curl_on_behalf_of_user($endpoint, 'download', null, $handle) == 200) {
            return $filepath;
        } else {
            return false;
        }
    }

    /**
     * Start an analyse of a document
     *
     * @param  string   $docid  Document ID
     * @return mixed    Return true if succeed, an error message otherwise
     */
    public function start_analyse($docid) {
        $this->recipe = $this->recipe ?? get_config('plagiarism_compilatio', 'recipe');

        $endpoint = '/api/private/analysis/';
        $params = [
            'doc_id' => $docid,
            'recipe_name' => $this->recipe,
            'tags' => [
                'stable',
            ],
            'params' => [
                'origin' => 'LMS-Moodle',
            ],
        ];

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'post', json_encode($params)));

        $error = $this->get_error_response($response, 201);
        if ($error === false) {
            return true;
        } else if (isset($response->errors->form[0])) {
            return $response->errors->form[0];
        }
        return $error;
    }

    /**
     * Get analysis and delete it
     *
     * @param  string   $docid  Document ID
     * @return mixed    Return true if succeed, an error message otherwise
     */
    public function delete_analyse($docid) {
        $endpoint = '/api/private/analysis/get-by-doc/' . $docid;
        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            $endpoint = '/api/private/analysis/' . $response->data->analysis->id;
            $response = json_decode($this->build_curl_on_behalf_of_user($endpoint, 'delete'));

            if ($this->get_error_response($response, 200) === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get a list of the allowed file types by Compilatio.
     *
     * @return  array   Return an array of the different allowed file types
     */
    public function get_allowed_file_types() {
        $endpoint = '/api/public/file/allowed-extensions';

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data;
        }
        return false;
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
    public function set_moodle_configuration($releasephp, $releasemoodle, $releaseplugin, $language, $cronfrequency) {
        $endpoint = '/api/private/moodle-configuration';
        $params = [
            'php_version' => $releasephp,
            'moodle_version' => $releasemoodle,
            'compilatio_plugin_version' => $releaseplugin,
            'language' => $language,
            'cron_frequency' => $cronfrequency,
        ];

        $response = json_decode($this->build_curl($endpoint, 'post', json_encode($params)));

        if ($this->get_error_response($response, 200) === false) {
            return true;
        }
        return false;
    }

    /**
     * Validate user's terms of service.
     *
     * @return boolean Return true if terms of service has been validated, false otherwise
     */
    public function validate_terms_of_service() {
        $endpoint = '/api/private/terms-of-service/validate';

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data->termsOfService_validated;
        }
        return false;
    }

    /**
     * Get zendesk jwt to authenticate user to help center.
     *
     * @return boolean Return jwt if succeed, false otherwise
     */
    public function get_zendesk_jwt() {
        $endpoint = '/api/private/user/zendesk/jwt';

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data->token;
        }
        return false;
    }

    /**
     * Get a list of Compilatio alerts.
     *
     * @return  array   Return an array of alerts
     */
    public function get_alerts() {
        $endpoint = '/api/public/alerts/moodle/' . get_config('plagiarism_compilatio', 'version');

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data->alerts;
        }
        return [];
    }

    /**
     * Get a list of Compilatio marketing notifications.
     *
     * @return  array   Return an array of marketing notifications
     */
    public function get_marketing_notifications($lang) {
        $endpoint = '/api/private/marketing-notifications/' . $lang;

        $response = json_decode($this->build_curl_on_behalf_of_user($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data->notifications;
        }

        return [];
    }

    /**
     * Get subscription info.
     *
     * @return  array   Return subscription info.
     */
    public function get_subscription_info() {
        $endpoint = '/api/private/authentication/check-api-key';
        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            foreach ($response->data->user->current_bundle->accesses as $access) {
                if ($access->resource == 'management') {
                    $magisterstandardbundleid = $access->bundle_id;
                }
            }
        }

        if (!isset($magisterstandardbundleid)) {
            return false;
        }

        $endpoint = '/api/private/subscription/bundles/' . $magisterstandardbundleid . '/last-subscription';

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            return $response->data->subscription;
        }

        return false;
    }

    /**
     * Get a Compilatio translation.
     *
     * @param  string  $lang  Language
     * @param  string  $key   Translation Key
     * @return string  Return the translation string
     */
    public function get_translation($lang, $key) {
        $endpoint = '/api/public/translation/last-version/' . $lang . '/key/' . $key;

        $response = json_decode($this->build_curl($endpoint));

        if ($this->get_error_response($response, 200) === false) {
            $translation = $response->data;
            foreach (explode('.', $key) as $object) {
                $translation = $translation->{$object};
            }
            return $translation;
        }
        return false;
    }

    private function get_error_response($response, $expectedstatuscode) {
        if (!isset($response->status->code, $response->status->message)) {
            return 'Error response status not found';
        } else if ($response->status->code == $expectedstatuscode) {
            return false;
        } else if (isset($response->errors->key) && $response->errors->key == 'need_terms_of_service_validation') {
            if (!empty($this->userid)) {
                global $DB;
                $user = $DB->get_record('plagiarism_compilatio_user', ['compilatioid' => $this->userid]);
                $user->validatedtermsofservice = false;
                $DB->update_record('plagiarism_compilatio_user', $user);
            }
            return $response->errors->key;
        } else if ($response->status->message == 'Forbidden ! Your read only API key cannot modify this resource') {
            set_config('read_only_apikey', 1, 'plagiarism_compilatio');
        }

        return $response->status->message;
    }

    private function build_curl_on_behalf_of_user($endpoint, $method = null, $data = null, $handle = null) {
        global $DB;

        $header = [];

        // Plugin v2 docs management.
        $header[] = null === $this->userid
            ? 'X-LMS-USER-ID: ' . $DB->get_field('plagiarism_compilatio_user', 'compilatioid', ['userid' => 0])
            : 'X-LMS-USER-ID: ' . $this->userid;
        return $this->build_curl($endpoint, $method, $data, $handle, $header);
    }

    private function build_curl($endpoint, $method = null, $data = null, $handle = null, $header = []) {
        global $CFG;

        $ch = curl_init();

        $params = [
            CURLOPT_URL => $this->urlrest . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $header[] = 'X-Auth-Token: ' . $this->apikey;

        if ($method !== 'upload') {
            $header[] = 'Content-Type: application/json';
        }
        $params[CURLOPT_HTTPHEADER] = $header;

        // Proxy settings.
        if (!empty($CFG->proxyhost)) {
            $params[CURLOPT_PROXY] = $CFG->proxyhost;

            $params[CURLOPT_HTTPPROXYTUNNEL] = false;

            if (!empty($CFG->proxytype) && ($CFG->proxytype == 'SOCKS5')) {
                $params[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
            }

            if (!empty($CFG->proxyport)) {
                $params[CURLOPT_PROXYPORT] = $CFG->proxyport;
            }

            if (!empty($CFG->proxyuser) && !empty($CFG->proxypassword)) {
                $params[CURLOPT_PROXYUSERPWD] = $CFG->proxyuser . ':' . $CFG->proxypassword;
            }
        }

        // SSL certificate verification.
        if (get_config('plagiarism_compilatio', 'disable_ssl_verification') == 1) {
            $params[CURLOPT_SSL_VERIFYPEER] = false;
        }

        switch ($method) {
            case 'post':
                $params[CURLOPT_POST] = true;
                $params[CURLOPT_POSTFIELDS] = $data;
                break;
            case 'upload':
                $params[CURLOPT_POST] = true;
                $params[CURLOPT_POSTFIELDS] = $this->build_post_fields($data);
                break;
            case 'patch':
                $params[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                $params[CURLOPT_POSTFIELDS] = $data;
                break;
            case 'delete':
                $params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'download':
                $params[CURLOPT_FILE] = $handle;
                $params[CURLOPT_TIMEOUT] = 20;
                $params[CURLOPT_FOLLOWLOCATION] = true;
                break;
        }

        curl_setopt_array($ch, $params);

        $result = curl_exec($ch);

        if ($method == 'download') {
            $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        return $result;
    }

    private function build_post_fields($data, $existingkeys = '', &$returnarray = []) {
        if (($data instanceof \CURLFile) || !(is_array($data) || is_object($data))) {
            $returnarray[$existingkeys] = $data;
            return $returnarray;
        } else {
            foreach ($data as $key => $item) {
                $this->build_post_fields($item, $existingkeys ? $existingkeys . "[$key]" : $key, $returnarray);
            }
            return $returnarray;
        }
    }
}

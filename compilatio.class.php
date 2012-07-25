<?php
/**
 * Description : compilatioservice - based on original compilatio class
 * �tablit la communication avec le serveur SOAP de Compilatio.net
 * appelle diverses m�thodes concernant la gestion d'un document dans Compilatio.net
 *
 * Date: 25/07/2012
 * @version    1.0 (updated by Dan Marsden <dan@danmarsden.com>
 *
 */

class compilatioservice {
	/* Clef d'identification pour le compte Compilatio*/
	var $key  = null;
	/*Connexion au Webservice*/
	var $soapcli;
	/*Constructeur -> on cr�er la connexion avec le webservice*/
	//MODIF 2009-03-19: passage des param�tres
	function compilatioservice($key,$urlsoap,$proxy_host='',$proxy_port='', $proxy_username='', $proxy_password='') {
		try {
			if (!empty($key)) {
				$this->key = $key;
				if (!empty($urlsoap)) {
                    $param = array('trace'=>false,
                                   'soap_version'=>SOAP_1_2,
                                   'exceptions'=>true);
					if(!empty($proxy_host)) {
						$param['proxy_host'] = '"' . $proxy_host . '"';
                        if (!empty($proxy_port)) {
                            $param['proxy_port'] = $proxy_port;
                        }
                        if(!empty($proxy_username) && !empty($proxy_password)) {
                            $param['proxy_login'] = '"' . $proxy_username . '"';
                            $param['proxy_password'] = '"' .$proxy_password. '"';
                        }
					}
                    $this->soapcli = new SoapClient($urlsoap,$param);
				} else {
					$this->soapcli = 'WS urlsoap not available' ;
				}
			} else {
				$this->soapcli ='API key not available';
			}
		} catch (SoapFault $fault) {
			$this->soapcli = "Error constructor compilatio " . $fault->faultcode ." " .$fault->faultstring ;
		} catch (Exception $e) {
        	$this->soapcli = "Error constructor compilatio with urlsoap" . $urlsoap;
    	}
	}


	/*M�thode qui permet le chargement de fichiers sur le compte compilatio*/
	function SendDoc($title,$description,$filename,$mimetype,$content) {
		try	{
			if (!is_object($this->soapcli)) {
				return("Error in constructor compilatio() " . $this->soapcli);
            }
			$idDocument = $this->soapcli->__call('addDocumentBase64',array($this->key,utf8_encode($title),utf8_encode($description),utf8_encode($filename),utf8_encode($mimetype),base64_encode($content)));
			return $idDocument;
		} catch (SoapFault $fault) {
			return("Erreur SendDoc()" . $fault->faultcode ." " .$fault->faultstring);
		}
	}
	/*M�thode qui r�cup�re les informations d'un document donn�*/
	function GetDoc($compi_hash) {
		try	{
			if (!is_object($this->soapcli)) {
				return("Error in constructor compilatio() " . $this->soapcli);
            }
			$param=array($this->key,$compi_hash);
			$idDocument = $this->soapcli->__call('getDocument',$param);
			return $idDocument;
		} catch (SoapFault $fault) {
			return("Erreur GetDoc()" . $fault->faultcode ." " .$fault->faultstring);
		}
	}

	/*M�thode qui permet de r�cup�r� l'url du rapport d'un document donn�*/
	function GetReportUrl($compi_hash) {
		try	{
			if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }
			$param=array($this->key,$compi_hash);
			$idDocument = $this->soapcli->__call('getDocumentReportUrl',$param);
			return $idDocument;
		} catch (SoapFault $fault) {
			return("Erreur  GetReportUrl()" . $fault->faultcode ." " .$fault->faultstring);
		}
	}

	/*M�thode qui permet de supprim� sur le compte compilatio un document donn�*/
	function DelDoc($compi_hash) {
		try	{
			if (!is_object($this->soapcli)) {
				return("Error in constructor compilatio() " . $this->soapcli);
            }
			$param=array($this->key,$compi_hash);
			$this->soapcli->__call('deleteDocument',$param);
			$this->soapcli->__call('dellAuteur',$param);
			$this->soapcli->__call('deleteAnalyseProgrammee',$param);
		} catch (SoapFault $fault) {
			return("Erreur  DelDoc()" . $fault->faultcode ." " .$fault->faultstring);
		}
	}

	/*M�thode qui permet de lancer l'analyse d'un document donn�*/
	function StartAnalyse($compi_hash) {
		try	{
			if (!is_object($this->soapcli)) {
                return("Error in constructor compilatio() " . $this->soapcli);
            }
			$param=array($this->key,$compi_hash);
			$this->soapcli->__call('startDocumentAnalyse',$param);
		} catch (SoapFault $fault) {
            $error = new stdClass();
            $error->code = $fault->faultcode;
            $error->string = $fault->faultstring;
            return $error;
		}
        return true;
	}
	/*M�thode qui permet de r�cup�r� les quotas du compte compilatio*/
	function GetQuotas() {
		try	{
			if (!is_object($this->soapcli)) {
                return null;
            }
			$param=array($this->key);
			$resultat=$this->soapcli->__call('getAccountQuotas',$param);
			return $resultat;
		} catch (SoapFault $fault) {
			return null;
		}
	}
}


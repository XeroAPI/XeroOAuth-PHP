<?php
require 'OAuthSimple.php';

// different app method defaults
$xro_defaults = array( 'xero_url'     => 'https://api.xero.com/api.xro/2.0',
                     'site'    => 'https://api.xero.com',
                     'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     'signature_method'    => 'HMAC-SHA1');
                     
$xro_private_defaults = array( 'xero_url'     => 'https://api.xero.com/api.xro/2.0',
                     'site'    => 'https://api.xero.com',
                     'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     'signature_method'    => 'RSA-SHA1');
                     
$xro_partner_defaults = array( 'xero_url'     => 'https://api-partner.network.xero.com/api.xro/2.0',
                     'site'    => 'https://api-partner.network.xero.com',
                     'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     'accesstoken_url'    => 'https://api-partner.xero.com/oauth/AccessToken',
                     'signature_method'    => 'RSA-SHA1');
                     
$xro_partner_mac_defaults = array( 'xero_url'     => 'https://api-partner2.network.xero.com/api.xro/2.0',
                     'site'    => 'https://api-partner2.network.xero.com',
                     'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     'accesstoken_url'    => 'https://api-partner2.xero.com/oauth/AccessToken',
                     'signature_method'    => 'RSA-SHA1');
                     
// standard Xero OAuth stuff
$xro_consumer_options = array( 'request_token_path'    => '/oauth/RequestToken',
                     'access_token_path'    => '/oauth/AccessToken',
                     'authorize_path'    => '/oauth/Authorize');
                     
/** Define a custom Exception for easy trap and detection
*/
class XeroOAuthException extends Exception {}

class XeroOAuth {
    var $_xero_defaults;
    var $_xero_consumer_options;
    var $_action;
    var $_nonce_chars;

    /** XeroOAuth creator
     *
     * Create an instance of XeroOAuth
     *
     * @param app_type {string}       The Xero API application type: http://blog.xero.com/developer/api-overview/
	 */
    function XeroOAuth($AppType = "Public") {
        if (!empty($AppType)){
        	switch ($AppType) {
    			case "Public":
            	$this->_xero_defaults =  array( 'xero_url'     => 'https://api.xero.com/api.xro/2.0',
                     							'site'    => 'https://api.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'HMAC-SHA1');
            	case "Private":
            	$this->_xero_defaults = array(	'xero_url'     => 'https://api.xero.com/api.xro/2.0',
                     							'site'    => 'https://api.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'RSA-SHA1');
            	case "Partner": 
            	$this->_xero_defaults = array( 	'xero_url'     => 'https://api-partner.network.xero.com/api.xro/2.0',
                     							'site'    => 'https://api-partner.network.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'RSA-SHA1');
                case "Partner_Mac":
                $this->_xero_defaults = array( 	'xero_url'     => 'https://api-partner2.network.xero.com/api.xro/2.0',
                     							'site'    => 'https://api-partner2.network.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'RSA-SHA1');
                break;
            	}
            }
            	
          $this->_xero_consumer_options = array('request_token_path'    => '/oauth/RequestToken',
                     							'access_token_path'    => '/oauth/AccessToken',
                     							'authorize_path'    => '/oauth/Authorize');  	
       
        return $this;
    }
    
    
    function MakeRequest($endpoint, $parameters, $action, $data, $app_type, $format="xml"){
    	$oauthObject = new OAuthSimple();
    	
    	# Set some standard curl options....
		$options[CURLOPT_VERBOSE] = 1;
    	$options[CURLOPT_RETURNTRANSFER] = 1;
    	$options[CURLOPT_SSL_VERIFYHOST] = 0;
    	$options[CURLOPT_SSL_VERIFYPEER] = 0;
    	$useragent = USER_AGENT;
    	$useragent = isset($useragent) ? USER_AGENT : 'XeroOAuth-PHP';
    	$options[CURLOPT_USERAGENT] = $useragent;
    
    }
    
    }
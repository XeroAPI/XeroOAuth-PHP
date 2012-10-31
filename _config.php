<?php
/**
 * @file
 * A single location to store configuration.
 */

/**
 * Define for file includes
 */
define('BASE_PATH',dirname(__FILE__));

/**
 * Define which app type you are using: 
 * Private - private app method
 * Public - standard public app method
 * Partner - partner app method      
 */      
define("XRO_APP_TYPE",     "Partner");

/**
 * It is a good idea to set a user agent for the Xero API logs - your application name is best
 */
$useragent = "";

/**
 * Set your callback url or set 'oob' if none required
 */
define("OAUTH_CALLBACK",     'http://localhost/XeroOAuth-PHP/example.php');

/**
 * Application specific settings
 * Not all are required for given application types
 * consumer_key: required for all applications
 * consumer_secret:  for partner applications, set to: s (cannot be blank)
 * rsa_private_key: not needed for public applications
 * rsa_public_key: not needed for public applications
 */
                     	 
$signatures = array( 'consumer_key'     => 'MWSAN8S5AAFPMMNBV3DQIEWH4TM9FE',
              	      	 'shared_secret'    => 's',
						 // API version 
                     	 'api_version'				=> '2.0',);

if(XRO_APP_TYPE=="Private"||XRO_APP_TYPE=="Partner"){
	$signatures['rsa_private_key']	= BASE_PATH . '/certs/rq-partner-app-2-privatekey.pem';
	$signatures['rsa_public_key']	= BASE_PATH . '/certs/php-test-private-rq-publickey.cer';
}

                     	 
/**
 * Special options for Partner applications 
 * Partner applications require a Client SSL certificate which is issued by Xero
 * the certificate is issued as a .p12 cert which you will then need to split into a cert and private key:
 * openssl pkcs12 -in entrust-client.p12 -clcerts -nokeys -out entrust-cert.pem
 * openssl pkcs12 -in entrust-client.p12 -nocerts -out entrust-private.pem <- you will be prompted to enter a password
 */   	
if(XRO_APP_TYPE=="Partner"){
	$signatures['curl_ssl_cert'] = BASE_PATH . '/certs/entrust-cert-2012.pem';
	$signatures['curl_ssl_password'] = '1234';
	$signatures['curl_ssl_key'] = BASE_PATH . '/certs/entrust-private-2012.pem';
}




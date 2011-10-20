<?php
/**
 * @file
 * A single location to store configuration.
 */

/**
 * Define for file includes
 */
define('BASE_PATH',realpath('.'));

/**
 * Define which app type you are using: 
 * Private - private app method
 * Public - standard public app method
 * Partner - partner app method      
 */      
define("XRO_APP_TYPE",     "Partner");

/**
 * Set your callback url or set 'oob' if none required
 */
define("OAUTH_CALLBACK",     'http://localhost/XeroOAuth-PHP/example.php');

/**
 * Application specific settings
 * Not all are required for given application types
 * consumer_key: required for all applications
 * shared_secret:  for partner applications, set to: s (cannot be blank)
 * rsa_private_key: not needed for public applications
 * rsa_public_key: not needed for public applications
 */
                     	 
$signatures = array( 'consumer_key'     => 'MWSAN8S5AAFPMMNBV3DQIEWH4TM9FE',
              	      	 'shared_secret'    => 's',
                	     'rsa_private_key'	=> BASE_PATH . '/certs/rq-partner-app-2-privatekey.pem',
                     	 'rsa_public_key'	=> BASE_PATH . '/certs/rq-partner-app-2-publickey.cer');

                     	 
/**
 * Special options for Partner applications - should be commented out for non-partner applications
 * Partner applications require a Client SSL certificate which is issued by Xero
 * the certificate is issued as a .p12 cert which you will then need to split into a cert and private key:
 * openssl pkcs12 -in entrust-client.p12 -clcerts -nokeys -out entrust-cert.pem
 * openssl pkcs12 -in entrust-client.p12 -nocerts -out entrust-private.pem <- you will be prompted to enter a password
 */   	
$options[CURLOPT_SSLCERT] = BASE_PATH . '/certs/entrust-cert.pem';
$options[CURLOPT_SSLKEYPASSWD] = '1234';
$options[CURLOPT_SSLKEY] = BASE_PATH . '/certs/entrust-private.pem';

/**
 * It is a good idea to set a user agent for the Xero API logs
 */
$useragent = "";


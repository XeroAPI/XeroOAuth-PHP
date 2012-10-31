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

  /**
   * Creates a new XeroOAuth object
   *
   * @param string $config, the configuration settings
   */

    function __construct($config) {
    	$this->params = array();
    	$this->headers = array();
    	$this->auto_fixed_time = false;
    	$this->buffer = null;
  
        if (!empty($config['application_type'])){
        	switch ($config['application_type']) {
    			case "Public":
            	$this->_xero_defaults =  array( 'xero_url'     => 'https://api.xero.com/api.xro',
                     							'site'    => 'https://api.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'HMAC-SHA1');
            	case "Private":
            	$this->_xero_defaults = array(	'xero_url'     => 'https://api.xero.com/api.xro',
                     							'site'    => 'https://api.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'RSA-SHA1');
            	case "Partner": 
            	$this->_xero_defaults = array( 	'xero_url'     => 'https://api-partner.network.xero.com/api.xro',
                     							'site'    => 'https://api-partner.network.xero.com',
                     							'authorize_url'    => 'https://api.xero.com/oauth/Authorize',
                     							'signature_method'    => 'RSA-SHA1');
            	
                break;
            	}
            }
            	
          $this->_xero_consumer_options = array('request_token_path'    => 'oauth/RequestToken',
                     							'access_token_path'    => 'oauth/AccessToken',
                     							'authorize_path'    => 'oauth/Authorize');  	
       $this->_xero_curl_options = array( // you probably don't want to change any of these curl values
									        'curl_connecttimeout'        => 30,
									        'curl_timeout'               => 10,
									        // for security you may want to set this to TRUE. If you do you need
									        // to install the servers certificate in your local certificate store.
									        'curl_ssl_verifypeer'        => false,
									        'curl_followlocation'        => false, // whether to follow redirects or not
									        'curl_ssl_verifyhost'        => false,
									        // support for proxy servers
									        'curl_proxy'                 => false, // really you don't want to use this if you are using streaming
									        'curl_proxyuserpwd'          => false, // format username:password for proxy, if required
									        'curl_encoding'              => '',    // leave blank for all supported formats, else use gzip, deflate, identity
											'curl_verbose'        		 => true,	
       			);  

          $this->config = array_merge($config, $this->_xero_defaults, $this->_xero_consumer_options, $this->_xero_curl_options);
    }
    
 /**
   * Utility function to parse the returned curl headers and store them in the
   * class array variable.
   *
   * @param object $ch curl handle
   * @param string $header the response headers
   * @return the string length of the header
   */
  private function curlHeader($ch, $header) {
    $i = strpos($header, ':');
    if ( ! empty($i) ) {
      $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
      $value = trim(substr($header, $i + 2));
      $this->response['headers'][$key] = $value;
    }
    return strlen($header);
  }

  /**
    * Utility function to parse the returned curl buffer and store them until
    * an EOL is found. The buffer for curl is an undefined size so we need
    * to collect the content until an EOL is found.
    *
    * This function calls the previously defined streaming callback method.
    *
    * @param object $ch curl handle
    * @param string $data the current curl buffer
    */
  private function curlWrite($ch, $data) {
    $l = strlen($data);
    if (strpos($data, $this->config['streaming_eol']) === false) {
      $this->buffer .= $data;
      return $l;
    }

    $buffered = explode($this->config['streaming_eol'], $data);
    $content = $this->buffer . $buffered[0];

    $this->metrics['tweets']++;
    $this->metrics['bytes'] += strlen($content);

    if ( ! function_exists($this->config['streaming_callback']))
      return 0;

    $metrics = $this->update_metrics();
    $stop = call_user_func(
      $this->config['streaming_callback'],
      $content,
      strlen($content),
      $metrics
    );
    $this->buffer = $buffered[1];
    if ($stop)
      return 0;

    return $l;
  }
  
 /**
   * Extracts and decodes OAuth parameters from the passed string
   *
   * @param string $body the response body from an OAuth flow method
   * @return array the response body safely decoded to an array of key => values
   */
  function extract_params($body) {
    $kvs = explode('&', $body);
    $decoded = array();
    foreach ($kvs as $kv) {
      $kv = explode('=', $kv, 2);
      $kv[0] = $this->safe_decode($kv[0]);
      $kv[1] = $this->safe_decode($kv[1]);
      $decoded[$kv[0]] = $kv[1];
    }
    return $decoded;
  }
  
 /**
   * Decodes the string or array from it's URL encoded form
   * If an array is passed each array value will will be decoded.
   *
   * @param mixed $data the scalar or array to decode
   * @return $data decoded from the URL encoded form
   */
  private function safe_decode($data) {
    if (is_array($data)) {
      return array_map(array($this, 'safe_decode'), $data);
    } else if (is_scalar($data)) {
      return rawurldecode($data);
    } else {
      return '';
    }
  }
  
/**
   * Prepares the HTTP method for use in the base string by converting it to
   * uppercase.
   *
   * @param string $method an HTTP method such as GET or POST
   * @return void value is stored to a class variable
   * @author themattharris
   */
  private function prepare_method($method) {
    $this->method = strtoupper($method);
  }

  /**
   * Makes a curl request. Takes no parameters as all should have been prepared
   * by the request method
   *
   * @return void response data is stored in the class variable 'response'
   */
  private function curlit() {
    // method handling
    switch ($this->method) {
      case 'POST':
        break;
      default:
        // GET, DELETE request so convert the parameters to a querystring
        if ( ! empty($this->request_params)) {
          foreach ($this->request_params as $k => $v) {
            // Multipart params haven't been encoded yet.
            // Not sure why you would do a multipart GET but anyway, here's the support for it
            if ($this->config['multipart']) {
              $params[] = $this->safe_encode($k) . '=' . $this->safe_encode($v);
            } else {
              $params[] = $k . '=' . $v;
            }
          }
          $qs = implode('&', $params);
          $this->url = strlen($qs) > 0 ? $this->url . '?' . $qs : $this->url;
          $this->request_params = array();
        }
        break;
    }

    // configure curl
    $c = curl_init();
    $useragent = (isset($this->config['user_agent'])) ? (empty($this->config['user_agent']) ? 'XeroOAuth-PHP' : $this->config['user_agent']) : 'XeroOAuth-PHP'; 
    curl_setopt_array($c, array(
      CURLOPT_USERAGENT      => $useragent,
      CURLOPT_CONNECTTIMEOUT => $this->config['curl_connecttimeout'],
      CURLOPT_TIMEOUT        => $this->config['curl_timeout'],
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_SSL_VERIFYPEER => $this->config['curl_ssl_verifypeer'],
      CURLOPT_SSL_VERIFYHOST => $this->config['curl_ssl_verifyhost'],
      CURLOPT_FOLLOWLOCATION => $this->config['curl_followlocation'],
      CURLOPT_PROXY          => $this->config['curl_proxy'],
      CURLOPT_ENCODING       => $this->config['curl_encoding'],
      CURLOPT_URL            => $this->sign['signed_url'],
      CURLOPT_VERBOSE        => $this->config['curl_verbose'],
      // process the headers
      CURLOPT_HEADERFUNCTION => array($this, 'curlHeader'),
      CURLOPT_HEADER         => FALSE,
      CURLINFO_HEADER_OUT    => TRUE,
      // ssl client cert options for partner apps
      CURLOPT_SSLCERT         => $this->config['curl_ssl_cert'],
      CURLOPT_SSLKEYPASSWD    => $this->config['curl_ssl_password'],
      CURLOPT_SSLKEY          => $this->config['curl_ssl_key'],
    ));

    if ($this->config['curl_proxyuserpwd'] !== false)
      curl_setopt($c, CURLOPT_PROXYUSERPWD, $this->config['curl_proxyuserpwd']);

    if ($this->config['is_streaming']) {
      // process the body
      $this->response['content-length'] = 0;
      curl_setopt($c, CURLOPT_TIMEOUT, 0);
      curl_setopt($c, CURLOPT_WRITEFUNCTION, array($this, 'curlWrite'));
    }

    switch ($this->method) {
      case 'GET':
        break;
      case 'POST':
        curl_setopt($c, CURLOPT_POST, TRUE);
        break;
      default:
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $this->method);
    }

    if ( ! empty($this->request_params) ) {
      // if not doing multipart we need to implode the parameters
      if ( ! $this->config['multipart'] ) {
        foreach ($this->request_params as $k => $v) {
          $ps[] = "{$k}={$v}";
        }
        $this->request_params = implode('&', $ps);
      }
      curl_setopt($c, CURLOPT_POSTFIELDS, $this->request_params);
    } else {
      // CURL will set length to -1 when there is no data, which breaks Twitter
      $this->headers['Content-Type'] = '';
      $this->headers['Content-Length'] = '';
    }

    // CURL defaults to setting this to Expect: 100-Continue which Twitter rejects
    $this->headers['Expect'] = '';

    if ( ! empty($this->headers)) {
      foreach ($this->headers as $k => $v) {
        $headers[] = trim($k . ': ' . $v);
      }
      curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    }

    if (isset($this->config['prevent_request']) && false == $this->config['prevent_request'])
      return;

    // do it!
    $response = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $info = curl_getinfo($c);
    curl_close($c);
    
    // store the response
    $this->response['code'] = $code;
    $this->response['response'] = $response;
    $this->response['info'] = $info;
    $this->response['format'] = $this->format;
    return $code;
  }
    
    function MakeRequest($endpoint, $parameters, $action, $data, $app_type, $format="xml"){
    	$oauthObject = new OAuthSimple();
    	
    	# Set some standard curl options....
		
    	$useragent = USER_AGENT;
    	$useragent = isset($useragent) ? USER_AGENT : 'XeroOAuth-PHP';
    	$options[CURLOPT_USERAGENT] = $useragent;
    	  $options[CURLOPT_VERBOSE] = 1;
    	$options[CURLOPT_RETURNTRANSFER] = 1;
    	$options[CURLOPT_SSL_VERIFYHOST] = 0;
    	$options[CURLOPT_SSL_VERIFYPEER] = 0;
    
    }
    
/**
   * Make an HTTP request using this library. This method doesn't return anything.
   * Instead the response should be inspected directly.
   *
   * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
   * @param string $url the request URL without query string parameters
   * @param array $params the request parameters as an array of key=value pairs
   * @param string $useauth whether to use authentication when making the request. Default true.
   * @param string $multipart whether this request contains multipart data. Default false
   * @param string $format the format of the response. Default json. Set to an empty string to exclude the format

   */
  function request($method, $url, $params=array(), $useauth=true, $multipart=false, $format='xml') {
  	
  	  	if(isset($format)){
  		switch ($format) {
    			case "pdf":
  					$this->headers['Accept'] = 'Accept: application/pdf'; 
  					break;
  				case "json":
  					$this->headers['Accept'] = 'Accept: application/json'; 
  				break;
  				default:
  					$this->headers['Accept'] = 'Accept: application/xml'; 
  				break;
  		}
  	}
  	
	$this->prepare_method($method);
    $this->config['multipart'] = $multipart;
	$this->url = $url;
   $oauthObject = new OAuthSimple();
   $this->sign = $oauthObject->sign(array(
        'path'      => $url,
        'parameters'=> array_merge($params,array(
        	'order' => urlencode($_REQUEST['order']),
			'oauth_signature_method' => $this->config['signature_method'])),
        'signatures'=> $this->config));
   $this->format = $format;

    $curlRequest = $this->curlit();
    if( $this->response['code']==401 && isset($this->config['session_handle'])){
    	$params = array(
    		'oauth_session_handle'	=> $this->config['session_handle'],
            'oauth_token'	=> $this->config['access_token'],
  			);
    	 unset($this->config['access_token']);
    	$this->request('GET', $this->url('AccessToken', ''), $params);
    	exit;
    } 
    if( $this->response['code']==403){
    	$errorMessage = "It looks like your client SSL cert issued by Xero is either invalid or has expired. See http://developer.xero.com/api-overview/http-response-codes/#403 for more";
    	// default IIS page isn't informative, a little swap
    	$this->response['response'] = $errorMessage;
    	//exit;
    }
    return $curlRequest;
  }
  
/**
   * Convert the response into usable data
   *
   * @param string $response the raw response from the API
   * @param string $format the format of the response
   * @return string the concatenation of the host, API version and API method
   */
  function parseResponse($response, $format) {
    
    	if(isset($format)){
  		switch ($format) {
    			case "pdf":
  					$theResponse = json_decode($response);  
  					break;
  				case "json":
  					$theResponse = json_decode($response);  
  				break;
  				default:
  					$theResponse = simplexml_load_string($response); 
  				break;
  		}
  	}
  
  	}
  	
 /**
   * Utility function to create the request URL in the requested format
   *
   * @param string $request the API method without extension
   * @return string the concatenation of the host, API version and API method
   */
  function url($request) {
    
  	if($request=="RequestToken"){
  		$this->config['host'] = $this->config['site'] . '/oauth/';
  	}elseif($request=="Authorize"){
  		$this->config['host'] = $this->config['authorize_url'];
  		$request = "";
  	}elseif($request=="AccessToken"){
  		$this->config['host'] = $this->config['site'] . '/oauth/';
  	}else{
  		if (isset($this->config['api_version']))
      	$this->config['host'] = $this->config['xero_url'] . '/' . $this->config['api_version'] . '/';
  	}
    
      
      

    return implode(array(
      $this->config['host'],
      $request
    ));
  }
  
  /**
   * Returns the current URL. This is instead of PHP_SELF which is unsafe
   *
   * @param bool $dropqs whether to drop the querystring or not. Default true
   * @return string the current URL
   */
  function php_self($dropqs=true) {
    $url = sprintf('%s://%s%s',
      empty($_SERVER['HTTPS']) ? (@$_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http') : 'http',
      $_SERVER['SERVER_NAME'],
      $_SERVER['REQUEST_URI']
    );
    
    $parts = parse_url($url);

    $port = $_SERVER['SERVER_PORT'];
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = @$parts['path'];
    $qs   = @$parts['query'];

    $port or $port = ($scheme == 'https') ? '443' : '80';

    if (($scheme == 'https' && $port != '443')
        || ($scheme == 'http' && $port != '80')) {
      $host = "$host:$port";
    }
    $url = "$scheme://$host$path";
    if ( ! $dropqs)
      return "{$url}?{$qs}";
    else
      return $url;
  }
  
  /*
   *
   * Run some basic checks on our config options etc to make sure all is ok
   * 
   */
  
  function diagnostics(){
  	$testOutput = array();
  		if($this->config['application_type']=='Partner'){
  			if(!file_get_contents($this->config['curl_ssl_cert'])){
  				$testOutput['ssl_cert_error'] = "Can't read the Xero Entrust cert. You need one for partner API applications. http://developer.xero.com/partner-applications-certificates-explained/ \n";
  			}else{
  				$data = openssl_x509_parse(file_get_contents($this->config['curl_ssl_cert']));
  				$validFrom = date('Y-m-d H:i:s', $data['validFrom_time_t']);
  				if(time() < $data['validFrom_time_t']){
					$testOutput['ssl_cert_error'] = "Xero Entrust cert not yet valid - cert valid from "  . $validFrom . "\n";
				} 
				$validTo = date('Y-m-d H:i:s', $data['validTo_time_t']);
				if(time() > $data['validTo_time_t']){
					$testOutput['ssl_cert_error'] = "Xero Entrust cert expired - cert valid to "  . $validFrom . "\n";
				} 
  			} 
  			
			
		if($this->config['application_type']=='Partner' || $this->config['application_type']=='Private'){
			if(!file_exists($this->config['rsa_public_key'])) $testOutput['rsa_cert_error'] = "Can't read the self-signed SSL cert. Private and Partner API applications require a self-signed X509 cert http://developer.xero.com/api-overview/setup-an-application/#certs \n";
			if(file_exists($this->config['rsa_public_key'])){
				$data = openssl_x509_parse(file_get_contents($this->config['rsa_public_key']));
				$validFrom = date('Y-m-d H:i:s', $data['validFrom_time_t']);
  				if(time() < $data['validFrom_time_t']){
					$testOutput['ssl_cert_error'] = "Application cert not yet valid - cert valid from "  . $validFrom . "\n";
				} 
				$validTo = date('Y-m-d H:i:s', $data['validTo_time_t']);
				if(time() > $data['validTo_time_t']){
					$testOutput['ssl_cert_error'] = "Application cert cert expired - cert valid to "  . $validFrom . "\n";
				} 
			}
			if(!file_exists($this->config['rsa_private_key'])) $testOutput['rsa_cert_error'] = "Can't read the self-signed cert key. Check your rsa_private_key config variable. Private and Partner API applications require a self-signed X509 cert http://developer.xero.com/api-overview/setup-an-application/#certs \n";
			if(file_exists($this->config['rsa_private_key'])){
				if(!openssl_x509_check_private_key($this->config['rsa_public_key'], $this->config['rsa_private_key'])) $testOutput['rsa_cert_error'] = "Application certificate and key do not match \n";;
			}
			
		}
  			
  			
  		}
  
		return 	$testOutput;	
				
  }
    
    }
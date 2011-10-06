<?php
/**
 * An example script for the XeroOAuth class
 *
 * @author Ronan Quirke <network@xero.com>
 */
require 'OAuthSimple.php';
require 'XeroOAuth.php';
require_once('_config.php');
$oauthObject = new OAuthSimple();

// As this is an example, I am not doing any error checking to keep 
// things simple.  Initialize the output in case we get stuck in
// the first step.
session_start();
$output = 'Authorizing...';


# Set some standard curl options....
		$options[CURLOPT_VERBOSE] = 1;
    	$options[CURLOPT_RETURNTRANSFER] = 1;
    	$options[CURLOPT_SSL_VERIFYHOST] = 0;
    	$options[CURLOPT_SSL_VERIFYPEER] = 0;
    	$useragent = isset($useragent) ? USER_AGENT : 'XeroOAuth-PHP';
    	$options[CURLOPT_USERAGENT] = $useragent;
    	//curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
                     
switch (XRO_APP_TYPE) {
    case "Private":
        $xro_settings = $xro_private_defaults;
        $_GET['oauth_verifier'] = 1;
       	$_COOKIE['oauth_token_secret'] =  $signatures['shared_secret'];
       	$_GET['oauth_token'] =  $signatures['consumer_key'];
        break;
    case "Public":
        $xro_settings = $xro_defaults;
        break;
    case "Partner":
        $xro_settings = $xro_partner_defaults;
        break;
    case "Partner_Mac":
        $xro_settings = $xro_partner_mac_defaults;
        break;
}
          
// bypass if we have an active session
if ($_SESSION&&$_REQUEST['start']==1) {

	$signatures['oauth_token'] = $_SESSION['access_token'];
    $signatures['oauth_secret'] = $_SESSION['access_token_secret'];
    $signatures['oauth_session_handle'] = $_SESSION['oauth_session_handle'];
    //////////////////////////////////////////////////////////////////////
    
     if (!empty($_REQUEST['endpoint'])){
    // Example Xero API Access:
    $oauthObject->reset();
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['xero_url'].'/'.$_REQUEST['endpoint'].'/',
        //'parameters'=> array('Where' => 'Type%3d%3d%22BANK%22'),
        'parameters'=> array(
			'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));
	$ch = curl_init();
	curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);
	curl_close($ch);
	
	parse_str($r, $returned_items);		   
	$oauth_problem = $returned_items['oauth_problem'];
		if($oauth_problem){
			session_destroy();
		}
	
	echo 'CURL RESULT: <textarea cols="160" rows="40">' . $r . '</textarea><br/>';
	}
	
	if (!empty($_REQUEST['put'])){
    // Example Xero API PUT:
    $oauthObject->reset();
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['xero_url'].'/Invoices/',
        'action'	=> 'PUT',
        'parameters'=> array(
			'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));
        
    $xml = "<Invoices>
  <Invoice>
    <Type>ACCREC</Type>
    <Contact>
      <Name>Martin Hudson</Name>
    </Contact>
    <Date>2011-10-01T00:00:00</Date>
    <DueDate>2011-10-08T00:00:00</DueDate>
    <InvoiceNumber>ORC1042</InvoiceNumber>
    <LineAmountTypes>Exclusive</LineAmountTypes>
    <LineItems>
      <LineItem>
        <Description>Monthly rental for property at 56a Wilkins Avenue</Description>
        <Quantity>4.3400</Quantity>
        <UnitAmount>395.00</UnitAmount>
        <AccountCode>200</AccountCode>
      </LineItem>
    </LineItems>
  </Invoice>
</Invoices>";
	$fh  = fopen('php://memory', 'w+');
	fwrite($fh, $xml);
	rewind($fh);
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_INFILE, $fh);
	curl_setopt($ch, CURLOPT_INFILESIZE, strlen($xml));
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);
	curl_close($ch);
	
	parse_str($r, $returned_items);		   
	$oauth_problem = $returned_items['oauth_problem'];
		if($oauth_problem){
			session_destroy();
		}
	
	echo 'CURL RESULT: <textarea cols="160" rows="40">' . $r . '</textarea><br/>';
	}
	
	if (!empty($_REQUEST['post'])){
    // Example Xero API Post - update invoice to Authorised status
    $oauthObject->reset();
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['xero_url'].'/Invoices/',
        'action'	=> 'POST',
        'parameters'=> array(
			'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));
        
    $xml = "<Invoices>
  <Invoice>
    <Type>ACCREC</Type>
    <Contact>
      <Name>Martin Hudson</Name>
    </Contact>
    <Date>2011-10-01T00:00:00</Date>
    <DueDate>2011-10-08T00:00:00</DueDate>
    <InvoiceNumber>ORC1042</InvoiceNumber>
     <Status>AUTHORISED</Status>
    <LineAmountTypes>Exclusive</LineAmountTypes>
    <LineItems>
      <LineItem>
        <Description>Monthly rental for property at 56a Wilkins Avenue</Description>
        <Quantity>4.3400</Quantity>
        <UnitAmount>395.00</UnitAmount>
        <AccountCode>200</AccountCode>
      </LineItem>
    </LineItems>
  </Invoice>
</Invoices>";
	$fh  = fopen('php://memory', 'w+');
	fwrite($fh, $xml);
	rewind($fh);
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_INFILE, $fh);
	curl_setopt($ch, CURLOPT_INFILESIZE, strlen($xml));
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);
	curl_close($ch);
	
	parse_str($r, $returned_items);		   
	$oauth_problem = $returned_items['oauth_problem'];
		if($oauth_problem){
			session_destroy();
		}
	
	echo 'CURL RESULT: <textarea cols="160" rows="40">' . $r . '</textarea><br/>';
	}
	
	// Example Xero API AccessToken swap:
	if (!empty($_REQUEST['action'])){
		$oauthObject->reset();
    	$result = $oauthObject->sign(array(
        	'path'      => $xro_settings['site'].$xro_consumer_options['access_token_path'],
        	'parameters'=> array(
            'scope'         => $xro_settings['xero_url'],
            'oauth_session_handle'	=> $signatures['oauth_session_handle'],
            'oauth_token'	=> $signatures['oauth_token'],
            'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));
	$ch = curl_init();
	curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);
	parse_str($r, $returned_items);		   
	$_SESSION['access_token'] = $returned_items['oauth_token'];
	$_SESSION['access_token_secret']   = $returned_items['oauth_token_secret'];
	$_SESSION['oauth_session_handle']   = $returned_items['oauth_session_handle'];
	if($returned_items['oauth_token']){
		echo "Refresh successful - new token: " . $returned_items['oauth_token'] . "<br/>";
		}
	curl_close($ch);
	}
	
}else{

// In step 3, a verifier will be submitted.  If it's not there, we must be
// just starting out. Let's do step 1 then.
if (!isset($_GET['oauth_verifier'])) {
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 1: Get a Request Token
    //
    // Get a temporary request token to facilitate the user authorization 
    // in step 2. We make a request to the OAuthGetRequestToken endpoint,
    // submitting the scope of the access we need (in this case, all the 
    // user's calendars) and also tell Google where to go once the token
    // authorization on their side is finished.
    //
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['site'].$xro_consumer_options['request_token_path'],
        'parameters'=> array(
            'scope'         => $xro_settings['xero_url'],
            'oauth_callback'	=> OAUTH_CALLBACK,
            'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));

    // The above object generates a simple URL that includes a signature, the 
    // needed parameters, and the web page that will handle our request.  I now
    // "load" that web page into a string variable.
    $ch = curl_init();
    
	curl_setopt_array($ch, $options);

    if(isset($_GET['debug'])){
    	echo 'signed_url: ' . $result['signed_url'] . '<br/>';
    }
    
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    $r = curl_exec($ch);
    if(isset($_GET['debug'])){
    echo 'CURL ERROR: ' . curl_error($ch) . '<br/>';
    }

    curl_close($ch);

	if(isset($_GET['debug'])){
    echo 'CURL RESULT: ' . print_r($r) . '<br/>';
    }
    // We parse the string for the request token and the matching token
    // secret. Again, I'm not handling any errors and just plough ahead 
    // assuming everything is hunky dory.
    parse_str($r, $returned_items);
    $request_token = $returned_items['oauth_token'];
    $request_token_secret = $returned_items['oauth_token_secret'];

	 if(isset($_GET['debug'])){
    echo 'request_token: ' . $request_token . '<br/>';
    }
    
    // We will need the request token and secret after the authorization.
    // Google will forward the request token, but not the secret.
    // Set a cookie, so the secret will be available once we return to this page.
    setcookie("oauth_token_secret", $request_token_secret, time()+3600);
    //
    //////////////////////////////////////////////////////////////////////
    
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 2: Authorize the Request Token
    //
    // Generate a URL for an authorization request, then redirect to that URL
    // so the user can authorize our access request.  The user could also deny
    // the request, so don't forget to add something to handle that case.
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['authorize_url'],
        'parameters'=> array(
            'oauth_token' => $request_token,
            'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));

    // See you in a sec in step 3.
    if(isset($_GET['debug'])){
    echo 'signed_url: ' . $result[signed_url];
    }else{
    header("Location:$result[signed_url]");
    }
    exit;
    //////////////////////////////////////////////////////////////////////
}
else {
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 3: Exchange the Authorized Request Token for an
    //         Access Token.
    //
    // We just returned from the user authorization process on Google's site.
    // The token returned is the same request token we got in step 1.  To 
    // sign this exchange request, we also need the request token secret that
    // we baked into a cookie earlier. 
    //

    // Fetch the cookie and amend our signature array with the request
    // token and secret.
    $signatures['oauth_secret'] = $_COOKIE['oauth_token_secret'];
    $signatures['oauth_token'] = $_GET['oauth_token'];
    
    // only need to do this for non-private apps
    if(XRO_APP_TYPE!='Private'){
	// Build the request-URL...
	$result = $oauthObject->sign(array(
		'path'		=> $xro_settings['site'].$xro_consumer_options['access_token_path'],
		'parameters'=> array(
			'oauth_verifier' => $_GET['oauth_verifier'],
			'oauth_token'	 => $_GET['oauth_token'],
			'oauth_signature_method' => $xro_settings['signature_method']),
		'signatures'=> $signatures));

	// ... and grab the resulting string again. 
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);

	// Voila, we've got an access token.
	parse_str($r, $returned_items);		   
	$access_token = $returned_items['oauth_token'];
	$access_token_secret = $returned_items['oauth_token_secret'];
	$oauth_session_handle = $returned_items['oauth_session_handle'];
    }else{
    $access_token = $signatures['oauth_token'];
	$access_token_secret = $signatures['oauth_secret'];
    }
    
    // We can use this long-term access token to request Google API data,
    // for example, a list of calendars. 
    // All Google API data requests will have to be signed just as before,
    // but we can now bypass the authorization process and use the long-term
    // access token you hopefully stored somewhere permanently.
    $signatures['oauth_token'] = $access_token;
    $signatures['oauth_secret'] = $access_token_secret;
    $signatures['oauth_session_handle'] = $oauth_session_handle;
    //////////////////////////////////////////////////////////////////////
    
    // Example Xero API Access:
    // This will build a link to an RSS feed of the users calendars.
    $oauthObject->reset();
    $result = $oauthObject->sign(array(
        'path'      => $xro_settings['xero_url'].'/Organisation/',
        //'parameters'=> array('Where' => 'Type%3d%3d%22BANK%22'),
        'parameters'=> array(
			'oauth_signature_method' => $xro_settings['signature_method']),
        'signatures'=> $signatures));

    // Instead of going to the list, I will just print the link along with the 
    // access token and secret, so we can play with it in the sandbox:
    // http://googlecodesamples.com/oauth_playground/
    //
    $ch = curl_init();
	curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
	$r = curl_exec($ch);
    echo "REQ URL" . $result['signed_url'];
    // start a session to show how we could use this in an app
    $_SESSION['access_token'] = $access_token;
	$_SESSION['access_token_secret']   = $access_token_secret;
	$_SESSION['oauth_session_handle']   = $oauth_session_handle;
	$_SESSION['time']     = time();

    $output = "<p>Access Token: ". $_SESSION['access_token'] ."<BR>
                  Token Secret: ". $_SESSION['access_token_secret'] . "<BR>
                  Session Handle: ". $_SESSION['oauth_session_handle'] ."</p>
               <p><a href=''>GET Accounts</a></p>";
               echo 'CURL RESULT: <textarea cols="160" rows="40">' . $r . '</textarea><br/>';
    curl_close($ch);
}     

}
?>
<HTML>
<BODY>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Accounts&start=1">Accounts</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Organisation&start=1">Organisation</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Invoices&start=1">Invoices</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Contacts&start=1">Contacts</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Currencies&start=1">Currencies</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=TrackingCategories&start=1">TrackingCategories</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?endpoint=Journals&start=1">Journals</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?action=ChangeToken&start=1">Token Refresh</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?put=put&start=1">PUT Invoice</a><br/>
<a href="<?php echo $_SERVER['PHP_SELF'] . SID ?>?post=post&start=1">POST Invoice update</a><br/>
</BODY>
</HTML>

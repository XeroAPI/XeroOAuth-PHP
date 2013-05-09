XeroOAuth-PHP
-----------------------

PHP library for working with the Xero OAuth API.

Intro
======
XeroOAuth-PHP is a sample library for use with the Xero API (<http://developer.xero.com>). The Xero API uses OAuth 1.0a, but we would not recommend using this library for other OAuth 1.0a APIs as
the Xero API has one of the more advanced implementations (RSA-SHA1, client ssl certs etc) and thus has many configuration options not typically used in other APIs.

This library is designed to get a developer up and running quickly with the OAuth authentication layer, but there will be some customisation of its implementation required before it can be used in a
production environment.

## Setup
To get setup, you will need to modify the values in the _config.php file to your own requirements and application settings
Special options for Partner applications - should be commented out for non-partner applications

## Usage

There are a number of functions you will find useful.

#### Make a request
The request function lies at the core of any communication with the API. There are a number of types of requests you may wish to make, all handled by the request() function.

    request($method, $url, $parameters, $xml, $format)

###### Parameters
* Method: the API method to be used (GET, PUT, POST)
* URL: the URL of the API endpoint. This is handled by a special function (see below)
* Parameters: an associative array of parameters such as where, order by etc (see <http://developer.xero.com/api-overview/http-requests-and-responses/>)
* XML: request data (for PUT and POST operations)
* Format: response format (currently xml, json & pdf are supported). Note that PDF is not supported for all endpoints

#### Generate a URL
For partner API applications where the 30 minute access tokens can be programatically refreshed via the API, you can use the refreshToken function:

    url($endpoint, $api)

###### Parameters
* Endpoint: the endpoint you wish to work with. Note there are OAuth endpoints such as 'RequestToken' and 'AccessToken' in addition to various API endpoints such as Invoices, Contacts etc. When specifying a resource, such as Invoices/$GUID, you can construct the request by appending the GUID to the base URL.
* API: there are two APIs: core (core accounting API) and payroll (payroll application API). Default is core.

#### Parse the response
Once you get data back, you can pass it through the parseResponse function to turn it into something usable.

    parseResponse($response, $format)

###### Parameters
* Response: the raw API response to be parsed
* Format: xml pdf and json are supported, but you cannot use this function to parse an XML API response as JSON - must correspond to the requested response format.


#### Refresh an access token
For partner API applications where the 30 minute access tokens can be programatically refreshed via the API, you can use the refreshToken function:

    refreshToken('the access token', 'the session handle')

###### Parameters
* Access token: the current access token
* Session handle: the session identifier handle

## Debug
If you append: ?debug=1 to example.php so you have /example.php?debug=1
- this will output some debug information
- this will include a "CURL ERROR:" line
- under this, if you are getting any errors it should provide this in the returned oauth_problem and oauth_problem_advice parameters - the error messages should be quite self explanatory
- if there are no errors, you should just see oauth_token and oauth_token_secret parameters returned, indicating all is ok


## Response Helpers
Understanding the type of message you are getting from the API could be useful. In each response that is not successful, a helper element is returned:

* **TokenExpired:**  This means that the access token has expired. If you are using a partner API type application, you can renew it automatically, or if using a public application, prompt the user to re-authenticate
* **TokenFatal:** In this scenario, a token is in a state that it cannot be renewed, and the user will need to re-authenticate
* **SetupIssue:** There is an issue within the setup/configuration of the connection - check the diagnostics function
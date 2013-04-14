XeroOAuth-PHP
-----------------------

PHP library for working with the Xero OAuth API.

Intro
======
TODO

## Setup
To get setup, you will need to modify the values in the _config.php file to your own requirements and application settings
Special options for Partner applications - should be commented out for non-partner applications

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
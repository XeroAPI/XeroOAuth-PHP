<?php

if ( isset($_REQUEST['wipe'])) {
  session_destroy();
  header("Location: {$here}");

// already got some credentials stored?
} elseif(isset($_REQUEST['refresh'])) {
    $response = $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['oauth_session_handle']);
    if ($XeroOAuth->response['code'] == 200) {
        $session = persistSession($response);
        $oauthSession = retrieveSession();
    } else {
        outputError($XeroOAuth);
        if ($XeroOAuth->response['helper'] == "TokenExpired") $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
    }

} elseif ( isset($oauthSession['oauth_token']) && isset($_REQUEST) ) {

    $XeroOAuth->config['access_token']  = $oauthSession['oauth_token'];
    $XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
    $XeroOAuth->config['session_handle'] = $oauthSession['oauth_session_handle'];


    if (isset($_REQUEST['accounts'])) {
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => $_REQUEST['where']));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            echo "There are " . count($accounts->Accounts[0]). " accounts in this Xero organisation, the first one is: </br>";
            pr($accounts->Accounts[0]->Account);
        } else {
            outputError($XeroOAuth);
        }
    }

    if (isset($_REQUEST['accountsfilter'])) {
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => 'Type=="BANK"'));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            echo "There are " . count($accounts->Accounts[0]). " accounts in this Xero organisation, the first one is: </br>";
            pr($accounts->Accounts[0]->Account);
        } else {
            outputError($XeroOAuth);
        }
    }
    if (isset($_REQUEST['payrollemployees'])) {
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('Employees', 'payroll'), array());
        if ($XeroOAuth->response['code'] == 200) {
            $employees = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            echo "There are " . count($employees->Employees[0]). " employees in this Xero organisation, the first one is: </br>";
            pr($employees->Employees[0]->Employee);
        } else {
            outputError($XeroOAuth);
        }
    }
    if (isset($_REQUEST['payruns'])) {
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('PayRuns', 'payroll'), array('Where' => $_REQUEST['where']));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            echo "There are " . count($accounts->PayRuns[0]). " PayRuns in this Xero organisation, the first one is: </br>";
            pr($accounts->PayRuns[0]->PayRun);
        } else {
            outputError($XeroOAuth);
        }
    }
    if (isset($_REQUEST['superfundproducts'])) {
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('SuperFundProducts', 'payroll'), array('ABN' => $_REQUEST['where']));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            echo "There are " . count($accounts->SuperFundProducts[0]). " SuperFundProducts in this Xero organisation, the first one is: </br>";
            pr($accounts->SuperFundProducts[0]->SuperFundProduct[0]);
        } else {
            outputError($XeroOAuth);
        }
    }
    if (isset($_REQUEST['invoice'])) {
        if (!isset($_REQUEST['method'])) {
            $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices', 'core'), array());
            if ($XeroOAuth->response['code'] == 200) {
                $invoices = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "There are " . count($invoices->Invoices[0]). " invoices in this Xero organisation, the first one is: </br>";
                pr($invoices->Invoices[0]->Invoice);
                if ($_REQUEST['invoice']=="pdf") {
                    $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoice/'.$invoices->Invoices[0]->Invoice->InvoiceID, 'core'), array(), "", 'pdf');
                    $myFile = $invoices->Invoices[0]->Invoice->InvoiceID.".pdf";
                    $fh = fopen($myFile, 'w') or die("can't open file");
                    fwrite($fh, $XeroOAuth->response['response']);
                    fclose($fh);
                    echo "PDF copy downloaded, check your the directory of this script.</br>";
                }
            } else {
                outputError($XeroOAuth);
            }
        } elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "put" ) {
            $xml = "<Invoices>
                      <Invoice>
                        <Type>ACCREC</Type>
                        <Contact>
                          <Name>Martin Hudson</Name>
                        </Contact>
                        <Date>2013-05-13T00:00:00</Date>
                        <DueDate>2013-05-20T00:00:00</DueDate>
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
            $response = $XeroOAuth->request('PUT', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
            if ($XeroOAuth->response['code'] == 200) {
                $invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "" . count($invoice->Invoices[0]). " invoice created in this Xero organisation.";
                if (count($invoice->Invoices[0])>0) {
                    echo "The first one is: </br>";
                    pr($invoice->Invoices[0]->Invoice);
                }
            } else {
                outputError($XeroOAuth);
            }
        } elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "post" ) {
            $xml = "<Invoices>
                      <Invoice>
                        <Type>ACCREC</Type>
                        <Contact>
                          <Name>Martin Hudson</Name>
                        </Contact>
                        <Date>2013-05-13T00:00:00</Date>
                        <DueDate>2013-05-20T00:00:00</DueDate>
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
            $response = $XeroOAuth->request('PUT', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
            if ($XeroOAuth->response['code'] == 200) {
                $invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "" . count($invoice->Invoices[0]). " invoice created in this Xero organisation.";
                if (count($invoice->Invoices[0])>0) {
                    echo "The first one is: </br>";
                    pr($invoice->Invoices[0]->Invoice);
                }
            } else {
                outputError($XeroOAuth);
            }
        }


    }
    if (isset($_REQUEST['invoicesfilter'])) {
       $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices', 'core'), array('Where' => 'Contact.Name.Contains("Martin")'));
       if ($XeroOAuth->response['code'] == 200) {
           $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
           echo "There are " . count($accounts->Invoices[0]). " matching invoices in this Xero organisation, the first one is: </br>";
           pr($accounts->Invoices[0]->Invoice);
       } else {
           outputError($XeroOAuth);
       }
   }
   if (isset($_REQUEST['banktransactions'])) {
       if (!isset($_REQUEST['method'])) {
           $response = $XeroOAuth->request('GET', $XeroOAuth->url('BankTransactions', 'core'), array(), "", "xml");
           if ($XeroOAuth->response['code'] == 200) {
               $banktransactions = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
               echo "There are " . count($banktransactions->BankTransactions[0]). " bank transactions in this Xero organisation.";
               if (count($banktransactions->BankTransactions[0])>0) {
                   echo "The first one is: </br>";
                   pr($banktransactions->BankTransactions[0]->BankTransaction);
               }
           } else {
               outputError($XeroOAuth);
           }
       } elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "put" ) {
           $xml = "<BankTransactions>
                     <BankTransaction>
                     <Type>SPEND</Type>
                     <Contact>
                       <Name>Westpac</Name>
                     </Contact>
                     <Date>2013-04-16T00:00:00</Date>
                     <LineItems>
                       <LineItem>
                         <Description>Yearly Bank &amp; Account Fee</Description>
                         <Quantity>1.0000</Quantity>
                         <UnitAmount>20.00</UnitAmount>
                         <AccountCode>400</AccountCode>
                      </LineItem>
                    </LineItems>
                    <BankAccount>
                      <Code>090</Code>
                    </BankAccount>
                  </BankTransaction>
                </BankTransactions>";
           $response = $XeroOAuth->request('PUT', $XeroOAuth->url('BankTransactions', 'core'), array(), $xml);
           if ($XeroOAuth->response['code'] == 200) {
               $banktransactions = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
               echo "There are " . count($banktransactions->BankTransactions[0]). " successful bank transaction(s) created in this Xero organisation.";
               if (count($banktransactions->BankTransactions[0])>0) {
                   echo "The first one is: </br>";
                   pr($banktransactions->BankTransactions[0]->BankTransaction);
               }
           } else {
               outputError($XeroOAuth);
           }
       }
   }

   if( isset($_REQUEST['contacts'])) {
       if (!isset($_REQUEST['method'])) {
           $response = $XeroOAuth->request('GET', $XeroOAuth->url('Contacts', 'core'), array());
           if ($XeroOAuth->response['code'] == 200) {
               $contacts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
               echo "There are " . count($contacts->Contacts[0]). " contacts in this Xero organisation, the first one is: </br>";
               pr($contacts->Contacts[0]->Contact);

           } else {
               outputError($XeroOAuth);
           }
       } elseif(isset($_REQUEST['method']) && $_REQUEST['method'] == "post" ){
           $xml = "<Contacts>
                     <Contact>
                       <Name>Jameson + Co</Name>
                       <EmailAddress>emailaddress@yourdomain.com</EmailAddress>
                       <SkypeUserName>Sîne klâwen durh die wolken sint geslagen</SkypeUserName>
                       <FirstName>Chester</FirstName>
                       <LastName>d’Arenberg</LastName>
                     </Contact>
                   </Contacts>";
           $response = $XeroOAuth->request('POST', $XeroOAuth->url('Contacts', 'core'), array(), $xml);
           if ($XeroOAuth->response['code'] == 200) {
               $contact = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
               echo "" . count($contact->Contacts[0]). " contact created in this Xero organisation.";
               if (count($contact->Contacts[0])>0) {
                   echo "The first one is: </br>";
                   pr($contact->Contacts[0]->Contact);
               }
           } else {
               outputError($XeroOAuth);
           }
       }
   }


   if (isset($_REQUEST['organisation'])) {
       $response = $XeroOAuth->request('GET', $XeroOAuth->url('Organisation', 'core'), array('page' => 0));
       if ($XeroOAuth->response['code'] == 200) {
           $organisation = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
           echo "Organisation name: " . $organisation->Organisations[0]->Organisation->Name;
       } else {
           outputError($XeroOAuth);
       }
   }

   if (isset($_REQUEST['trialbalance'])) {
       $response = $XeroOAuth->request('GET', $XeroOAuth->url('Reports/TrialBalance', 'core'), array('page' => 0));
       if ($XeroOAuth->response['code'] == 200) {
           $report = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
           echo "Organisation name: " . $report->Organisations[0]->Organisation->Name;
       } else {
           outputError($XeroOAuth);
       }
   }

}

<?php

use Concise\TestCase;

class XeroOAuthTest extends TestCase
{
    public function testAutoloaderIsWorking()
    {
        $this->assert(new XeroOAuth(array(
            'application_type' => 'Public'
        )), is_not_null);
    }
}

<?php

use Concise\TestCase;

class XeroOAuthExceptionTest extends TestCase
{
    public function testAutoloadCanFindClass()
    {
        $this->assert(new XeroOAuthException(), is_not_null);
    }
}

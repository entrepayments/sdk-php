<?php
namespace EntrePayments;

use PHPUnit\Framework\TestCase;

class MerchantTest extends TestCase
{
    /**
     * @testdox We should be able to create a new merchant and set its credentials.
     */
    public function testMerchantCreation(): void
    {
        $merchantCode = 'merchantCode';
        $merchantKey  = '123';
        $terminal     = '001';
        $merchant     = new Merchant($merchantKey, $merchantCode, $terminal);

        $this->assertEquals($merchantCode, $merchant->getMerchantCode());
        $this->assertEquals($merchantKey, $merchant->getMerchantKey());
        $this->assertEquals($terminal, $merchant->getTerminal());
    }
}

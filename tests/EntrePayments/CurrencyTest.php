<?php

namespace EntrePayments;

use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @testdox When getting a constant from Currency, it SHOULD BE in the allCurrencies method.
     */
    public function testAllCurrencies(): void
    {
        $allCurrencies = Currency::allCurrencies();

        $this->assertContains(Currency::BRL, $allCurrencies);
        $this->assertNotContains(-1, $allCurrencies);
    }

    /**
     * @testdox When verifying a constant from Currency, it SHOULD BE valid.
     */
    public function testIsValid(): void
    {
        $this->assertEquals(true, Currency::isValid(Currency::BRL));
        $this->assertEquals(false, Currency::isValid(-1));
    }
}

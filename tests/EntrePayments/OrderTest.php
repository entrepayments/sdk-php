<?php

namespace EntrePayments;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @testdox The API expect the order amount as integer
     */
    public function testFloatToIntConversion(): void
    {
        $number   = '123';
        $amount   = 624.80;
        $currency = Currency::BRL;
        $order    = new Order($number, $amount, $currency);

        $this->assertEquals(62480, $order->getAmount());
        $this->assertEquals($number, $order->getNumber());
        $this->assertEquals($currency, $order->getCurrency());
    }

    /**
     * @testdox When passing an invalid currency to Order, it SHOULD THROW an exception.
     */
    public function testCurrencyValidity(): void
    {
        $number   = '123';
        $amount   = 624.80;
        $currency = Currency::BRL;
        $order    = new Order($number, $amount, $currency);

        $this->assertEquals(Currency::BRL, $order->getCurrency());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency provided.');

        $currency = -1;

        new Order($number, $amount, $currency);
    }

    /**
     * @testdox If the product description is set, we SHOULD GET the same value set.
     */
    public function testSettingDescription(): void
    {
        $number      = '123';
        $amount      = 624.80;
        $currency    = Currency::BRL;
        $description = 'test 123';
        $order       = new Order($number, $amount, $currency);

        $order->setDescription($description);

        $this->assertEquals($description, $order->getDescription());
    }
}

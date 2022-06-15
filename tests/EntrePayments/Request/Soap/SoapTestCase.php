<?php

namespace EntrePayments\Request\Soap;

use EntrePayments\Card;
use EntrePayments\Merchant;
use EntrePayments\Order;
use EntrePayments\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SoapTestCase extends TestCase
{
    /**
     * @var MockObject&SoapEnvironment
     */
    protected SoapEnvironment $environment;
    protected Merchant $merchant;
    protected ?Payment $payment = null;

    public function setUp(): void
    {
        $this->merchant    = new Merchant('123', '123', '123');
        $this->environment = $this->getMockBuilder(SoapEnvironment::class)
                                  ->disableOriginalConstructor()
                                  ->onlyMethods(['sendMessage', 'getMerchant'])
                                  ->getMock();

        $this->environment->method('getMerchant')
                          ->willReturn($this->merchant);

        $this->payment = new Payment(
            new Order(
                '123test',
                1
            ),
            Card::creditCard(
                '4516492462175496',
                '22',
                '12',
                'Fulano de tal',
                '626'
            )
        );
    }

    /**
     * @param Payment $expected
     * @param Payment $actual
     */
    protected function assertExpectations(Payment $expected, Payment $actual): void
    {
        $this->assertEquals($expected->getOrder()->getAmount(), $actual->getOrder()->getAmount());
        $this->assertEquals($expected->getOrder()->getNumber(), $actual->getOrder()->getNumber());
        $this->assertEquals($expected->getOrder()->getCurrency(), $actual->getOrder()->getCurrency());
        $this->assertEquals($expected->getCard()->getPan(), $actual->getCard()->getPan());
        $this->assertEquals($expected->getCard()->getExpiration(), $actual->getCard()->getExpiration());
        $this->assertEquals($expected->getCard()->getCvv(), $actual->getCard()->getCvv());
        $this->assertEquals($expected->getInstallments(), $actual->getInstallments());
    }
}

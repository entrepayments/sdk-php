<?php

namespace EntrePayments;

use EntrePayments\Request\Environment;
use EntrePayments\Request\Soap\SoapEnvironment;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntrePaymentsTest extends TestCase
{
    /**
     * @var MockObject&Environment
     */
    private Environment $environment;
    private Payment $payment;
    private EntrePayments $entrePayments;

    public function setUp(): void
    {
        $this->payment = (new Payment(
            new Order(
                '0311183709',
                30
            ),
            Card::creditCard(
                '4548810000000003',
                '22',
                '12',
                'Fulano de tal',
                '123'
            )
        ))->setSoftDescriptor("TESTE");

        $this->environment   = $this->createMock(SoapEnvironment::class);
        $this->entrePayments = new EntrePayments($this->environment);
    }

    /**
     * @testdox The creational method WILL create a preconfigured instance of EntrePayments.
     */
    public function testCreationalMethod(): void
    {
        $merchant         = new Merchant('123', '123', '123');
        $entrePayments = EntrePayments::create($merchant, false);

        $this->assertEquals($merchant, $entrePayments->getEnvironment()->getMerchant());
        $this->assertEquals(false, $entrePayments->getEnvironment()->isProduction());

        $entrePayments = EntrePayments::create($merchant);
        $this->assertEquals(true, $entrePayments->getEnvironment()->isProduction());
    }

    /**
     * @testdox If no second argument is passed, capture MUST TO BE true.
     * @throws Exception
     */
    public function testAuthorizeWithoutCaptureArgument(): void
    {
        $this->environment->expects($this->once())
                          ->method('authorize')
                          ->with($this->payment, true)
                          ->willReturn($this->payment);

        $this->entrePayments->authorize($this->payment);
    }

    /**
     * @testdox If the capture argument is passed, the environment will receive THE SAME argument.
     * @throws Exception
     */
    public function testAuthorizeWithCaptureArgument(): void
    {
        $this->environment->expects($this->once())
                          ->method('authorize')
                          ->with($this->payment, false)
                          ->willReturn($this->payment);

        $this->entrePayments->authorize($this->payment, false);
    }

    /**
     * @testdox If the EntrePayments::consult is called, the Environment::consult MUST TO BE called too.
     * @throws Exception
     */
    public function testConsult(): void
    {
        $this->environment->expects($this->once())
                          ->method('consult')
                          ->with($this->payment)
                          ->willReturn($this->payment);

        $this->entrePayments->consult($this->payment);
    }

    /**
     * @testdox If the GlobalPagamentos::recurring is called, the Environment::authorize MUST TO BE called with
     *     recurring argument set to true
     * @throws Exception
     */
    public function testRecurring(): void
    {
        $this->environment->expects($this->once())
                          ->method('authorize')
                          ->with($this->payment, true, true)
                          ->willReturn($this->payment);

        $this->entrePayments->recurring($this->payment);
    }

    /**
     * @testdox When calling ZeroDolar, a new Payment will be created with zero dolar amount
     * @throws Exception
     */
    public function testZeroDolar(): void
    {
        $this->environment->expects($this->once())
                          ->method('authorize')
                          ->with($this->payment, true);

        $payment = $this->entrePayments->zeroDolar($this->payment);

        $this->assertEquals(0, $payment->getOrder()->getAmount());
    }

    /**
     * @testdox If the GlobalPagamentos::capture is called, the Environment::capture MUST TO BE called too.
     * @throws Exception
     */
    public function testCapture(): void
    {
        $this->environment->expects($this->once())
                          ->method('capture')
                          ->with($this->payment)
                          ->willReturn($this->payment);

        $this->entrePayments->capture($this->payment);
    }

    /**
     * @testdox If the GlobalPagamentos::cancel is called, the Environment::cancel MUST TO BE called too.
     * @throws Exception
     */
    public function testCancel(): void
    {
        $this->environment->expects($this->once())
                          ->method('cancel')
                          ->with($this->payment)
                          ->willReturn($this->payment);

        $this->entrePayments->cancel($this->payment);
    }

    /**
     * @testdox If the GlobalPagamentos::void is called, the Environment::cancel MUST TO BE called.
     * @throws Exception
     */
    public function testVoid(): void
    {
        $this->environment->expects($this->once())
                          ->method('cancel')
                          ->with($this->payment)
                          ->willReturn($this->payment);

        $this->entrePayments->void($this->payment);
    }
}

<?php

namespace EntrePayments;

use Exception;
use PHPUnit\Framework\TestCase;

class PaymentMethodsTest extends TestCase
{
    public const SOFT_DESCRIPTOR = 'SDK Test';

    private EntrePayments $entrePayments;
    private string $orderNumber;

    public function setUp(): void
    {
        $this->entrePayments = EntrePayments::create(
            new Merchant(
                'qwertyasdf0123456789',
                '012005541349001',
                '1'
            ),
            false
        );

        $this->generateOrderNumber();
    }

    private function generateOrderNumber(): void
    {
        $this->orderNumber = (string)time();
    }

    /**
     * @throws Exception
     */
    public function testAuthorize(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            )),
            capture: false
        );

        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testAuthorizeWithInstallments(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            ))->setInstallments(12),
            capture: false
        );

        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testCapture(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            )),
            capture: false
        );

        $this->assertEquals('0000', $payment->getResponse());

        $payment = $this->entrePayments->capture(
            payment: (new Payment(
                order: new Order(
                    $this->orderNumber,
                    1
                ),
                softDescriptor: self::SOFT_DESCRIPTOR
            ))
        );

        $this->assertEquals('0900', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testAuthorizeCapture(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            ))
        );

        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testAuthorizeCaptureAndInstallments(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            ))->setInstallments(12)
        );

        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testTokenize(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR,
            )),
            tokenize: true
        );

        $this->assertEquals('0000', $payment->getResponse());

        $token = $payment->getCard()->getIdentifier();

        $this->generateOrderNumber();

        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::token(
                    $token
                ),
                self::SOFT_DESCRIPTOR
            ))
        );

        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testRefund(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            ))
        );

        $this->assertEquals('0000', $payment->getResponse());

        $payment = $this->entrePayments->cancel(
            new Payment(
                new Order(
                    $this->orderNumber,
                    1
                )
            )
        );

        $this->assertEquals('0900', $payment->getResponse());
    }

    /**
     * @throws Exception
     */
    public function testConsult(): void
    {
        $payment = $this->entrePayments->authorize(
            payment: (new Payment(
                new Order(
                    $this->orderNumber,
                    1
                ),
                Card::creditCard(
                    '4761120000000148',
                    '34',
                    '12',
                    'Fulano de tal',
                    '123'
                ),
                self::SOFT_DESCRIPTOR
            ))
        );

        $this->assertEquals('0000', $payment->getResponse());

        $payment = $this->entrePayments->consult(
            payment: (new Payment(
                new Order(
                    $payment->getOrder()->getNumber(),
                    1
                )
            )),
            transactionType: Payment::AUTHORIZATION_WITHOUT_3DS
        );

        $this->assertEquals('0000', $payment->getResponse());
    }
}

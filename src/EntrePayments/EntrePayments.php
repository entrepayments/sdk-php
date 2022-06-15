<?php

namespace EntrePayments;

use EntrePayments\Request\Environment;
use EntrePayments\Request\Soap\SoapEnvironment;
use Exception;

class EntrePayments
{
    /**
     * The SDK Version
     */
    public const VERSION = '1.0.0';

    /**
     * The environment to be used
     *
     * @var Environment
     */
    private Environment $environment;

    /**
     * Construct the EntryPayments instance
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Creates an instance of EntryPayments
     *
     * @param Merchant $merchant
     * @param bool     $production
     *
     * @return EntrePayments
     */
    public static function create(Merchant $merchant, bool $production = true): EntrePayments
    {
        return new EntrePayments(
            $production ?
                SoapEnvironment::production($merchant) :
                SoapEnvironment::test($merchant)
        );
    }

    /**
     * Gets the current environment
     *
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Authorize a payment
     *
     * @param Payment $payment
     * @param bool    $capture
     * @param bool    $tokenize
     *
     * @return Payment
     * @throws Exception
     */
    public function authorize(Payment $payment, bool $capture = true, bool $tokenize = false): Payment
    {
        return $this->environment->authorize($payment, $capture, false, $tokenize);
    }

    /**
     * Consult a payment
     *
     * @param Payment $payment
     * @param string  $transactionType
     *
     * @return Payment
     * @throws Exception
     */
    public function consult(Payment $payment, string $transactionType = '1'): Payment
    {
        return $this->environment->consult($payment, $transactionType);
    }

    /**
     * Creates a recurring payment
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function recurring(Payment $payment): Payment
    {
        return $this->environment->authorize($payment, true, true);
    }

    /**
     * Creates a zero dolar transaction
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function zeroDolar(Payment $payment): Payment
    {
        $newPayment = clone $payment;
        $newPayment->getOrder()->setAmount(0);

        return $this->environment->authorize($newPayment, true);
    }

    /**
     * Captures a payment
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function capture(Payment $payment): Payment
    {
        return $this->environment->capture($payment);
    }

    /**
     * Cancel a payment
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function cancel(Payment $payment): Payment
    {
        return $this->environment->cancel($payment, false);
    }

    /**
     * Void a payment
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function void(Payment $payment): Payment
    {
        return $this->environment->cancel($payment, true);
    }
}

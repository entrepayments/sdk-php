<?php

namespace EntrePayments\Request\Soap;

use BadMethodCallException;
use EntrePayments\Payment;

abstract class SoapRequest
{
    /**
     * The SoapEnvironment
     *
     * @var SoapEnvironment
     */
    protected SoapEnvironment $soap;

    /**
     * Creates an instance of SoapRequest
     *
     * @param SoapEnvironment $soap
     */
    public function __construct(SoapEnvironment $soap)
    {
        $this->soap = $soap;
    }

    /**
     * Creates an authorization request
     *
     * @param Payment $payment
     * @param bool    $capture
     * @param bool    $recurring
     *
     * @return Payment
     */
    public function authorize(Payment $payment, bool $capture = true, bool $recurring = false): Payment
    {
        throw new BadMethodCallException('Method not implemented.');
    }

    /**
     * Creates a consultation request
     *
     * @param Payment $payment
     *
     * @return Payment
     */
    public function consult(Payment $payment): Payment
    {
        throw new BadMethodCallException('Method not implemented.');
    }

    /**
     * Creates a capture request
     *
     * @param Payment $payment
     *
     * @return Payment
     */
    public function capture(Payment $payment): Payment
    {
        throw new BadMethodCallException('Method not implemented.');
    }

    /**
     * Creates a cancellation request
     *
     * @param Payment $payment
     * @param bool    $preAuthorization
     *
     * @return Payment
     */
    public function cancel(Payment $payment, bool $preAuthorization = false): Payment
    {
        throw new BadMethodCallException('Method not implemented.');
    }
}

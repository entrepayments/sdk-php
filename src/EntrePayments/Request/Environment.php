<?php

namespace EntrePayments\Request;

use DOMException;
use EntrePayments\Merchant;
use EntrePayments\Payment;
use Exception;

/**
 * Defines an environment to be used to request the API.
 */
interface Environment
{
    /**
     * Is a production environment?
     *
     * @return bool
     */
    public function isProduction(): bool;

    /**
     * Gets the merchant of this environment
     *
     * @return Merchant
     */
    public function getMerchant(): Merchant;

    /**
     * Gets the SDK user agent
     *
     * @return string
     */
    public function getUserAgent(): string;

    /**
     * Creates an authorization
     *
     * @param Payment $payment
     * @param bool    $capture
     * @param bool    $recurring
     * @param bool    $tokenize
     *
     * @return Payment
     * @throws Exception
     */
    public function authorize(
        Payment $payment,
        bool $capture = true,
        bool $recurring = false,
        bool $tokenize = false
    ): Payment;

    /**
     * Creates a consultation
     *
     * @param Payment $payment
     * @param string  $transactionType
     *
     * @return Payment
     * @throws Exception
     */
    public function consult(Payment $payment, string $transactionType = '1'): Payment;

    /**
     * Creates a capture
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws Exception
     */
    public function capture(Payment $payment): Payment;

    /**
     * Creates a cancellation
     *
     * @param Payment $payment
     * @param bool    $preAuthorization
     *
     * @return Payment
     * @throws Exception
     */
    public function cancel(Payment $payment, bool $preAuthorization = false): Payment;
}

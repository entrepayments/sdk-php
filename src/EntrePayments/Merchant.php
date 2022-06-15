<?php

namespace EntrePayments;

class Merchant
{
    private string $merchantKey;
    private string $merchantCode;
    private string $terminal;

    /**
     * Creates a new merchant
     *
     * @param string $merchantKey
     * @param string $merchantCode
     * @param string $terminal
     */
    public function __construct(string $merchantKey, string $merchantCode, string $terminal)
    {
        $this->setMerchantKey($merchantKey);
        $this->setMerchantCode($merchantCode);
        $this->setTerminal($terminal);
    }

    /**
     * Gets the merchant key
     *
     * @return string
     */
    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }

    /**
     * Sets the merchant key
     *
     * @param string $merchantKey
     *
     * @return Merchant
     */
    final public function setMerchantKey(string $merchantKey): Merchant
    {
        $this->merchantKey = $merchantKey;

        return $this;
    }

    /**
     * Get the merchant code
     *
     * @return string
     */
    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    /**
     * Set the merchant code
     *
     * @param string $merchantCode
     *
     * @return Merchant
     */
    final public function setMerchantCode(string $merchantCode): Merchant
    {
        $this->merchantCode = $merchantCode;

        return $this;
    }

    /**
     * Get the terminal
     *
     * @return string
     */
    public function getTerminal(): string
    {
        return $this->terminal;
    }

    /**
     * Set the terminal
     *
     * @param string $terminal
     *
     * @return Merchant
     */
    final public function setTerminal(string $terminal): Merchant
    {
        $this->terminal = sprintf('%d', $terminal);

        return $this;
    }
}

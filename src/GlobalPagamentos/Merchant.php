<?php

namespace GlobalPagamentos;

class Merchant
{
    private string $merchantKey;
    private string $merchantCode;
    private string $terminal;

    public function __construct(string $merchantKey, string $merchantCode, string $terminal)
    {
        $this->setMerchantKey($merchantKey);
        $this->setMerchantCode($merchantCode);
        $this->setTerminal($terminal);
    }

    /**
     * @return string
     */
    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }

    /**
     * @param string $merchantKey
     *
     * @return Merchant
     */
    public function setMerchantKey(string $merchantKey): Merchant
    {
        $this->merchantKey = $merchantKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    /**
     * @param string $merchantCode
     *
     * @return Merchant
     */
    public function setMerchantCode(string $merchantCode): Merchant
    {
        $this->merchantCode = $merchantCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerminal(): string
    {
        return $this->terminal;
    }

    /**
     * @param string $terminal
     *
     * @return Merchant
     */
    public function setTerminal(string $terminal): Merchant
    {
        $this->terminal = $terminal;

        return $this;
    }
}

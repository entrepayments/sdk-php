<?php

namespace EntrePayments;

use EntrePayments\Request\Environment;

class Payment
{
    public const AUTHORIZATION_WITHOUT_3DS = 'A';
    public const AUTHORIZATION_WITH_3DS = '1';
    public const PRE_AUTHORIZATION = '1';
    public const AUTHORIZATION_CONFIRMATION = '2';
    public const CANCELMENT = '3';
    public const PRE_AUTHORIZATION_CANCELMENT = '9';

    private Order $order;
    private ?Card $card = null;
    private Environment $environment;
    private string $softDescriptor;
    private int $installments = 1;
    private bool $securePayment = false;
    private ?string $authenticationUrl = null;
    private ?string $authorizationCode = null;
    private ?string $language = null;
    private ?string $nsu = null;
    private ?string $processedPayMethod = null;
    private ?string $response = null;
    private ?string $respondeInt = null;
    private ?string $paRequest = null;
    private ?string $md = null;
    private ?string $merchantData = null;
    private ?string $state = null;
    private ?string $transactionType = null;

    public function __construct(Order $order, ?Card $card = null, string $softDescriptor = '')
    {
        $this->setOrder($order);
        $this->setCard($card);
        $this->setSoftDescriptor($softDescriptor);
    }

    /**
     * @return string|null
     */
    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    /**
     * @param string|null $transactionType
     *
     * @return Payment
     */
    public function setTransactionType(?string $transactionType): Payment
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     *
     * @return Payment
     */
    public function setState(?string $state): Payment
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMerchantData(): ?string
    {
        return $this->merchantData;
    }

    /**
     * @param string|null $merchantData
     *
     * @return Payment
     */
    public function setMerchantData(?string $merchantData): Payment
    {
        $this->merchantData = $merchantData;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMd(): ?string
    {
        return $this->md;
    }

    /**
     * @param string|null $md
     *
     * @return Payment
     */
    public function setMd(?string $md): Payment
    {
        $this->md = $md;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaRequest(): ?string
    {
        return $this->paRequest;
    }

    /**
     * @param string|null $paRequest
     *
     * @return Payment
     */
    public function setPaRequest(?string $paRequest): Payment
    {
        $this->paRequest = $paRequest;

        return $this;
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     *
     * @return Payment
     */
    public function setEnvironment(Environment $environment): Payment
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string|null $response
     *
     * @return Payment
     */
    public function setResponse(?string $response): Payment
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRespondeInt(): ?string
    {
        return $this->respondeInt;
    }

    /**
     * @param string|null $respondeInt
     *
     * @return Payment
     */
    public function setRespondeInt(?string $respondeInt): Payment
    {
        $this->respondeInt = $respondeInt;

        return $this;
    }

    /**
     * @return int
     */
    public function getInstallments(): int
    {
        return $this->installments;
    }

    /**
     * @param int $installments
     *
     * @return Payment
     */
    final public function setInstallments(int $installments): Payment
    {
        $this->installments = $installments;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecurePayment(): bool
    {
        return $this->securePayment;
    }

    /**
     * @param bool $securePayment
     *
     * @return Payment
     */
    final public function setSecurePayment(bool $securePayment): Payment
    {
        $this->securePayment = $securePayment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string $authorizationCode
     *
     * @return Payment
     */
    final public function setAuthorizationCode(string $authorizationCode): Payment
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return Payment
     */
    final public function setLanguage(string $language): Payment
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNsu(): ?string
    {
        return $this->nsu;
    }

    /**
     * @param string $nsu
     *
     * @return Payment
     */
    final public function setNsu(string $nsu): Payment
    {
        $this->nsu = $nsu;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard(): Card
    {
        if ($this->card == null) {
            $this->setCard(new Card());
        }

        return $this->card;
    }

    /**
     * @param Card|null $card
     *
     * @return Payment
     */
    final public function setCard(?Card $card): Payment
    {
        if ($card == null) {
            return $this;
        }

        $this->card = $card;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor(): string
    {
        return $this->softDescriptor;
    }

    /**
     * @param string $softDescriptor
     *
     * @return Payment
     */
    final public function setSoftDescriptor(string $softDescriptor): Payment
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return Payment
     */
    final public function setOrder(Order $order): Payment
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationUrl(): ?string
    {
        return $this->authenticationUrl;
    }

    /**
     * @param string|null $authenticationUrl
     *
     * @return Payment
     */
    public function setAuthenticationUrl(?string $authenticationUrl): Payment
    {
        $this->authenticationUrl = $authenticationUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProcessedPayMethod(): ?string
    {
        return $this->processedPayMethod;
    }

    /**
     * @param string|null $processedPayMethod
     *
     * @return Payment
     */
    public function setProcessedPayMethod(?string $processedPayMethod): Payment
    {
        $this->processedPayMethod = $processedPayMethod;

        return $this;
    }
}

<?php

namespace EntrePayments;

use InvalidArgumentException;

class Card
{
    public const CREDITCARD = 1;
    public const DEBITCARD = 2;
    public const USE_3DS = true;
    public const THREE_D_SECURE_VERSION_1 = 1;
    public const THREE_D_SECURE_VERSION_2 = 2;

    private ?int $accountType;
    private ?string $pan = null;
    private ?string $expiration = null;
    private ?string $holderName = null;
    private ?string $cvv = null;
    private bool $threeDSecure = false;
    private int $threeDSecureVersion = self::THREE_D_SECURE_VERSION_2;
    private ?int $cardBrand = null;
    private ?int $cardCountry = null;
    private ?string $identifier = null;

    /**
     * Card constructor.
     *
     * @param int|null    $accountType
     * @param string|null $pan
     * @param string|null $expirationYear
     * @param string|null $expirationMonth
     * @param string|null $holderName
     * @param string|null $cvv
     * @param string|null $identifier
     */
    public function __construct(
        ?int $accountType = null,
        ?string $pan = null,
        ?string $expirationYear = null,
        ?string $expirationMonth = null,
        ?string $holderName = null,
        ?string $cvv = null,
        ?string $identifier = null
    ) {
        $this->setAccountType($accountType);
        $this->setHolderName($holderName);
        $this->setPan($pan);
        $this->setExpiration($expirationYear, $expirationMonth);
        $this->setCvv($cvv);
        $this->setIdentifier($identifier);
    }

    /**
     * @param string $pan
     * @param string $expirationYear
     * @param string $expirationMonth
     * @param string $holderName
     * @param string $cvv
     *
     * @return Card
     */
    public static function creditCard(
        string $pan,
        string $expirationYear,
        string $expirationMonth,
        string $holderName,
        string $cvv
    ): Card {
        return new Card(
            self::CREDITCARD,
            $pan,
            $expirationYear,
            $expirationMonth,
            $holderName,
            $cvv
        );
    }

    public static function debitCard(
        string $pan,
        string $expirationYear,
        string $expirationMonth,
        string $holderName,
        string $cvv,
        int $threeDSecureVersion = self::THREE_D_SECURE_VERSION_2
    ): Card {
        $card = new Card(
            self::DEBITCARD,
            $pan,
            $expirationYear,
            $expirationMonth,
            $holderName,
            $cvv
        );

        $card->setThreeDSecureVersion($threeDSecureVersion);

        return $card;
    }

    public static function token(string $identifier): Card
    {
        return new Card(
            accountType: self::CREDITCARD,
            identifier: $identifier
        );
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string|null $identifier
     *
     * @return Card
     */
    public function setIdentifier(?string $identifier): Card
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCardBrand(): ?int
    {
        return $this->cardBrand;
    }

    /**
     * @param int|null $cardBrand
     *
     * @return Card
     */
    public function setCardBrand(?int $cardBrand): Card
    {
        $this->cardBrand = $cardBrand;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCardCountry(): ?int
    {
        return $this->cardCountry;
    }

    /**
     * @param int|null $cardCountry
     *
     * @return Card
     */
    public function setCardCountry(?int $cardCountry): Card
    {
        $this->cardCountry = $cardCountry;

        return $this;
    }

    /**
     * @return int
     */
    public function getThreeDSecureVersion(): int
    {
        return $this->threeDSecureVersion;
    }

    /**
     * @param int $threeDSecureVersion
     *
     * @return Card
     */
    public function setThreeDSecureVersion(int $threeDSecureVersion): Card
    {
        $this->threeDSecureVersion = $threeDSecureVersion;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountType(): int
    {
        return $this->accountType;
    }

    /**
     * @param int|null $accountType
     *
     * @return Card
     */
    final public function setAccountType(?int $accountType): Card
    {
        if ($accountType === null) {
            return $this;
        }

        $this->accountType = $accountType;

        if ($accountType == self::DEBITCARD) {
            $this->setThreeDSecure();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isThreeDSecure(): bool
    {
        return $this->threeDSecure;
    }

    /**
     * @param bool $threeDSecure
     *
     * @return Card
     */
    final public function setThreeDSecure(bool $threeDSecure = self::USE_3DS): Card
    {
        $this->threeDSecure = $threeDSecure;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPan(): ?string
    {
        return $this->pan;
    }

    /**
     * @param string|null $pan
     *
     * @return Card
     */
    final public function setPan(?string $pan): Card
    {
        if ($pan === null) {
            return $this;
        }

        $sum = 0;
        $pan = preg_replace('/[^\d]/', '', $pan);
        $rev = strrev($pan);

        for ($i = 0, $t = strlen($pan); $i < $t; $i++) {
            $num = (int)$rev[$i];
            $sum += $i & 1 ? ($num > 4 ? $num * 2 - 9 : $num * 2) : $num;
        }

        if ($sum % 10 !== 0) {
            throw new InvalidArgumentException('Invalid PAN provided.');
        }

        $this->pan = (string)$pan;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpiration(): string
    {
        return $this->expiration;
    }

    /**
     * @param string|null $expirationYear
     * @param string|null $expirationMonth
     *
     * @return Card
     */
    final public function setExpiration(?string $expirationYear, ?string $expirationMonth): Card
    {
        if ($expirationYear === null && $expirationMonth === null) {
            return $this;
        }

        if (strlen($expirationYear) > 2) {
            throw new InvalidArgumentException('We are expecting the year with only 2 digits.');
        }

        if ((int)$expirationMonth < 1 || (int)$expirationMonth > 12) {
            throw new InvalidArgumentException('Month should be >= than 1 and <= than 12.');
        }

        if (strtotime(sprintf('%s-%s-01', $expirationYear, $expirationMonth)) < time()) {
            throw new InvalidArgumentException('Card expiration date MUST BE in future.');
        }

        $this->expiration = $expirationYear . $expirationMonth;

        return $this;
    }

    /**
     * @return string
     */
    public function getHolderName(): string
    {
        return $this->holderName;
    }

    /**
     * @param string|null $holderName
     *
     * @return Card
     */
    final public function setHolderName(?string $holderName): Card
    {
        if ($holderName === null) {
            return $this;
        }

        $sanitizedHolderName = preg_replace('/[^a-z ]/i', '', $holderName);

        if ($sanitizedHolderName !== $holderName) {
            throw new InvalidArgumentException('Holder name MUST NOT HAVE any special character.');
        }

        $this->holderName = $sanitizedHolderName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCvv(): string
    {
        return $this->cvv;
    }

    /**
     * @param string|null $cvv
     *
     * @return Card
     */
    final public function setCvv(?string $cvv): Card
    {
        if ($cvv === null) {
            return $this;
        }

        $sanitizedCvv = preg_replace('/[^\d]/', '', $cvv);
        $cvvLength    = strlen($sanitizedCvv);

        if ($sanitizedCvv !== $cvv) {
            throw new InvalidArgumentException('CVV MUST HAVE only numbers.');
        }

        if ($cvvLength < 3 || $cvvLength > 4) {
            throw new InvalidArgumentException('CVV MUST HAVE between 3 and 4 digits - inclusive.');
        }

        $this->cvv = $sanitizedCvv;

        return $this;
    }
}

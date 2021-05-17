<?php
namespace GlobalPagamentos;

use InvalidArgumentException;

class Card
{
    public const CREDITCARD = 1;
    public const DEBITCARD = 2;

    private int $accountType;
    private string $pan;
    private string $expirity;
    private string $holderName;
    private string $cvv;
    private bool $threeDSecure = false;

    /**
     * Card constructor.
     *
     * @param int $accountType
     * @param string $pan
     * @param string $expirityYear
     * @param string $expirityMonth
     * @param string $holderName
     * @param string $cvv
     */
    public function __construct(
        int $accountType,
        string $pan,
        string $expirityYear,
        string $expirityMonth,
        string $holderName,
        string $cvv
    ) {
        $this->setAccountType($accountType);
        $this->setHolderName($holderName);
        $this->setPan($pan);
        $this->setExpirity($expirityYear, $expirityMonth);
        $this->setCvv($cvv);
    }

    /**
     * @param string $pan
     * @param string $expirityYear
     * @param string $expirityMonth
     * @param string $holderName
     * @param string $cvv
     *
     * @return Card
     */
    public static function creditCard(
        string $pan,
        string $expirityYear,
        string $expirityMonth,
        string $holderName,
        string $cvv
    ): Card {
        return new Card(
            self::CREDITCARD,
            $pan,
            $expirityYear,
            $expirityMonth,
            $holderName,
            $cvv
        );
    }

    /**
     * @param string $pan
     * @param string $expirityYear
     * @param string $expirityMonth
     * @param string $holderName
     * @param string $cvv
     *
     * @return Card
     */
    public static function debitCard(
        string $pan,
        string $expirityYear,
        string $expirityMonth,
        string $holderName,
        string $cvv
    ): Card {
        return new Card(
            self::DEBITCARD,
            $pan,
            $expirityYear,
            $expirityMonth,
            $holderName,
            $cvv
        );
    }

    /**
     * @return int
     */
    public function getAccountType(): int
    {
        return $this->accountType;
    }

    /**
     * @param int $accountType
     *
     * @return Card
     */
    public function setAccountType(int $accountType): Card
    {
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
    public function setThreeDSecure(bool $threeDSecure = true): Card
    {
        $this->threeDSecure = $threeDSecure;

        return $this;
    }

    /**
     * @return string
     */
    public function getPan(): string
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     *
     * @return Card
     * @throws InvalidArgumentException
     */
    public function setPan(string $pan): Card
    {
        $sum = 0;
        $pan = preg_replace('/[^\d]/', null, $pan);
        $rev = strrev($pan);

        for ($i = 0, $t = strlen($pan); $i < $t; $i++) {
            $sum += $i & 1 ? ($rev[$i] > 4 ? $rev[$i] * 2 - 9 : $rev[$i] * 2) : $rev[$i];
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
    public function getExpirity(): string
    {
        return $this->expirity;
    }

    /**
     * @param string $expirityYear
     * @param string $expirityMonth
     *
     * @return Card
     * @throws InvalidArgumentException
     */
    public function setExpirity(string $expirityYear, string $expirityMonth): Card
    {
        if (strlen($expirityYear) > 2) {
            throw new InvalidArgumentException('We are expecting the year with only 2 digits.');
        }

        if ((int)$expirityMonth < 1 || (int)$expirityMonth > 12) {
            throw new InvalidArgumentException('Month should be >= than 1 and <= than 12.');
        }

        if (strtotime(sprintf('%s-%s-01', $expirityYear, $expirityMonth)) < time()) {
            throw new InvalidArgumentException('Card expiration date MUST BE in future.');
        }

        $this->expirity = $expirityYear . $expirityMonth;

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
     * @param string $holderName
     *
     * @return Card
     * @throws InvalidArgumentException
     */
    public function setHolderName(string $holderName): Card
    {
        $sanitizedHolderName = preg_replace('/[^a-z ]/i', null, $holderName);

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
     * @param string $cvv
     *
     * @return Card
     */
    public function setCvv(string $cvv): Card
    {
        $sanitizedCvv = preg_replace('/[^\d]/', null, $cvv);
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

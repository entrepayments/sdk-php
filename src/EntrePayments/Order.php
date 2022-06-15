<?php

namespace EntrePayments;

use InvalidArgumentException;

class Order
{
    private string $number;
    private int $amount;
    private int $currency = Currency::BRL;
    private string $description;

    public function __construct(string $number, float $amount, int $currency = Currency::BRL, string $description = '')
    {
        $this->setNumber($number);
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setDescription($description);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Order
     */
    final public function setDescription(string $description): Order
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return Order
     */
    final public function setNumber(string $number): Order
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Order
     */
    final public function setAmount(float $amount): Order
    {
        $this->amount = (int)round($amount * 100, 2);

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * @param int $currency
     *
     * @return Order
     * @throws InvalidArgumentException
     */
    final public function setCurrency(int $currency): Order
    {
        if (! Currency::isValid($currency)) {
            throw new InvalidArgumentException('Invalid currency provided.');
        }

        $this->currency = $currency;

        return $this;
    }
}

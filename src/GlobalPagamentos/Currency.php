<?php

namespace GlobalPagamentos;

class Currency
{
    public const BRL = 986;

    /**
     * @param int $currency
     *
     * @return bool
     */
    public static function isValid(int $currency): bool
    {
        foreach (self::allCurrencies() as $testCurrency) {
            if ($testCurrency === $currency) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int[]
     */
    public static function allCurrencies(): array
    {
        return [self::BRL];
    }
}

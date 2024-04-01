<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Gateway\Helper;

/**
 * Formatting Stripe Amount
 */
class AmountHelper
{
    public const CURRENCY_ZERO_DECIMAL = [
        'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
        'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'
    ];

    /**
     * @param string $currency
     * @return bool
     */
    private function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), self::CURRENCY_ZERO_DECIMAL);
    }

    /**
     * For stripe needs to *100
     * but there is a list of currencies that do not need to be multiplied.
     *
     * @param float|int $amount
     *
     * @return float|int
     */
    public function getAmountForStripe($amount, $currency)
    {
        $cents = 100;

        if ($this->isZeroDecimal($currency)) {
            $cents = 1;
        }

        return round($amount * $cents);
    }
}

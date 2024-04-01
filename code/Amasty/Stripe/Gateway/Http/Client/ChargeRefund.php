<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Gateway\Http\Client;

/**
 * Charge Refund in stripe
 */
class ChargeRefund extends AbstractClient
{
    /**
     * Create Refund
     *
     * @param array $data
     */
    protected function process(array $data)
    {
        $storeId = null;
        if (!empty($data[AbstractClient::STORE_ID])) {
            $storeId = (int)$data[AbstractClient::STORE_ID];
            unset($data[AbstractClient::STORE_ID]);
        }

        $stripeAdapter = $this->adapterProvider->get($storeId);

        return $stripeAdapter->refundCreate($data);
    }
}

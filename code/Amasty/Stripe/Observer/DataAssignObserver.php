<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Observer for assign data
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Key For Get Stripe Source
     */
    public const KEY_SOURCE = 'source';

    /**
     * Key For Get Stripe Payment
     */
    public const KEY_PAYMENT = 'payment_method';

    /**
     * Key for Get Save Card Flag
     */
    public const SAVE_CARD = 'save_card';
    /**
     * @var array
     */
    protected $additionalInfo = [
        self::KEY_SOURCE,
        self::KEY_PAYMENT,
        self::SAVE_CARD
    ];

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInfo as $additionalInfoKey) {
            if (isset($additionalData[$additionalInfoKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInfoKey,
                    $additionalData[$additionalInfoKey]
                );
            }
        }
    }
}

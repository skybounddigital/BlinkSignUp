<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Observer\Sales\Order\Payment;

use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Amasty\Stripe\Gateway\Config\Config as StripeConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Place implements ObserverInterface
{
    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    public function __construct(
        OrderConfig $orderConfig,
        StripeConfig $stripeConfig
    ) {
        $this->orderConfig = $orderConfig;
        $this->stripeConfig = $stripeConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getPayment()->getOrder();

        if ($order->getPayment()->getMethod() === ConfigProvider::CODE
            && $status = $this->stripeConfig->getOrderStatus()
        ) {
            $order->setState($status)
                ->setStatus($status);

            foreach ($order->getStatusHistories() as $item) {
                $item->setStatus($status);
            }
        }
    }
}

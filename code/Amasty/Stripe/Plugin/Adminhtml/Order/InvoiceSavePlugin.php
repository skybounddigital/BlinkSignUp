<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Plugin\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Save;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection as ShipmentCollection;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Config as OrderConfig;

class InvoiceSavePlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderResource $orderResource,
        OrderConfig $orderConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderResource = $orderResource;
        $this->orderConfig = $orderConfig;
    }

    /**
     * @param Save $subject
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function afterExecute(Save $subject, $result)
    {
        $orderId = $subject->getRequest()->getParam(OrderItemInterface::ORDER_ID);
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        if ($order->getPayment()->getMethod() === ConfigProvider::CODE) {
            $this->searchCriteriaBuilder->addFilter(OrderItemInterface::ORDER_ID, $orderId);
            /** @var ShipmentCollection $shipmentList */
            $shipmentList = $this->shipmentRepository->getList($this->searchCriteriaBuilder->create());

            if ($shipmentList->getSize() && $order->canShip()) {
                $order->setState(Order::STATE_COMPLETE)
                    ->setStatus($this->orderConfig->getStateDefaultStatus($order->getState()));
                $this->orderResource->save($order);
            }
        }

        return $result;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Gateway\Command;

use Amasty\Stripe\Gateway\Config\Config as StripeConfig;
use Amasty\Stripe\Gateway\Helper\SubjectReader;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Select Command strategy
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * Authorization time to live in seconds
     */
    public const AUTHORIZATION_TTL = 604800;

    /**
     * Authorize and capture command
     */
    public const SALE = 'sale';

    /**
     * Capture command
     */
    public const CAPTURE = 'charge_capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        SubjectReader $subjectReader,
        StripeConfig $stripeConfig
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->subjectReader = $subjectReader;
        $this->stripeConfig = $stripeConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     *
     * @param OrderPaymentInterface $payment
     *
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        // If no authorization transaction exists execute authorize and capture command
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            $authorizeMethod = $this->stripeConfig->getAuthorizeMethod();
            if ($authorizeMethod == 'authorize') {
                return self::CAPTURE;
            } else {
                return self::SALE;
            }
        }

        // Capture authorized charge
        if (!$existsCapture && !$this->isExpiredAuthorization($payment)) {
            return self::CAPTURE;
        }

        // Process capture for payment via Vault
        return self::CAPTURE;
    }

    /**
     * @param OrderPaymentInterface $payment
     *
     * @return boolean
     */
    private function isExpiredAuthorization(OrderPaymentInterface $payment)
    {
        $currentTs = $this->dateTime->timestamp();
        $txTs = $this->dateTime->timestamp($payment->getOrder()->getCreatedAt());

        return $currentTs - $txTs > self::AUTHORIZATION_TTL;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     *
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();

        return (boolean)$count;
    }
}

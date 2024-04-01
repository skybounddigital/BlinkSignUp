<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package App for Payments with Stripe for Magento 2
 */

namespace Amasty\Stripe\Gateway\Http\Client;

use Amasty\Stripe\Gateway\Config\Config as StripeConfig;
use Amasty\Stripe\Model\Adapter\StripeAdapterProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractClient
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * Store Id for get proper configurations
     */
    public const STORE_ID = 'store_id';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $paymentLogger;

    /**
     * @var StripeAdapterProvider
     */
    protected $adapterProvider;

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        LoggerInterface $logger,
        Logger $paymentLogger,
        StripeAdapterProvider $adapterProvider,
        StripeConfig $stripeConfig,
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        State $state
    ) {
        $this->logger = $logger;
        $this->paymentLogger = $paymentLogger;
        $this->adapterProvider = $adapterProvider;
        $this->stripeConfig = $stripeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->state = $state;
    }

    /**
     * @param TransferInterface $transferObject
     *
     * @return array|mixed
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response['object'] = [];
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
            $this->paymentLogger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return \Stripe\ApiResource|\Stripe\Error\Base
     */
    abstract protected function process(array $data);

    /**
     * @return bool
     */
    public function isEmailReceiptsEnabled()
    {
        return $this->stripeConfig->isEmailReceiptsEnabled();
    }

    /**
     * @return string
     */
    public function getReceiptEmail()
    {
        $receiptEmail = '';
        $areaCode = $this->state->getAreaCode();

        if ($areaCode === Area::AREA_WEBAPI_REST && $this->checkoutSession->getQuote()) {
            $receiptEmail = $this->checkoutSession->getQuote()->getCustomerEmail();
        } elseif ($areaCode === Area::AREA_ADMINHTML && $adminOrder = $this->request->getParam('order')) {
            $receiptEmail = isset($adminOrder['account']['email']) ? $adminOrder['account']['email'] : '';
        }

        return $receiptEmail;
    }

    /**
     * @return string
     */
    public function getSaveCardSource()
    {
        return $this->request->getParam('card_source_id') ?
            $this->request->getParam('card_source_id')
            : $this->request->getParam('save_card_source_id');
    }
}

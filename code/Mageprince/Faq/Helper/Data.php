<?php
/**
 * MagePrince
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageprince.com license that is
 * available through the world-wide-web at this URL:
 * https://mageprince.com/end-user-license-agreement
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MagePrince
 * @package     Mageprince_Faq
 * @copyright   Copyright (c) MagePrince (https://mageprince.com/)
 * @license     https://mageprince.com/end-user-license-agreement
 */

namespace Mageprince\Faq\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Mageprince\Base\Helper\Data as BaseHelper;
use Mageprince\Faq\Model\Config\DefaultConfig;
use Magento\Framework\App\Http\Context as AuthContext;

class Data extends AbstractHelper
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var AuthContext
     */
    protected $authContext;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param AuthContext $authContext
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        AuthContext $authContext,
        BaseHelper $baseHelper
    ) {
        $this->customerSession = $customerSession;
        $this->authContext = $authContext;
        $this->baseHelper = $baseHelper;
        parent::__construct($context);
    }

    /**
     * Get config path
     * @param $config
     * @return mixed
     */
    public function getConfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get faq url
     * @return string
     */
    public function getFaqUrl()
    {
        return $this->scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH);
    }

    /**
     * Get current customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroup = 0;
        $isLoggedIn = $this->authContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            $customerGroup = $this->customerSession->getCustomer()->getGroupId();
        }
        return $customerGroup;
    }

    /**
     * Check is block data
     *
     * @param $data
     * @return bool
     */
    public function checkBlockData($data)
    {
        return $this->baseHelper->checkBlockData($data);
    }

    /**
     * Check is module enabled
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue(DefaultConfig::CONFIG_PATH_IS_ENABLE);
    }
}

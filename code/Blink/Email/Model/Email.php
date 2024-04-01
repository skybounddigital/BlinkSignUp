<?php

namespace Blink\Email\Model;

/**
 * Banner Model
 */

class Email extends \Magento\Framework\Model\AbstractModel
{
    const TARGET_SELF = 0;
    const TARGET_PARENT = 1;
    const TARGET_BLANK = 2;
	

	
    protected $_storeViewId = null;

    protected $_emailFactory;

    protected $_formFieldHtmlIdPrefix = 'page_';
	
    protected $_storeManager;

    protected $_monolog;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Blink\Email\Model\ResourceModel\Email $resource,
        \Blink\Email\Model\ResourceModel\Email\Collection $resourceCollection,
        \Blink\Email\Model\EmailFactory $emailFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Logger\Monolog $monolog
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_bannerFactory = $emailFactory;
        $this->_storeManager = $storeManager;
        $this->_monolog = $monolog;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    public function getFormFieldHtmlIdPrefix()
    {
        return $this->_formFieldHtmlIdPrefix;
    }
	
    public function getStoreAttributes()
    {
        return array(
            'name',
            'status',
        );
    }

    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    public function load($id, $field = null)
    {
        parent::load($id, $field);
        if ($this->getStoreViewId()) {
            $this->getStoreViewValue();
        }

        return $this;
    }

    public function getTargetValue()
    {
        switch ($this->getTarget()) {
            case self::TARGET_SELF:
                return '_self';
            case self::TARGET_PARENT:
                return '_parent';

            default:
                return '_blank';
        }
    }
}

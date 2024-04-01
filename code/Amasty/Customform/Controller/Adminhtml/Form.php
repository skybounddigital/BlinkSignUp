<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Controller\Adminhtml;

use Amasty\Customform\Model\FormRegistry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Form extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Customform::forms';

    public const ADMIN_RESOURCE_PAGE = 'Amasty_Customform::forms';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Amasty\Customform\Model\FormFactory
     */
    protected $formFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    protected $formRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var FormRegistry
     */
    protected $formRegistry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\Customform\Model\FormFactory $formFactory,
        \Amasty\Customform\Model\FormRepository $formRepository,
        FormRegistry $formRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->formFactory = $formFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->formRepository = $formRepository;
        $this->logger = $logger;
        $this->mathRandom = $mathRandom;
        $this->formRegistry = $formRegistry;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}

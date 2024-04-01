<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Model;

use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Block\Widget\Form\Element\Wysiwyg;
use Amasty\Customform\Model\Config\Source\Design;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Form extends AbstractModel implements FormInterface, IdentityInterface
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 0;

    public const CACHE_TAG = 'amasty_customform';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_customform';

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var Form\Rendering\Autocomplete\Cleaning\FieldsCleaner
     */
    private $fieldsCleaner;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Customform\Helper\Data $helper,
        \Amasty\Customform\Model\Form\Rendering\Autocomplete\Cleaning\FieldsCleaner $fieldsCleaner,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->fieldsCleaner = $fieldsCleaner;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Customform\Model\ResourceModel\Form::class);
        $this->setIdFieldName('form_id');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getFormId()];
    }

    /**
     * should generate validation attribute before saving model
     * @return $this
     */
    public function beforeSave()
    {
        $this->setPopupButton(
            $this->helper->escapeHtml($this->getPopupButton())
        );
        if (!$this->getData('json_saved')) {
            $formData = $this->getFormJson();
            $formData = $this->jsonDecoder->decode($formData);
            $emailFieldExist = false;
            $emailField = $this->getEmailField();
            foreach ($formData as &$page) {
                if (isset($page['type'])) {
                    // support for old versions of forms
                    $this->dataProcessing($page);
                    if ($emailField == $page['name']) {
                        $emailFieldExist = true;
                    }
                } else {
                    foreach ($page as &$element) {
                        $this->dataProcessing($element);
                        if ($emailField == $element['name']) {
                            $emailFieldExist = true;
                        }
                    }
                }
            }
            if (!$emailFieldExist) {
                $this->setEmailField('');
            }

            $formData = $this->jsonEncoder->encode($formData);
            $this->setFormJson($formData);
        }

        return parent::beforeSave();
    }

    /**
     * @param $data
     */
    private function dataProcessing(&$data)
    {
        $fieldType = $data['type'] ?? null;
        foreach ($data as $field => $value) {
            if ($fieldType == Wysiwyg::TYPE_NAME && $field === 'value') {
                continue;
            }

            $data[$field] = $field == 'label' ? $value : $this->helper->stripTags($value);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    /* Getters and setters implementation:*/

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return $this->_getData(FormInterface::FORM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormId($formId)
    {
        $this->setData(FormInterface::FORM_ID, $formId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_getData(FormInterface::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->setData(FormInterface::CODE, $code);

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_getData(FormInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(FormInterface::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessUrl()
    {
        return $this->_getData(FormInterface::SUCCESS_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccessUrl($successUrl)
    {
        $this->setData(FormInterface::SUCCESS_URL, $successUrl);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_getData(FormInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->setData(FormInterface::STATUS, $status);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(FormInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(FormInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroup()
    {
        return $this->_getData(FormInterface::CUSTOMER_GROUP);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->setData(FormInterface::CUSTOMER_GROUP, $customerGroup);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_getData(FormInterface::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        $this->setData(FormInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSendNotification()
    {
        return $this->_getData(FormInterface::SEND_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setSendNotification($sendNotification)
    {
        $this->setData(FormInterface::SEND_NOTIFICATION, $sendNotification);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSendTo()
    {
        return $this->_getData(FormInterface::SEND_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setSendTo($sendTo)
    {
        $this->setData(FormInterface::SEND_TO, $sendTo);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailTemplate()
    {
        return $this->_getData(FormInterface::EMAIL_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->setData(FormInterface::EMAIL_TEMPLATE, $emailTemplate);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmitButton()
    {
        return $this->_getData(FormInterface::SUBMIT_BUTTON);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubmitButton($submitButton)
    {
        $this->setData(FormInterface::SUBMIT_BUTTON, $submitButton);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessMessage()
    {
        return $this->_getData(FormInterface::SUCCESS_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccessMessage($successMessage)
    {
        $this->setData(FormInterface::SUCCESS_MESSAGE, $successMessage);

        return $this;
    }

    /**
     * @return string
     */
    public function getOrigFormJson()
    {
        return $this->_getData(FormInterface::FORM_JSON);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormJson()
    {
        $formJson = $this->getOrigFormJson();

        return $this->fieldsCleaner->cleanJson($formJson);
    }

    /**
     * @return array
     */
    public function getDecodedFormJson()
    {
        $data = [];
        if ($this->getFormJson()) {
            try {
                $data = $this->jsonDecoder->decode($this->getFormJson());
            } catch (\Exception $ex) {
                $data = [];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormJson($json)
    {
        $this->setData(FormInterface::FORM_JSON, $json);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHideEmailField()
    {
        return (bool)$this->_getData(FormInterface::EMAIL_FIELD_HIDE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailFieldHide($hide)
    {
        return $this->setData(FormInterface::EMAIL_FIELD_HIDE, $hide);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailField()
    {
        return $this->_getData(FormInterface::EMAIL_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailField($emailField)
    {
        return $this->setData(FormInterface::EMAIL_FIELD, $emailField);
    }

    /**
     * {@inheritdoc}
     */
    public function isPopupShow()
    {
        return (bool)$this->_getData(FormInterface::POPUP_SHOW);
    }

    /**
     * {@inheritdoc}
     */
    public function setPopupShow($popupShow)
    {
        return $this->setData(FormInterface::POPUP_SHOW, $popupShow);
    }

    /**
     * {@inheritdoc}
     */
    public function getPopupButton()
    {
        return $this->_getData(FormInterface::POPUP_BUTTON);
    }

    /**
     * {@inheritdoc}
     */
    public function setPopupButton($popupButton)
    {
        return $this->setData(FormInterface::POPUP_BUTTON, $popupButton);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTitle()
    {
        return $this->_getData(FormInterface::FORM_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormTitle($json)
    {
        return $this->setData(FormInterface::FORM_TITLE, $json);
    }

    /**
     * @return string
     */
    public function getAutoReplyTemplate()
    {
        return $this->_getData(FormInterface::AUTO_REPLY_TEMPLATE);
    }

    /**
     * @param string $template
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setAutoReplyTemplate($template)
    {
        return $this->setData(FormInterface::AUTO_REPLY_TEMPLATE, $template);
    }

    /**
     * @return bool
     */
    public function isAutoReplyEnabled()
    {
        return $this->_getData(FormInterface::AUTO_REPLY_ENABLE);
    }

    /**
     * @param bool $value
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setIsAutoReplyEnabled($value)
    {
        return $this->setData(FormInterface::AUTO_REPLY_ENABLE, $value);
    }

    /**
     * @return bool
     */
    public function isSurveyModeEnabled()
    {
        return $this->_getData(FormInterface::SURVEY_MODE_ENABLE);
    }

    /**
     * @param bool $value
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setIsSurveyModeEnabled($value)
    {
        return $this->setData(FormInterface::SURVEY_MODE_ENABLE, $value);
    }

    /**
     * @return string
     */
    public function getDesignClass(): string
    {
        switch ($this->getDesign()) {
            case Design::LINEAR_THEME:
                $result = Design::LINEAR_THEME_CLASS;
                break;
            case Design::CIRCLE_THEME:
                $result = DESIGN::CIRCLE_THEME_CLASS;
                break;
            default:
                $result = DESIGN::DEFAULT_THEME_CLASS;
        }

        return $result;
    }

    public function getFormContainsSensitiveData(): bool
    {
        return (bool) $this->_getData(self::FORM_CONTAINS_SENSITIVE_DATA);
    }

    public function setFormContainsSensitiveData(bool $isContainsSensitiveData): void
    {
        $this->setData(self::FORM_CONTAINS_SENSITIVE_DATA, $isContainsSensitiveData);
    }

    public function setScheduledFrom(string $scheduledFrom): void
    {
        $this->setData(self::SCHEDULED_FROM, $scheduledFrom);
    }

    public function getScheduledFrom(): ?string
    {
        return $this->_getData(self::SCHEDULED_FROM) ?: null;
    }

    public function setScheduledTo(string $scheduledTo): void
    {
        $this->setData(self::SCHEDULED_TO, $scheduledTo);
    }

    public function getScheduledTo(): ?string
    {
        return $this->_getData(self::SCHEDULED_TO) ?: null;
    }

    public function getIsVisible(): bool
    {
        return (bool) $this->_getData(self::IS_VISIBLE);
    }

    public function setIsVisible(bool $isVisible): void
    {
        $this->setData(self::IS_VISIBLE, $isVisible);
    }

    public function isSubscriptionEnabled(): ?bool
    {
        return $this->hasData(self::IS_SUBSCRIPTION_ENABLED)
            ? (bool) $this->_getData(self::IS_SUBSCRIPTION_ENABLED)
            : null;
    }

    public function setSubscriptionEnabled(bool $isSubscriptionEnabled): void
    {
        $this->setData(self::IS_SUBSCRIPTION_ENABLED, $isSubscriptionEnabled);
    }

    public function isSubscriptionRequired(): ?bool
    {
        return $this->hasData(self::IS_SUBSCRIPTION_ENABLED)
            ? (bool) $this->_getData(self::IS_SUBSCRIPTION_REQUIRED)
            : null;
    }

    public function setSubscriptionRequired(bool $isSubscriptionRequired): void
    {
        $this->setData(self::IS_SUBSCRIPTION_REQUIRED, $isSubscriptionRequired);
    }

    public function getSubscriptionText(): ?string
    {
        return $this->hasData(self::IS_SUBSCRIPTION_ENABLED)
            ? (string) $this->_getData(self::SUBSCRIPTION_TEXT)
            : null;
    }

    public function setSubscriptionText(string $subscriptionText): void
    {
        $this->setData(self::SUBSCRIPTION_TEXT, $subscriptionText);
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Controller\Adminhtml\Answer;

use Amasty\Customform\Model\Answer;
use Amasty\Customform\Model\Config\Source\Status;
use Amasty\Customform\Model\Grid\Bookmark;
use Laminas\Validator\NotEmpty;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class Send extends \Amasty\Customform\Controller\Adminhtml\Answer
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Amasty\CustomForm\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var NotEmpty
     */
    private $notEmptyValidator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Customform\Model\AnswerRepository $answerRepository,
        \Amasty\Customform\Model\FormRegistry $formRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\Customform\Helper\Data $helper,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Bookmark $bookmark,
        NotEmpty $notEmptyValidator = null // TODO move to not optional
    ) {
        parent::__construct(
            $context,
            $answerRepository,
            $formRegistry,
            $resultPageFactory,
            $logger,
            $bookmark
        );

        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->formRepository = $formRepository;
        $this->notEmptyValidator = $notEmptyValidator ?? ObjectManager::getInstance()->get(NotEmpty::class);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $answerId = $this->getRequest()->getParam('answer_id');
        $message = $this->getRequest()->getParam('email_text');
        try {
            if (!$this->notEmptyValidator->isValid(trim($message))) {
                $this->messageManager->addErrorMessage(__('Please enter a Email Text.'));
                $this->_redirect('amasty_customform/answer/edit', ['id' => $answerId]);
                return;
            }

            if ($answerId) {
                $model = $this->answerRepository->get($answerId);
                if ($model->getAdminResponseStatus() == Status::ANSWERED) {
                    $this->messageManager->addNoticeMessage(__('Email response is already sent.'));
                } else {
                    $emailTo = $model->getRecipientEmail();

                    if (empty($emailTo)) {
                        throw new LocalizedException(__('Please choose a field to be used as an e-mail address in Email'
                            . ' Address Field setting of the form configuration.'));
                    }

                    if ($this->sendEmail($model, $emailTo, $message)) {
                        $model->setResponseMessage($message);
                        $model->setAdminResponseEmail($emailTo);
                        $model->setAdminResponseStatus(Status::ANSWERED);
                        $this->answerRepository->save($model);
                        $this->messageManager->addSuccessMessage(__('Email response is sent.'));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Submitted data id is not specified.'));
                return $this->_redirect('amasty_customform/answer');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } finally {
            $this->_redirect('amasty_customform/answer/edit', ['id' => $answerId]);
        }
    }

    /**
     * @param \Amasty\Customform\Model\Answer $model
     * @param string $emailTo
     * @param string $message
     * @return bool
     */
    private function sendEmail(\Amasty\Customform\Model\Answer $model, $emailTo, $message)
    {
        try {
            $storeId = $model->getStoreId();
            $sender = $this->helper->getModuleConfig('response/sender', $storeId);
            $template = $this->helper->getModuleConfig('response/template', $storeId);
            $bcc = $this->helper->getModuleConfig('response/bcc', $storeId);
            $store = $this->storeManager->getStore($model->getStoreId());
            $model->addData($this->getModelFields($model));

            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'form_name'     => $this->formRepository->get($model->getFormId())->getTitle(),
                'answer'        => $model,
                'message'       => $message,
                'customer_name' => $model->getCustomerName()
            ];

            if (!empty($bcc)) {
                $bcc = explode(',', $bcc);
                $bcc = array_map('trim', $bcc);
                $this->transportBuilder->addBcc($bcc);
            }

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )
                ->setTemplateVars($data)
                ->setFrom($sender)
                ->addTo($emailTo)
                ->getTransport();
            $transport->sendMessage();
            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }

    /**
     * @param Answer $model
     *
     * @return array
     */
    protected function getModelFields(Answer $model)
    {
        $data = [];
        $fields = $model->getResponseJson() ? $this->helper->decode($model->getResponseJson()) : [];
        foreach ($fields as $key => $field) {
            $value = $this->getRow($field, 'value');
            if (is_array($value)) {
                $filteredFiles = array_filter($value);
                $value = implode(', ', $filteredFiles);
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param $field
     * @param $type
     *
     * @return |null
     */
    private function getRow($field, $type)
    {
        return isset($field[$type]) ? $field[$type] : null;
    }
}

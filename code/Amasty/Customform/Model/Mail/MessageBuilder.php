<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Model\Mail;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;

class MessageBuilder
{
    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var EmailMessageInterface|MessageInterface
     */
    private $oldMessage;

    /**
     * @var array
     */
    private $messageParts = [];

    /**
     * @var bool
     */
    private $isNewVersion = false;

    public function __construct(
        ObjectManagerInterface $objectManager,
        MagentoVersion $magentoVersion
    ) {
        $this->isNewVersion = version_compare($magentoVersion->get(), '2.3.3', '>=');

        if ($this->isNewVersion) {
            //It is created using ObjectManager since in Magento versions prior to 2.3.3 these classes does not exist
            $this->emailMessageInterfaceFactory = $objectManager->get(EmailMessageInterfaceFactory::class);
            $this->mimeMessageInterfaceFactory = $objectManager->get(MimeMessageInterfaceFactory::class);
        }
    }

    /**
     * @return EmailMessageInterface|MessageInterface
     * @throws LocalizedException
     */
    public function build()
    {
        if ($this->isNewVersion) {
            return $this->buildUsingEmailMessageInterfaceFactory();
        }

        return $this->replaceMessageBody();
    }

    /**
     * @return EmailMessageInterface
     * @throws LocalizedException
     */
    private function buildUsingEmailMessageInterfaceFactory()
    {
        $this->checkDependencies();
        $parts = $this->oldMessage->getBody()->getParts();
        $parts = array_merge($parts, $this->messageParts);
        $messageData = [
            'body' => $this->mimeMessageInterfaceFactory->create(
                ['parts' => $parts]
            ),
            'from' => $this->oldMessage->getFrom(),
            'to' => $this->oldMessage->getTo(),
            'replyTo' => $this->getReplyTo(),
            'subject' => $this->oldMessage->getSubject()
        ];
        $message = $this->emailMessageInterfaceFactory->create($messageData);

        return $message;
    }

    /**
     * @return MessageInterface
     * @throws LocalizedException
     */
    private function replaceMessageBody()
    {
        $this->checkDependencies();

        if (!empty($this->messageParts)) {
            /** @var \Laminas\Mime\Part $part */
            foreach ($this->messageParts as $part) {
                $this->oldMessage->getBody()->addPart($part);
            }

            $this->oldMessage->setBody($this->oldMessage->getBody());
        }

        return $this->oldMessage;
    }

    /**
     * @throws LocalizedException
     */
    private function checkDependencies()
    {
        if ($this->oldMessage === null) {
            throw new LocalizedException(__('To create a message, you need it\'s prototype...'));
        }
    }

    /**
     * @return \Magento\Framework\Mail\Address[]
     */
    private function getReplyTo(): array
    {
        $replyToInOldMessage = $this->oldMessage->getReplyTo();

        if (is_array($replyToInOldMessage) && count($replyToInOldMessage)) {
            $replyTo = $replyToInOldMessage;
        } else {
            $replyTo = $this->oldMessage->getFrom();
        }

        return $replyTo;
    }

    /**
     * @param EmailMessageInterface|MessageInterface $oldMessage
     *
     * @return MessageBuilder
     */
    public function setOldMessage($oldMessage)
    {
        $this->oldMessage = $oldMessage;

        return $this;
    }

    /**
     * @param array $messageParts
     *
     * @return MessageBuilder
     */
    public function setMessageParts(array $messageParts)
    {
        $this->messageParts = $messageParts;

        return $this;
    }
}

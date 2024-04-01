<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Model\Template;

use Amasty\Customform\Model\Mail\MessageBuilder;
use Amasty\Customform\Model\Mail\MessageBuilderFactory;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as Transport;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;

class TransportBuilder extends Transport
{
    /**
     * @var array
     */
    private $parts = [];

    /**
     * @var MessageBuilderFactory
     */
    protected $messageBuilderFactory;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageBuilderFactory $messageBuilderFactory
    ) {
        $this->messageBuilderFactory = $messageBuilderFactory;

        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory
        );
    }

    /**
     * @param $body
     * @param null $filename
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     *
     * @return $this
     */
    public function addAttachment(
        $body,
        $filename = null,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64
    ) {
        if ($this->message && method_exists($this->message, 'createAttachment')) {
            $this->message->createAttachment(
                $body,
                $mimeType,
                $disposition,
                $encoding,
                $filename
            );
        } else {
            $mp = new Part($body);
            $mp->encoding = $encoding;
            $mp->type = $mimeType;
            $mp->disposition = $disposition;
            $mp->filename = $filename;
            $this->parts[] = $mp;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();

        /**
         * @var MessageBuilder $messageBuilder
         */
        $messageBuilder = $this->messageBuilderFactory->create();
        $this->message = $messageBuilder
            ->setOldMessage($this->message)
            ->setMessageParts($this->parts)
            ->build();

        return $this;
    }
}

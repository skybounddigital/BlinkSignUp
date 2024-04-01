<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Plugin\Customer\Model;

use Amasty\CustomerAttributes\Component\Form\AttributeMapper;
use Amasty\CustomerAttributes\Model\Attribute;

class DataProvider
{
    protected $objectManager;
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;
    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var \Magento\Framework\Filesystem
     */

    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
    }

    /**
     * set magento data model for checkboxes and radios
     *
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetFieldsMetaInfo($subject, $result)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            Attribute::AMASTY_ATTRIBUTE_CODE
        );
        $customerAttributes = $codes =  [];
        foreach ($attributes as $attribute) {
            $customerAttributes[$attribute->getAttributeCode()] = $attribute;
            $codes[] = $attribute->getAttributeCode();
        }

        foreach ($result as $name => $meta) {
            if (in_array($name, $codes)) {
                $result[$name] = $this->attributeMapper->map($customerAttributes[$name]);
            }
        }

        return $result;
    }

    public function afterGetMeta($subject, $result)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            Attribute::AMASTY_ATTRIBUTE_CODE
        );
        $customerAttributes = $codes =  [];
        foreach ($attributes as $attribute) {
            $customerAttributes[$attribute->getAttributeCode()] = $attribute;
            $codes[] = $attribute->getAttributeCode();
        }

        foreach ($result['customer']['children'] as $name => $meta) {
            if (in_array($name, $codes)) {
                if ($customerAttributes[$name]->getTypeInternal() == 'selectgroup') {
                    unset($result['customer']['children'][$name]);
                    continue;
                }
            }
        }

        return $result;
    }
}

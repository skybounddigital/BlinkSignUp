<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Plugin\Customer\Model\Address;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Api\AttributeValue;

class AbstractAddressPlugin
{
    /**
     * @param AbstractAddress $subject
     * @param $result
     * @param string $key
     * @param null $value
     * @return mixed
     */
    public function afterSetData(
        AbstractAddress $subject,
        $result,
        $key,
        $value = null
    ) {
        if ($key === 'custom_attributes') {
            foreach ($result->getCustomAttributes() as $attribute) {
                $this->revertArray($attribute, $attribute->getValue());
                $this->revertMultiline($attribute, $value);
            }
        }

        return $result;
    }

    /**
     * @param AttributeValue $attribute
     * @param string|array $valueAttribute
     */
    private function revertArray(
        $attribute,
        $valueAttribute
    ) {
        if (is_array($valueAttribute) &&
            array_key_exists('attribute_code', $valueAttribute) &&
            array_key_exists('value', $valueAttribute)) {
            $attribute->setValue($valueAttribute['value']);
        }
    }

    /**
     * @param AttributeValue $attribute
     * @param array $originalCustomAttributes
     */
    private function revertMultiline(
        $attribute,
        $originalCustomAttributes
    ) {
        $valueOriginalAttribute = null;
        if (isset($originalCustomAttributes[$attribute->getAttributeCode()])
            && is_array($originalCustomAttributes[$attribute->getAttributeCode()])) {
            $valueOriginalAttribute = $originalCustomAttributes[$attribute->getAttributeCode()]['value'];
        }

        if (isset($originalCustomAttributes[$attribute->getAttributeCode()])
            && is_object($originalCustomAttributes[$attribute->getAttributeCode()])) {
            $valueOriginalAttribute = $originalCustomAttributes[$attribute->getAttributeCode()]->getValue();
        }

        if ($valueOriginalAttribute === null) {
            foreach ($originalCustomAttributes as $origAttribute) {
                if ($origAttribute['attribute_code'] == $attribute->getAttributeCode()) {
                    $valueOriginalAttribute = $origAttribute['value'];
                }
            }
        }

        if ($valueOriginalAttribute &&
            is_array($valueOriginalAttribute) && $attribute->getAttributeCode() !== 'street') {
            $attribute->setValue(implode(',', $valueOriginalAttribute));
        }
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Plugin\Magento\Customer\Model;

class Attribute
{
    private $attribute;

    public function __construct(
        \Amasty\CustomerAttributes\Model\Attribute $attribute
    ) {
        $this->attribute = $attribute;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $subject
     * @param $result
     * @return array
     */
    public function afterGetUsedInForms($subject, $result)
    {
        if ($this->attribute->isOurAttribute($subject->getAttributeCode())) {
            $result = array_merge($result, ['customer_account_create', 'customer_account_edit']);
        }

        return $result;
    }
}

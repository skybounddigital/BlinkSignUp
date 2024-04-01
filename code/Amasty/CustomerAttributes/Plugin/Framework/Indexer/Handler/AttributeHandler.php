<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Plugin\Framework\Indexer\Handler;

class AttributeHandler
{
    /**
     * Fix for indexing of customer grid
     * @param \Magento\Framework\Indexer\Handler\AttributeHandler $subject
     * @param \Closure $closure
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $source
     * @param string $alias
     * @param array $fieldInfo
     */
    public function aroundPrepareSql($subject, $closure, $source, $alias, $fieldInfo)
    {
        if ($source instanceof \Magento\Customer\Model\Indexer\Source) {
            if (!isset($fieldInfo['bind'])) {
                $fieldInfo['bind'] = '';
                $source->addFieldToSelect($fieldInfo['origin'], $alias);
                return;
            }
        }
        $closure($source, $alias, $fieldInfo);
    }
}

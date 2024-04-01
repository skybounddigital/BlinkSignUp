<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Plugin\Ui\Config\Reader\Definition;

class Data
{
    /**
     * @var array
     */
    protected $filterMap = [
            'selectimg'     => 'select',
            'multiselectimg' => 'multiselect',
            'statictext' => 'textarea'
        ];

    /**
     * Method replace all custom types to default for admin customer page
     * @param \Magento\UI\Config\Reader\Definition\Data $subject
     * @param string $key
     * @param $default
     * @return array
     */
    public function beforeGet($subject, $key, $default = null)
    {
        $key = isset($this->filterMap[$key]) ? $this->filterMap[$key] : $key;
        return [$key, $default];
    }
}

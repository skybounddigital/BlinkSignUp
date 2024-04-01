<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */

namespace Amasty\CustomerAttributes\Ui\Component;

use Amasty\CustomerAttributes\Block\Data\Form\Element\Boolean;

class ColumnFactory extends \Magento\Catalog\Ui\Component\ColumnFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($componentFactory);
        $this->_objectManager = $objectManager;
    }

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default'       => 'text',
        'text'          => 'text',
        'boolean'       => 'select',
        'select'        => 'select',
        'multiselect'   => 'multiselect',
        'multiselectimg'=> 'multiselect',
        'selectimg'     => 'select',
        'selectgroup'   => 'select',
        'date'          => 'date',
    ];

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create($attribute, $context, array $config = [])
    {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->getDataType($attribute),
            'add_field' => true,
            'visible' => $attribute->getIsVisibleInGrid(),
            'filter' => ($attribute->getIsFilterableInGrid())
                ? $this->getFilterType($attribute->getFrontendInput())
                : null,
        ], $config);

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }
        if ($attribute->getFrontendInput() === 'boolean') {
            $config['options'] = $this->_objectManager->get(Boolean::class)->getValues();
        }
        
        $config['component'] = $this->getJsComponent($config['dataType']);
        
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];
        
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }
}

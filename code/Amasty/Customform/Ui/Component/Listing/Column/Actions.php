<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */
namespace Amasty\Customform\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_customform/forms/edit',
                        [
                            'form_id' => $item['form_id']
                        ]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['duplicate'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_customform/forms/duplicate',
                        [
                            'form_id' => $item['form_id']
                        ]
                    ),
                    'label' => __('Duplicate'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['export_to_csv'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_customform/forms/export',
                        [
                            'form_id' => $item['form_id']
                        ]
                    ),
                    'label' => __('Export Submitted Data To CSV'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['export_to_pdfs'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_customform/answer/exportFormDataToPdfs',
                        [
                            'form_id' => $item['form_id']
                        ]
                    ),
                    'label' => __('Export Submitted Data To PDF'),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}

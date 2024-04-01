<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Model\Export;

use Magento\Ui\Model\Export\MetadataProvider;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Customform\Model\Export\MetadataProvider $metadataProvider
    ) {
        $this->metadataProvider = $metadataProvider;
        parent::__construct($filesystem, $filter, $metadataProvider);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $component->getContext()->getDataProvider()->setLimit(0, 0);
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        $fields = $this->metadataProvider->getMainTableColumns($searchResult);

        $this->directory->create('export');
        $file = 'export_' . $component->getName() . hash('sha256', microtime()) . '.csv';
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getMainTableHeaders($searchResult));

        foreach ($searchResult->getItems() as $document) {
            $stream->writeCsv($this->metadataProvider->getRowData($document, $fields, []));
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}

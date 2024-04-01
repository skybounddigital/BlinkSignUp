<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\ViewModel\Export\Pdf\SubmittedData\Fields;

trait FieldViewModelTrait
{
    /**
     * @var string
     */
    private $fieldValue;

    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    public function setFieldValue(string $fieldValue): void
    {
        $this->fieldValue = $fieldValue;
    }
}

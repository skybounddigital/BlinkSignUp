<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Customer Attributes Base for Magento 2
 */
namespace Amasty\CustomerAttributes\Plugin\Customer\Model;

class FileUploader
{
    /**
     * Since url was generated, return back file name to show it correct on FE side
     *
     * @param \Magento\Customer\Model\FileUploader $object
     * @param array $result
     * @return array
     */
    public function afterUpload($object, $result)
    {
        $result['name'] = $result['tmp_real_name'];
        unset($result['tmp_real_name']);
        return $result;
    }
}

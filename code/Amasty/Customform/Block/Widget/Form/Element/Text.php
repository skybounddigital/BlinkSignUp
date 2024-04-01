<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace Amasty\Customform\Block\Widget\Form\Element;

class Text extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();

        $this->options['title'] = __('Text');
        $this->options['image_href'] = 'Amasty_Customform::images/text.png';
    }

    public function generateContent()
    {
        return '<div>' . $this->getExamplePhrase() . '</div>';
    }

    public function getExamplePhrase()
    {
        return __('Sphinx of black quartz, judge my vow');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class for Notice
 */
class Notice extends Field
{
    /**
     * Render text
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<td colspan="4"><p class="' . $element->getId() . '_notice">' . '<strong>' . __('Important:')
            . ' ' . '</strong>' . ' <span>' . __('Please note that this option is not compatible with PWA Studio.
            Please use reCAPTCHA v3 instead.') . '</span>' . '</p></td>';

        return $this->_decorateRowHtml($element, $html);
    }
}

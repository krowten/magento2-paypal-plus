<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config form fieldset renderer
 */
namespace Iways\PayPalPlus\Block\Adminhtml\System\Config;


use Magento\Framework\App\Config\ScopeConfigInterface;

class ThirdPartyInfo extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var
     */
    protected $_dummyElement;
    /**
     * @var
     */
    protected $_fieldRenderer;
    /**
     * @var
     */
    protected $_values;

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $html .= '<tr id="row_' . $field->getHtmlId() . '"><td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $html .= $field->toHtml();
            }
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}

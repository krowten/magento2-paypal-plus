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
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;


    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        $this->paymentConfig = $paymentConfig;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

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
        $dummyField = $element->getElements()[0];

        $thirdPartyMethods = explode(',', $this->_scopeConfig->getValue('payment/iways_paypalplus_payment/third_party_moduls'));
        foreach ($this->paymentConfig->getActiveMethods() as $paymentMethod) {
            if(in_array($paymentMethod->getCode(), $thirdPartyMethods)) {
                $thirdPartyMethod = $paymentMethod->getCode();
                $field = clone $dummyField;
                $field->setData('name', str_replace('dummy', $thirdPartyMethod, $field->getName()));
                $field->setData('label', $paymentMethod->getTitle());
                $field->setData('value', $this->_scopeConfig->getValue('payment/iways_paypalplus_section/third_party_modul_info/text_'.$thirdPartyMethod));
                $fieldConfig = $field->getData('field_config');
                $fieldConfig['id'] = 'text_'.$thirdPartyMethod;
                $fieldConfig['label'] = $paymentMethod->getTitle();
                $fieldConfig['config_path'] = 'payment/iways_paypalplus_section/third_party_modul_info/text_'.$thirdPartyMethod;
                $field->setData('field_config', $fieldConfig);
                $field->setData('html_id', str_replace('dummy', $thirdPartyMethod, $field->getData('html_id')));
                $html .= $field->toHtml();
            }
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}

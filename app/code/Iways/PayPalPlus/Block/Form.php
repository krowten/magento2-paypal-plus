<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Iways\PayPalPlus\Block;

/**
 * Payment method form base block
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * PayPalPlus Payment method code
     */
    const IWAYS_PAYPALPLUS_PAYMENT = 'iways_paypalplus_payment';

    /**
     * Templates for third party methods
     */
    const THIRDPARTY_TEMPLATE = 'thirdPartyPaymentMethods: [%s],';
    const THIRDPARTY_METHOD_TEMPLATE =
        '{"redirectUrl":"%s", "methodName": "%s", "imageUrl": "%s", "description": "%s"}';

    /**
     * Byte marks to check payment method availability.
     */
    const CHECK_USE_FOR_COUNTRY = 1;
    const CHECK_USE_FOR_CURRENCY = 2;
    const CHECK_USE_CHECKOUT = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX = 32;
    const CHECK_RECURRING_PROFILES = 64;
    const CHECK_ZERO_TOTAL = 128;

    /**
     * @var \Iways\PayPalPlus\Helper\Data
     */
    protected $payPalPlusHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * PayPal Plus template
     *
     * @var string
     */
    protected $_template = 'Iways_PayPalPlus::form/payment.phtml';


    public function __construct(
        \Iways\PayPalPlus\Helper\Data $payPalPlusHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->payPalPlusHelper = $payPalPlusHelper;
        $this->scopeConfig = $scopeConfig;
        $this->paymentHelper = $paymentHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Request payment experience from PayPal for current quote.
     *
     * @return string
     */
    public function getPaymentEperience()
    {
        return $this->payPalPlusHelper->getPaymentExperience();
    }

    /**
     * Construct third party method json string with all needed information for PayPal.
     *
     * @return string
     */
    public function getThirdPartyMethods()
    {
        /*$thirdPartyMethods = $this->scopeConfig->getValue('payment/iways_paypalplus_payment/third_party_moduls', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($thirdPartyMethods)) {
            $thirdPartyMethods = explode(',', $thirdPartyMethods);
            $activePamentMethods = $this->getMethods();
            $renderMethods = array();
            foreach ($activePamentMethods as $activePaymentMethod) {
                if (in_array($activePaymentMethod->getCode(), $thirdPartyMethods)) {
                    $renderMethods[] = sprintf(
                        self::THIRDPARTY_METHOD_TEMPLATE,
                        $this->getCheckoutUrl() . $activePaymentMethod->getCode(),
                        $activePaymentMethod->getTitle(),
                        '',
                        $this->scopeConfig->getValue('payment/third_party_modul_info/text_' . $activePaymentMethod->getCode(, \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                    );
                }
            }
            return sprintf(
                self::THIRDPARTY_TEMPLATE,
                implode(', ', $renderMethods)
            );
        }*/
        return '';
    }

    /**
     * Build Json Object for payment name and code.
     *
     * Used for third party method selection.
     *
     * @return string
     */
    public function getThirdPartyJsonObject()
    {
        $methods = $this->getMethods();
        $methodsArray = array();
        foreach ($methods as $method) {
            $methodsArray[$method->getTitle()] = $method->getCode();
        }
        return json_encode($methodsArray);
    }

    /**
     * Build Method Json Object for payment code and name.
     *
     * Used for third party method selection.
     *
     * @return string
     */
    public function getThirdPartyMethodJsonObject()
    {
        $thirdPartyMethods = $this->scopeConfig->getValue('payment/iways_paypalplus_payment/third_party_moduls', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $renderMethods = array();
        if (!empty($thirdPartyMethods)) {
            $thirdPartyMethods = explode(',', $thirdPartyMethods);
            $activePaymentMethods = $this->getMethods();
            foreach ($activePaymentMethods as $activePaymentMethod) {
                if (in_array($activePaymentMethod->getCode(), $thirdPartyMethods)) {
                    $renderMethods[$activePaymentMethod->getCode()] = $activePaymentMethod->getTitle();
                }
            }
        }
        return json_encode($renderMethods);
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod $method
     * @return bool
     */
    protected function _canUseNewMethod($method)
    {
        return $method->isApplicableToQuote(
            $this->getQuote(),
            self::CHECK_USE_FOR_COUNTRY | self::CHECK_USE_FOR_CURRENCY | self::CHECK_ORDER_TOTAL_MIN_MAX
        );
    }

    /**
     * Retrieve available payment methods
     *
     * with versionswitch
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = array();
            foreach ($this->paymentHelper->getStoreMethods($store, $quote) as $method) {
                if ($method->getCode() == self::IWAYS_PAYPALPLUS_PAYMENT) {
                    continue;
                }
                if ($this->_canUseNewMethod($method)
                    && $method->isApplicableToQuote($quote, \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL)
                ) {
                    $methods[] = $method;
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    /**
     * Get frontend language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get country for current quote
     *
     * @return string
     */
    public function getCountryId()
    {
        $billingAddress = $this->checkoutSession->getQuote()->getBillingAddress();
        if ($billingAddress) {
            $countryId = $billingAddress->getCountryId();
        } else {
            $countryId = $this->payPalPlusHelper->getDefaultCountryId();
        }
        return $countryId;
    }

    /**
     * Return quote for current customer.
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}

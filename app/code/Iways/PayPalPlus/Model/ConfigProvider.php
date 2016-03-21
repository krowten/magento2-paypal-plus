<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Payment::CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Iways\PayPalPlus\Helper\Data
     */
    protected $payPalPlusHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param \Iways\PayPalPlus\Helper\Data $payPalPlusHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Iways\PayPalPlus\Helper\Data $payPalPlusHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->payPalPlusHelper = $payPalPlusHelper;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'iways_paypalplus_payment' => [
                    'paymentExperience' => $this->payPalPlusHelper->getPaymentExperience(),
                    'showPuiOnSandbox' => $this->scopeConfig->getValue('iways_paypalplus/dev/pui_sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? true : false,
                    'showLoadingIndicator' => $this->scopeConfig->getValue('payment/iways_paypalplus_payment/show_loading_indicator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? true : false,
                    'mode' => $this->scopeConfig->getValue('iways_paypalplus/api/mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'country' => $this->getCountry(),
                    'language' => $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                ],
            ],
        ] : [];
    }

    protected function getCountry()
    {
        $billingAddress = $this->checkoutSession->getQuote()->getBillingAddress();
        if ($billingAddress->getCountryId()) {
            return $billingAddress->getCountryId();
        }

        $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        if ($shippingAddress->getCountryId()) {
            return $shippingAddress->getCountryId();
        }

        return $this->scopeConfig->getValue('paypal/general/merchant_country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
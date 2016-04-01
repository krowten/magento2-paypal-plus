<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Psr\Log\LoggerInterface;

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
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var MethodList
     */
    protected $methodLost;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param \Iways\PayPalPlus\Helper\Data $payPalPlusHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Iways\PayPalPlus\Helper\Data $payPalPlusHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Model\Config $paymentConfig,
        MethodList $methodList,
        \Magento\Framework\UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->payPalPlusHelper = $payPalPlusHelper;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->paymentConfig = $paymentConfig;
        $this->methodList = $methodList;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
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
                    'showPuiOnSandbox' => $this->scopeConfig->getValue('iways_paypalplus/dev/pui_sandbox',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? true : false,
                    'showLoadingIndicator' => $this->scopeConfig->getValue('payment/iways_paypalplus_payment/show_loading_indicator',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? true : false,
                    'mode' => $this->scopeConfig->getValue('iways_paypalplus/api/mode',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'country' => $this->getCountry(),
                    'language' => $this->scopeConfig->getValue('general/locale/code',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'thirdPartyPaymentMethods' => $this->getThirdPartyMethods()
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

        return $this->scopeConfig->getValue('paypal/general/merchant_country',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    protected function getThirdPartyMethods()
    {
        $paymentMethods = $this->methodList->getAvailableMethods($this->checkoutSession->getQuote(), false);
        $allowedPPPMethods = explode(
            ',',
            $this->scopeConfig->getValue(
                'payment/iways_paypalplus_payment/third_party_moduls',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $methods = [];
        foreach ($paymentMethods as $paymentMethod) {
            if (strpos($paymentMethod->getCode(), 'paypal') === false && in_array($paymentMethod->getCode(), $allowedPPPMethods)) {
                $method = [
                    'redirectUrl' => $this->urlBuilder->getUrl('checkout', ['_secure' => true]),
                    'methodName' => $paymentMethod->getTitle(),
                    'imageUrl' => '',
                    'description' => $this->scopeConfig->getValue(
                        'payment/iways_paypalplus_section/third_party_modul_info/text_'.$paymentMethod->getCode(),
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ];
                $methods[$paymentMethod->getCode()] = $method;
            }
        }
        if ($methods) {
            return $methods;
        }
        return null;
    }
}
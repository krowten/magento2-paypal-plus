<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 05.11.18
 * Time: 11:40
 */

namespace Iways\PayPalPlus\Plugin\Payment\Model;

use Iways\PayPalPlus\Model\Payment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\MethodList;

class MethodListPlugin
{
    const AMAZON_PAYMENT = 'amazon_payment';
    const CHECK_PPP_FUNCTION_NAME = 'getCheckPPP';

    /**
     * MethodListPlugin constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MethodList $methodList
     * @param $result
     * @return array
     */
    public function afterGetAvailableMethods(MethodList $methodList, $result)
    {
        $checkPPP = false;
        if (method_exists($methodList, self::CHECK_PPP_FUNCTION_NAME)) {
            $checkPPP = $methodList->{self::CHECK_PPP_FUNCTION_NAME}();
        }

        if (!$checkPPP) {
            $allowedPPPMethods = explode(
                ',',
                $this->scopeConfig->getValue(
                    'payment/iways_paypalplus_payment/third_party_moduls',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $allowedMethods = [];

            foreach ($result as $method) {
                if (
                    $method->getCode() == Payment::CODE
                    || $method->getCode() == self::AMAZON_PAYMENT
                    || !in_array($method->getCode(), $allowedPPPMethods)
                ) {
                    $allowedMethods[] = $method;
                }
            }

            return $allowedMethods;
        }
        return $result;
    }
}
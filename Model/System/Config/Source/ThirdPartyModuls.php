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
namespace Iways\PayPalPlus\Model\System\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * PayPal Api Mode resource class
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class ThirdPartyModuls implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Construct
     *
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_paymentConfig = $paymentConfig;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payments = $this->_paymentConfig->getActiveMethods();

        $methods = [['value' => '', 'label' => __('--Please Select--')]];

        foreach ($payments as $paymentCode => $paymentModel) {
            if (strpos($paymentCode, 'paypal') !== false) {
                continue;
            }

            $paymentTitle = $this->_scopeConfig->getValue('payment/' . $paymentCode . '/title');
            if (empty($paymentTitle)) {
                $paymentTitle = $paymentCode;
            }
            $methods[$paymentCode] = [
                'label' => $paymentTitle,
                'value' => $paymentCode,
            ];
        }
        return $methods;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $payments = $this->_paymentConfig->getAllMethods();

        $methods = [];

        foreach ($payments as $paymentCode => $paymentModel) {
            if ($paymentCode == 'iways_paypalplus_payment') {
                continue;
            }
            if (empty($paymentTitle)) {
                $paymentTitle = $paymentCode;
            }
            $paymentTitle = $this->_scopeConfig->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = $paymentTitle;
        }
        return $methods;
    }
}

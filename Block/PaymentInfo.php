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

use Iways\PayPalPlus\Model\Payment;

/**
 * Class Info
 * @package Iways\PayPalPlus\Block\Payment
 */
class PaymentInfo extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'paypalplus/info/default.phtml';

    /**
     * Render as PDF
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('paypalplus/info/pdf/default.phtml');
        return $this->toHtml();
    }

    /**
     * Prepare information specific to current payment method
     * @param null $transport
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = [];

        if (!$this->getIsSecureMode()) {
            $info[(string)__('Transaction ID')] = $this->getInfo()->getLastTransId();
        }
        if ($this->isPUI()) {
            $info[(string)__('Account holder')] = $payment->getData('ppp_account_holder_name');
            $info[(string)__('Bank')] = $payment->getData('ppp_bank_name');
            $info[(string)__('IBAN')] = $payment->getData('ppp_international_bank_account_number');
            $info[(string)__('BIC')] = $payment->getData('ppp_bank_identifier_code');
            $info[(string)__('Reference number')] = $payment->getData('ppp_reference_number');
            $info[(string)__('Payment due date')] = $payment->getData('ppp_payment_due_date');
        }

        return $transport->addData($info);
    }

    /**
     * Checks if PayPal PLUS payment is PUI
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isPUI()
    {
        return (
            $this->getInfo()->getData('ppp_instruction_type') == Payment::PPP_INSTRUCTION_TYPE
        ) ? true : false;
    }
}
<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * Created on 02.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

namespace Iways\PayPalPlus\Block\Payment;

/**
 * Iways PayPalPlus Payment Block
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Info extends \Magento\Payment\Block\Info
{

    /**
     * @var string
     */
    protected $_template = 'Iways_PayPalPlus::paypalplus/payment/info.phtml';

    /**
     * Set PayPal Plus template in construct
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Render as PDF
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Iways_PayPalPlus::paypalplus/payment/pdf/info.phtml');
        return $this->toHtml();
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param \Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = array();

        if(!$this->getIsSecureMode()) {
            $info[(string)__('Transaction ID')] = $this->getInfo()->getLastTransId();
        }
        if($this->isPUI()) {
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
     * Checks if PayPal Plus payment is PUI
     *
     * @return bool
     */
    public function isPUI()
    {
        return ($this->getInfo()->getData('ppp_instruction_type') == \Iways\PayPalPlus\Model\Payment::PPP_INSTRUCTION_TYPE) ? true : false;
    }
}
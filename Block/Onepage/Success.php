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
namespace Iways\PayPalPlus\Block\Onepage;

use Iways\PayPalPlus\Model\Payment;
use Magento\Sales\Model\Order;

/**
 * One page checkout success page
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * Store name config path
     */
    const STORE_NAME_PATH = 'general/store_information/name';

    /**
     * Checkout session
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Order
     * @var Order
     */
    protected $order;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param [] $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->order = $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Check if last order is PayPalPlus
     * @return bool
     */
    public function isPPP()
    {
        if ($this->order->getPayment()->getMethodInstance()->getCode() == Payment::CODE) {
            return true;
        }
        return false;
    }

    /**
     * Checks if order is PayPal PLUS and PuI
     *
     * @return bool
     */
    public function isPUI()
    {
        return (
            $this->isPPP()
            && (
                $this->order->getPayment()->getData('ppp_instruction_type')
                == Payment::PPP_INSTRUCTION_TYPE
            )
        ) ? true : false;
    }

    /**
     * Checks if order is PayPal PLUS and has payment instructions
     *
     * @return bool
     */
    public function hasPaymentInstruction()
    {
        return ($this->isPPP() && $this->order->getPayment()->getData('ppp_instruction_type')) ? true : false;
    }

    /**
     * Wrapper for $payment->getData($key)
     *
     * @param string $key
     * @return array|mixed|null
     */
    public function getAdditionalInformation($key)
    {
        return $this->order->getPayment()->getData($key);
    }

    /**
     * Get store name from config
     *
     * @return string|null
     */
    public function getStoreName()
    {
        return $this->_scopeConfig->getValue(self::STORE_NAME_PATH);
    }
}

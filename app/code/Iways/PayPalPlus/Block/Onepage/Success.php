<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Block\Onepage;

use Iways\PayPalPlus\Model\Payment;
use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

/**
 * One page checkout success page
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_order = $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Check if last order is PayPalPlus
     * @return bool
     */
    public function isPPP()
    {
        if ($this->_order->getPayment()->getMethodInstance()->getCode() == Payment::CODE) {
            return true;
        }
        return false;
    }

    /**
     * Checks if order is PayPal Plus and PuI
     *
     * @return bool
     */
    public function isPUI()
    {
        return (
            $this->isPPP()
            && (
                $this->_order->getPayment()->getData('ppp_instruction_type')
                == Payment::PPP_INSTRUCTION_TYPE
            )
        ) ? true : false;
    }

    /**
     * Checks if order is PayPal Plus and has payment instructions
     *
     * @return bool
     */
    public function hasPaymentInstruction()
    {
        return ($this->isPPP() && $this->_order->getPayment()->getData('ppp_instruction_type')) ? true : false;
    }

    /**
     * Wrapper for $payment->getData($key)
     *
     * @param $key
     * @return array|mixed|null
     */
    public function getAdditionalInformation($key)
    {
        return $this->_order->getPayment()->getData($key);
    }
}

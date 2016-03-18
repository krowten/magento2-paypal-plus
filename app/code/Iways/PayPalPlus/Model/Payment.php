<?php

namespace Iways\PayPalPlus\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'iways_paypalplus_payment';
    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var string
     */
    #protected $_formBlockType = 'Iways\PayPalPlus\Block\Form';

    /**
     * @var string
     */
    #protected $_infoBlockType = 'Iways\PayPalPlus\Block\Info';
}
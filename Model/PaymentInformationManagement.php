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

namespace Iways\PayPalPlus\Model;

class PaymentInformationManagement
{
    /**
     * @var ApiFactory
     */
    protected $payPalPlusApiFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->quoteManagement = $quoteRepository;
        $this->customerSession = $customerSession;
    }

    public function patchPayment($cartId)
    {
        $quote = $this->quoteManagement->getActive($cartId);
        return $this->payPalPlusApiFactory->create()->patchPayment($quote);
    }

    public function handleComment($paymentMethod)
    {
        $this->customerSession->setOrderComment(null);
        $additionalData = $paymentMethod->getAdditionalData();
        if (isset($additionalData['comments']) && !empty($additionalData['comments'])) {
            $this->customerSession->setOrderComment($additionalData['comments']);
        }
        return $paymentMethod;
    }
}
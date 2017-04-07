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

namespace Iways\PayPalPlus\Model\PaymentInformationManagement;

use Iways\PayPalPlus\Model\PaymentInformationManagement;
use Magento\Quote\Api\CartRepositoryInterface;

class GuestPPPPaymentInformationManagement extends PaymentInformationManagement implements \Iways\PayPalPlus\Api\GuestPPPPaymentInformationManagementInterface
{

    /**
     * @var \Magento\Quote\Api\GuestBillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\GuestPaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    protected $paymentInformationManagement;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * GuestPPPPaymentInformationManagement constructor.
     * @param \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory


    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $quoteRepository;
        parent::__construct($payPalPlusApiFactory, $quoteRepository, $customerSession);
    }

    /**
     * {@inheritDoc}
     */
    public function savePaymentInformation(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $this->billingAddressManagement->assign($cartId, $billingAddress);
        } else {
            $this->cartRepository->getActive($quoteIdMask->getQuoteId())->getBillingAddress()->setEmail($email);
        }
        $paymentMethod = $this->handleComment($paymentMethod);
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        $this->patchPayment($quoteIdMask->getQuoteId());
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentInformation($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->paymentInformationManagement->getPaymentInformation($quoteIdMask->getQuoteId());
    }
}

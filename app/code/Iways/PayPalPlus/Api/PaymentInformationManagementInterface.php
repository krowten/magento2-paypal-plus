<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Api;

/**
 * Interface for managing quote payment information
 * @api
 */
interface PPPPaymentInformationManagementInterface
{
    /**
     * Set payment information for a specified cart.
     *
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     */
    public function savePaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Get payment information
     *
     * @param int $cartId
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function getPaymentInformation($cartId);
}

<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Iways\PayPalPlus\Model\Plugin;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order\Payment;

class AfterOrderPaymentGetExtensionAttributes
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterface
     */
    protected $orderPaymentExtensionFactory;

    /**
     * @param OrderPaymentExtensionInterface $orderPaymentExtensionFactory
     */
    public function __construct(
        OrderPaymentExtensionFactory $orderPaymentExtensionFactory
    ) {
        $this->orderPaymentExtensionFactory = $orderPaymentExtensionFactory;
    }

    /**
     * Add stock item information to the product's extension attributes
     *
     * @param Payment $payment
     * @return \Magento\Catalog\Model\Product
     */
    public function afterGetExtensionAttributes(Payment $payment)
    {
        $paymentExtension = $payment->getData(AbstractExtensibleModel::EXTENSION_ATTRIBUTES_KEY);
        if ($paymentExtension === null) {
            $paymentExtension = $this->orderPaymentExtensionFactory->create();
        }
        $pppAttributes = [
            'ppp_reference_number',
            'ppp_instruction_type',
            'ppp_payment_due_date',
            'ppp_note',
            'ppp_bank_name',
            'ppp_account_holder_name',
            'ppp_international_bank_account_number',
            'ppp_bank_identifier_code',
            'ppp_routing_number',
            'ppp_amount',
            'ppp_currency'
        ];

        foreach ($pppAttributes as $pppAttribute) {
            $paymentExtension->setData($pppAttribute, $payment->getData($pppAttribute));
        }

        $payment->setExtensionAttributes($paymentExtension);
        return $paymentExtension;
    }
}

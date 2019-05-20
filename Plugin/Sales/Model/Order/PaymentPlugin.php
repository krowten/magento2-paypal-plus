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

namespace Iways\PayPalPlus\Plugin\Sales\Model\Order;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order\Payment;

class PaymentPlugin
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterface
     */
    protected $orderPaymentExtensionFactory;

    /**
     * PaymentPlugin constructor.
     * @param OrderPaymentExtensionFactory $orderPaymentExtensionFactory
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

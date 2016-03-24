<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 24.03.16
 * Time: 15:20
 */

namespace Iways\PayPalPlus\Model;


use Magento\Quote\Model\Quote;

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

    public function __construct(
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->quoteManagement = $quoteRepository;
    }

    public function patchPayment($cartId) {
        $quote = $this->quoteManagement->getActive($cartId);
        return $this->payPalPlusApiFactory->create()->patchPayment($quote);
    }
}
<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Observer;

use Iways\PayPalPlus\Helper\Data;
use Magento\Framework\Event\ObserverInterface;

class ResetObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $payPalPlusHelper;

    /**
     * ValidateObserver constructor.
     * @param Data $payPalPlusHelper
     */
    public function __construct(
        Data $payPalPlusHelper
    ) {
        $this->payPalPlusHelper = $payPalPlusHelper;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->payPalPlusHelper->resetWebProfileId();
    }
}

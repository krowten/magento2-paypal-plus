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

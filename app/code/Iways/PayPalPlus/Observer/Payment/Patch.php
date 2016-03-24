<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * Created on 03.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

namespace Iways\PayPalPlus\Observer\Payment;

class Patch implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $payPalPlusApiFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->logger = $logger;
        $this->backendSession = $backendSession;
        $this->storeManager = $storeManager;
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
        try {
            $quote = $observer->getEvent()->getQuote();
            if (
                !$this->customerSession->getPayPalPaymentPatched()
                && $quote->getPayment()
                && $quote->getPayment()->getMethodInstance()->getCode() == \Iways\PayPalPlus\Model\Payment::CODE
            ) {
                $this->payPalPlusApiFactory->create()->patchPayment($quote);
            }
        } catch (Exception $ex) {
            if ($ex->getMessage() != 'The requested Payment Method is not available.') {
                $this->logger->critical($ex);
            }
        }
        return $this;
    }
}
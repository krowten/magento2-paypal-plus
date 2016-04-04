<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalPlus\Observer;

use Magento\Framework\Event\ObserverInterface;

class ValidateObserver implements ObserverInterface
{
    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $payPalPlusApiFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;


    /**
     * ValidateObserver constructor.
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResponseInterface $response,
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_backendData = $backendData;
        $this->_coreRegistry = $coreRegistry;
        $this->_authSession = $authSession;
        $this->_response = $response;
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
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
        $pppApi = $this->payPalPlusApiFactory->create();
        $pppApi->testCredentials($this->getDefaultStoreId($observer));
        $api = $pppApi->setApiContext($this->getDefaultStoreId($observer));
        $webhook = $api->createWebhook();
        if ($webhook === false) {
            $this->messageManager->addError(
                __('Webhook creation failed.')
            );
        }
    }

    /**
     * Try to get default store id from observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getDefaultStoreId(\Magento\Framework\Event\Observer $observer)
    {
        $website = $observer->getWebsite();
        if ($website) {
            $website = $this->storeManager
                ->getWebsite($website)
                ->getDefaultGroup()
                ->getDefaultStoreId();
        }
        return $website;
    }
}

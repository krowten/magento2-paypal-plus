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
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return object|null
     */
    protected function getDefaultStoreId(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getStore();

        if ($store) {
            return $store;
        }
        return null;
    }
}

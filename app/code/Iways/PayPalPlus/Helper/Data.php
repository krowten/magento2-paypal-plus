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
 * Created on 02.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

namespace Iways\PayPalPlus\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Iways PayPalPlus Helper
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Data extends \Magento\Payment\Helper\Data
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $payPalPlusApiFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->scopeConfig = $scopeConfig;
        $this->generic = $generic;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
    }
    /**
     * Show Exception if debug mode.
     *
     * @param \Exception $e
     */
    public function handleException(\Exception $e)
    {
        if ($this->scopeConfig->getValue('iways_paypalplus/dev/debug', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->generic->addWarning($e->getData());
        }
    }

    /**
     * Build webhook listener url
     *
     * @return string
     */
    public function getWebhooksUrl()
    {
        return str_replace(
            'http://',
            'https://',
            $this->_getUrl(
                'paypalplus/index/webhooks',
                array(
                    '_forced_secure' => true,
                    '_nosid' => true,
                )
            )
        );
    }


    /**
     * Get url wrapper for security urls and form key
     *
     * @param $url
     * @param array $params
     * @param bool|true $formKey
     * @return string
     */
    public function getUrl($url, $params = array(), $formKey = true)
    {
        $isSecure = $this->request->isSecure();
        if ($isSecure) {
            $params['_forced_secure'] = true;
        } else {
            $params['_secure'] = true;
        }
        if ($formKey) {
            $params['form_key'] = $this->generic->getFormKey();
        }
        return $this->_getUrl($url, $params);
    }

    /**
     * Get deafult country id for different supported checkouts
     *
     * @return mixed
     */
    public function getDefaultCountryId()
    {
        return $this->scopeConfig->getValue('payment/account/merchant_country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Helper for saving store configuration programmatically
     *
     * @param $key
     * @param $value
     * @return boolean
     */
    public function saveStoreConfig($key, $value)
    {
        /*Mage::getModel('core/config')->saveConfig(
            $key,
            $value,
            'stores',
            $this->storeManager->getStore()->getId()
        );
        Mage::app()->getCacheInstance()->cleanType('config'); */
        return true;
    }

    /**
     * Request payment experience from PayPal for current quote.
     *
     * @return string
     */
    public function getPaymentExperience()
    {
        if ($this->scopeConfig->getValue('payment/iways_paypalplus_payment/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->payPalPlusApiFactory->create()->getPaymentExperience();
        }
        return false;
    }

    /**
     * Convert due date
     *
     * @param $date
     * @return string
     */
    public function convertDueDate($date)
    {
        $dateArray = explode('-', $date);
        $dateArray = array_reverse($dateArray);
        return implode('.', $dateArray);
    }
}
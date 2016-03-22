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
 * Created on 05.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

namespace Iways\PayPalPlus\Controller;

/**
 * PayPalPlus checkout controller
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Index extends \Magento\Checkout\Controller\Action
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $checkoutTypeOnepage;

    /**
     * @var \Iways\PayPalPlus\Helper\Data
     */
    protected $payPalPlusHelper;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $payPalPlusApiFactory;

    /**
     * @var \Iways\PayPalPlus\Model\Api
     */
    protected $payPalPlusApi;

    /**
     * @var \Iways\PayPalPlus\Model\Webhook\EventFactory
     */
    protected $payPalPlusWebhookEventFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Checkout\Model\Type\Onepage $checkoutTypeOnepage,
        \Iways\PayPalPlus\Helper\Data $payPalPlusHelper,
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Iways\PayPalPlus\Model\Api $payPalPlusApi,
        \Iways\PayPalPlus\Model\Webhook\EventFactory $payPalPlusWebhookEventFactory
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->checkoutTypeOnepage = $checkoutTypeOnepage;
        $this->payPalPlusHelper = $payPalPlusHelper;
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->payPalPlusApi = $payPalPlusApi;
        $this->payPalPlusWebhookEventFactory = $payPalPlusWebhookEventFactory;
    }
    /**
     * Index
     */
    public function indexAction()
    {
        $this->_redirect('checkout/cart');
    }

    /**
     * success
     */
    public function successAction()
    {
        try {
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getOnepage()->saveOrder();
            $this->getOnepage()->getQuote()->save();
            $this->_redirect('checkout/onepage/success');
            return true;
        } catch (Exception $ex) {
            $this->logger->critical($ex);
        }
        $this->checkoutSession->addError($this->__('There was an error with your payment.'));
        $this->_redirect('checkout/cart');
    }

    /**
     * Validate agreements bevor redirect to PayPal
     */
    public function validateAction()
    {
        if (version_compare(Mage::getVersion(), '1.8.0', '>=') && !$this->_validateFormKey()) {
            $response = array('status' => 'error', 'message' => 'Invalid form key.');
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }
        $requiredAgreements = $this->checkoutHelper->getRequiredAgreementIds();
        if ($requiredAgreements) {
            $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
            $diff = array_diff($requiredAgreements, $postedAgreements);
            if ($diff) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] =
                    $this->__('Please agree to all the terms and conditions before placing the order.');

                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
        }
        $result['success'] = true;
        $result['error'] = false;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Get Onepage checkout
     *
     * @return \Magento\Checkout\Model\Type\Onepage
     */
    protected function getOnepage()
    {
        return $this->checkoutTypeOnepage;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * Patch PayPalPayment
     */
    public function patchAction()
    {
        try {
            if (version_compare(Mage::getVersion(), '1.8.0', '>=') && !$this->_validateFormKey()) {
                $response = array('status' => 'error', 'message' => 'Invalid form key.');
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
                return;
            }
            if ($this->payPalPlusHelper->isIdevOsc()) {
                /* Save Idev_Onestepcheckout POST Data to Quote */
                $this->getLayout()->createBlock('Iways_PayPalPlus_Block_Idev_Checkout',
                    'iways_paypalplus_handle_post_block');
            } else {
                if ($this->payPalPlusHelper->isFirecheckout()) {
                    $this->getQuote()->setFirecheckoutCustomerComment($this->getRequest()->getPost('order-comment'));
                    $quote = $this->getQuote();
                    foreach (Mage::helper('checkoutfields')->getEnabledFields() as $fieldName => $fieldConfig) {
                        $value = (string)$this->getRequest()->getPost($fieldName);
                        $quote->setData($fieldName, $value);
                    }
                }

                $billing = $this->getRequest()->getPost('billing', array());
                $customerBillingAddressId = $this->getRequest()->getPost('billing_address_id', false);

                if (isset($billing['email'])) {
                    $billing['email'] = trim($billing['email']);
                }
                $this->getOnepage()->saveBilling($billing, $customerBillingAddressId);

                $shipping = $this->getRequest()->getPost('shipping', array());
                if ($billing['use_for_shipping']) {
                    $shipping = $billing;
                }
                $customerShippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                $this->getOnepage()->saveShipping($shipping, $customerShippingAddressId);

                $this->getOnepage()->saveShippingMethod($this->getRequest()->getPost('shipping_method', ''));

                $this->getOnepage()->savePayment($this->getRequest()->getPost('payment', array()));
            }
            $responsePayPal = $this->payPalPlusApiFactory->create()->patchPayment($this->getOnepage()->getQuote());
            if ($responsePayPal) {
                $response = array('status' => 'success');
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => $this->__('Please select an other payment method.')
                );
            }
        } catch (Exception $ex) {
            $response = array('status' => 'error', 'message' => $ex->getMessage());
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Listener for PayPal REST Webhooks
     */
    public function webhooksAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        try {
            /** @var \PayPal\Api\WebhookEvent $webhookEvent */
            $webhookEvent =
                $this->payPalPlusApi->validateWebhook($this->getRequest()->getRawBody());

            $this->payPalPlusWebhookEventFactory->create()->processWebhookRequest($webhookEvent);
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable')->sendResponse();
        }
    }
}
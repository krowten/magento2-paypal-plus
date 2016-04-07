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

namespace Iways\PayPalPlus\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;

/**
 * PayPalPlus checkout controller
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Create extends \Magento\Framework\App\Action\Action
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
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    protected $guestCartManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCartManagement,
        Session $customerSession
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->customerSession = $customerSession;
        parent::__construct($context);

    }

    /**
     * success
     */
    public function execute()
    {
        try {
            $cartId = $this->checkoutSession->getQuoteId();
            $result = new DataObject();
            if($this->customerSession->isLoggedIn()) {
                $this->cartManagement->placeOrder($cartId);
            } else {
                $this->guestCartManagement->placeOrder($cartId);
            }
            $result->setData('success', true);
            $result->setData('error', false);

            $this->_eventManager->dispatch(
                'checkout_controller_onepage_saveOrder',
                [
                    'result' => $result,
                    'action' => $this
                ]
            );
            $this->_redirect('checkout/onepage/success');
        } catch(\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('checkout/cart');
        }
    }
}
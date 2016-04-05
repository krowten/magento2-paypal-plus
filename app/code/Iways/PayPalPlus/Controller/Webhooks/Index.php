<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Iways\PayPalPlus\Controller\Webhooks;

use Magento\Framework\Exception\RemoteServiceUnavailableException;

/**
 * Unified IPN controller for all supported PayPal methods
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Iways\PayPalPlus\Model\Webhook\EventFactory
     */
    protected $_webhookEventFactory;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $_apiFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Iways\PayPalPlus\Model\Webhook\EventFactory $webhookEventFactory
     * @param \Iways\PayPalPlus\Model\ApiFactory $apiFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Iways\PayPalPlus\Model\Webhook\EventFactory $webhookEventFactory,
        \Iways\PayPalPlus\Model\ApiFactory $apiFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_webhookEventFactory = $webhookEventFactory;
        $this->_apiFactory = $apiFactory;
        parent::__construct($context);
    }

    /**
     * Instantiate Event model and pass Webhook request to it
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = file_get_contents('php://input');
            /** @var \PayPal\Api\WebhookEvent $webhookEvent */
            $webhookEvent = $this->_apiFactory->create()->validateWebhook($data);
            $this->_webhookEventFactory->create()->processWebhookRequest($webhookEvent);
        } catch (RemoteServiceUnavailableException $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setStatusHeader(503, '1.1', 'Service Unavailable')->sendResponse();
            /** @todo eliminate usage of exit statement */
            exit;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}

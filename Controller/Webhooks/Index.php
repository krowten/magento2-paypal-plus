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

namespace Iways\PayPalPlus\Controller\Webhooks;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Unified IPN controller for all supported PayPal methods
 */
class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

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
            if (!$webhookEvent) {
                throw new LocalizedException(__('Event not found.'));
            }
            $this->_webhookEventFactory->create()->processWebhookRequest($webhookEvent);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setStatusHeader(503, '1.1', 'Service Unavailable')->sendResponse();
        }

        return;
    }
}

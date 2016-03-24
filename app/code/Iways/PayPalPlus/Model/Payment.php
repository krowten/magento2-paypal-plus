<?php

namespace Iways\PayPalPlus\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PPP_STATUS_APPROVED = 'approved';
    const CODE = 'iways_paypalplus_payment';
    const PENDING = 'pending';

    const PPP_PUI_INSTRUCTION_TYPE = 'PAY_UPON_INVOICE';

    protected $_code = self::CODE;

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway = true;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canCaptureOnce = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = false;
    protected $_canUseCheckout = true;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Iways\PayPalPlus\Model\ApiFactory
     */
    protected $payPalPlusApiFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Iways\PayPalPlus\Helper\Data
     */
    protected $payPalPlusHelper;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory
     */
    protected $salesOrderPaymentTransactionFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iways\PayPalPlus\Model\ApiFactory $payPalPlusApiFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Iways\PayPalPlus\Helper\Data $payPalPlusHelper,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $salesOrderPaymentTransactionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->payPalPlusApiFactory = $payPalPlusApiFactory;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->payPalPlusHelper = $payPalPlusHelper;
        $this->salesOrderPaymentTransactionFactory = $salesOrderPaymentTransactionFactory;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig,
            $logger, $resource, $resourceCollection, $data);
    }

    /**
     * Authorize payment method
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @param float $amount
     *
     * @throws \Exception Payment could not be executed
     *
     * @return \Iways\PayPalPlus\Model\Payment
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $paymentId = $this->request->getParam('paymentId');
        $payerId = $this->request->getParam('PayerID');
        try {
            if ($this->scopeConfig->getValue('payment/iways_paypalplus_payment/transfer_reserved_order_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ) {
                $this->payPalPlusApiFactory->create()->patchInvoiceNumber(
                    $paymentId,
                    $payment->getOrder()->getIncrementId()
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        /**
         * @var \PayPal\Api\Payment $ppPayment
         */
        $ppPayment = $this->payPalPlusApiFactory->create()->executePayment(
            $paymentId,
            $payerId
        );

        $this->customerSession->setPayPalPaymentId(null);
        $this->customerSession->setPayPalPaymentPatched(null);

        if (!$ppPayment) {
            throw new \Magento\Framework\Exception\LocalizedException('Payment could not be executed.');
        }

        if ($paymentInstructions = $ppPayment->getPaymentInstruction()) {
            $payment->setData('ppp_pui_reference_number', $paymentInstructions->getReferenceNumber());
            $payment->setData('ppp_pui_instruction_type', $paymentInstructions->getInstructionType());
            $payment->setData(
                'ppp_pui_payment_due_date',
                $this->payPalPlusHelper->convertDueDate($paymentInstructions->getPaymentDueDate())
            );
            $payment->setData('ppp_pui_note', $paymentInstructions->getNote());

            $bankInsctructions = $paymentInstructions->getRecipientBankingInstruction();
            $payment->setData('ppp_pui_bank_name', $bankInsctructions->getBankName());
            $payment->setData('ppp_pui_account_holder_name', $bankInsctructions->getAccountHolderName());
            $payment->setData(
                'ppp_pui_international_bank_account_number',
                $bankInsctructions->getInternationalBankAccountNumber()
            );
            $payment->setData('ppp_pui_bank_identifier_code', $bankInsctructions->getBankIdentifierCode());
            $payment->setData('ppp_pui_routing_number', $bankInsctructions->getRoutingNumber());

            $ppAmount = $paymentInstructions->getAmount();
            $payment->setData('ppp_pui_amount', $ppAmount->getValue());
            $payment->setData('ppp_pui_currency', $ppAmount->getCurrency());
        }

        $transactionId = null;
        try {
            $transactions = $ppPayment->getTransactions();

            if ($transactions && isset($transactions[0])) {
                $resource = $transactions[0]->getRelatedResources();
                if ($resource && isset($resource[0])) {
                    $sale = $resource[0]->getSale();
                    $transactionId = $sale->getId();
                    if ($sale->getState() == self::PENDING) {
                        $payment->setIsTransactionPending(true);
                    }
                }
            }

        } catch (Exception $e) {
            $transactionId = $payment->getId();
        }
        $payment->setTransactionId($transactionId);

        if ($ppPayment->getState() == self::PPP_STATUS_APPROVED) {
            $payment->setStatus(self::STATUS_APPROVED);
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Magento\Payment\Model\InfoInterface
     * @param float $amount
     *
     * @return \Iways\PayPalPlus\Model\Payment
     */
    /*public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $ppRefund = $this->payPalPlusApiFactory->create()->refundPayment(
            $this->_getParentTransactionId($payment),
            $amount
        );
        $payment->setTransactionId($ppRefund->getId())->setTransactionClosed(1);
        return $this;
    }*/

    /**
     * Parent transaction id getter
     *
     * @param \Magento\Framework\DataObject $payment
     * @return string
     */
    protected function _getParentTransactionId(\Magento\Framework\DataObject $payment)
    {
        $transaction = $this->salesOrderPaymentTransactionFactory->create()->load($payment->getLastTransId(), 'txn_id');
        if ($transaction && $transaction->getParentTxnId()) {
            return $transaction->getParentTxnId();
        }
        return $payment->getLastTransId();
    }
}
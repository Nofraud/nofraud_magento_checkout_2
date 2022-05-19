<?php
namespace NoFraud\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderObserver implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
    * @var \Magento\Sales\Model\Service\InvoiceService
    */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
    * @var \Magento\Sales\Api\OrderRepositoryInterface
    */
    protected $_orderRepository;

    /**
    * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
    * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
    * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
    * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
    * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
        ) {
          $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
          $this->_invoiceService = $invoiceService;
          $this->_transactionFactory = $transactionFactory;
          $this->_invoiceRepository = $invoiceRepository;
          $this->_orderRepository = $orderRepository;
    }




    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
        $orderId = $observer->getEvent()->getOrder()->getId();
		
		$logger->info('observer for order : ' . $orderId);
        $this->createInvoice($orderId);      
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
		$checkoutSession->clearQuote()->clearStorage();
		$checkoutSession->clearQuote();
		$checkoutSession->clearStorage();
		$checkoutSession->clearHelperData();
		$checkoutSession->resetCheckout();
		$checkoutSession->restoreQuote();
    }

    protected function createInvoice($orderId)
    {
        try 
        {
			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
			$logger = new \Zend_Log();
			$logger->addWriter($writer);
			$logger->info('createInvoice');
            $order = $this->_orderRepository->get($orderId);
			
			$logger->info('payment method : ' . $order->getPayment()->getMethod());
            if ( $order && $order->getPayment()->getMethod() == 'nofraud' )
            {
				$logger->info('start automatic create invoice : ');
                $invoices = $this->_invoiceCollectionFactory->create()
                  ->addAttributeToFilter('order_id', array('eq' => $order->getId()));

                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                  $invoices = $invoices->getFirstItem();
                  $invoice = $this->_invoiceRepository->get($invoices->getId());
                  return $invoice;
                }

                if(!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment(__('Automatically INVOICED for No Fraud'), false);
                $transactionSave = $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
				
				$logger->info('curent status : ' . $order->getStatus());
				
                return $invoice;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
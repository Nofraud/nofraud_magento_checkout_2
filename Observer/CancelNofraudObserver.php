<?php

namespace NoFraud\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * NoFraud Checkout Cancel transaction module observer
 */
class CancelNofraudObserver implements ObserverInterface
{
    private $backendSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * CancelNofraudObserver constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \NoFraud\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger

     ) {
        $this->orderRepository = $orderRepository;
        $this->_messageManager = $messageManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->_curl = $curl;
        $this->logger = $logger;
        $this->backendSession  = $backendSession;
    }
    
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $order = $this->orderRepository->get($creditmemo->getOrderId());

        $this->backendSession->setCancelNofraudTranscation(true);
        
    }
}

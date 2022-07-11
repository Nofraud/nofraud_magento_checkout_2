<?php

namespace NoFraud\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * NoFraud Checkout Cancel transaction module observer
 */
class StatusChangeNofraudObserver implements ObserverInterface
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

        $order = $observer->getEvent()->getOrder(); 

        $cancelNofraudTranscation = $this->backendSession->getCancelNofraudTranscation();

        if( $order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED ){
            if ($cancelNofraudTranscation) {
                $transactionId = $this->getRefundTransactionId($order);
                if($transactionId){
                    $this->cancelNofraudTranscation($transactionId);
                }
                $this->backendSession->unsCancelNofraudTranscation();
            }
        }       
    }

    /**
     * get payment Transaction Id
     */
    public function getRefundTransactionId($order) {
        $nofraudcheckout = $order->getData("nofraudcheckout");
        if(!$nofraudcheckout) {
            return false;
        }
        $nofraudcheckoutArray = json_decode($nofraudcheckout, true);
        if(isset($nofraudcheckoutArray["transaction_id"])) {
            $transaction_id = explode("#",$nofraudcheckoutArray["transaction_id"]);
            return $transaction_id[1] ?? "";
        }
        return false;
    }

    /**
     * Refund from NoFraud
     */
    private function cancelNofraudTranscation($transaction_id) {

        $apiUrl = $this->checkoutHelper->getCancelTransactionApiUrl();
        $nfToken = $this->checkoutHelper->getCanelTransactionNfToken();

        $data = [
            "nf_token" => $nfToken,
            "transaction_id" => (string) $transaction_id,
        ];

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cancel-transaction-api-'.date("d-m-Y").'.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info("== Request information ==");
        $logger->info(print_r($data, true));

        $logger->info($apiUrl);
        $logger->info($nfToken);
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $logger->info("== Response Information ==");
            $logger->info(print_r($response,true));

            if($response) {
                $responseArray = json_decode($response, true);
                if($responseArray && isset($responseArray["code"]) && $responseArray["code"] == 200) {
                    $this->_messageManager->addSuccess(__($responseArray["message"]));
                } else {
                    $this->_messageManager->addError(__($responseArray["message"]));
                }
            } else {
                $this->_messageManager->addError(__("No Response from API endpoint.."));
            }
        } catch(\Exception $e) {
            $this->_messageManager->addError(__($e->getMessage()));
        }
    }
}
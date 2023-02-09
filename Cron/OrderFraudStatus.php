<?php

namespace NoFraud\Checkout\Cron;

use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;

class OrderFraudStatus
{
    const ORDER_REQUEST = 'status';

    const REQUEST_TYPE  = 'GET';

    protected $orderStatusesKeys = [
        'pass',
        'review',
        'fail',
        'error',
    ];

    private $orders;

    private $storeManager;

    private $requestHandler;

    private $configHelper;

    private $apiUrl;

    private $orderProcessor;

    private $moduleManager;

    protected $_logger;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orders,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \NoFraud\Connect\Api\RequestHandler $requestHandler,
        \NoFraud\Checkout\Helper\Data $dataHelper,
        \NoFraud\Connect\Api\ApiUrl $apiUrl,
        \NoFraud\Connect\Order\Processor $orderProcessor,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->orders = $orders;
        $this->orderStatusCollection = $orderStatusCollection;
        $this->storeManager = $storeManager;
        $this->requestHandler = $requestHandler;
        $this->dataHelper = $dataHelper;
        $this->apiUrl = $apiUrl;
        $this->orderProcessor = $orderProcessor;
        $this->moduleManager = $moduleManager;
    }

    public function execute()
    {
        $storeList = $this->storeManager->getStores();
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/NofraudCheckout.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        foreach ($storeList as $store) {
            $storeId = $store->getId();
            if (!$this->dataHelper->getEnabled($storeId)) {
                return;
            }
            $orders = $this->readOrders($storeId, $logger);
            $this->updateOrdersFromNoFraudApiResult($orders, $storeId, $logger);
        }
    }

    public function readOrders($storeId, $logger)
    {
        $orders = $this->orders->create()
            ->addFieldToSelect('status')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('nofraud_checkout_status')
            ->addFieldToSelect('nofraud_checkout_screened')
            ->addFieldToSelect('nofraudcheckout')
            ->setOrder('status', 'desc');

        $select = $orders->getSelect()
            ->where('store_id = '.$storeId)
            ->where('nofraud_checkout_screened != 1')
            ->where('status = \'processing\' OR status = \'pending_payment\' OR status = \'payment_review\' OR nofraud_checkout_status = \'review\'');
        $logger->info($orders->getSelect());
        return $orders;
    }

    public function getRefundTransactionId($order) {
        $nofraudcheckout = $order->getData("nofraudcheckout");
        error_log("\n nofraudcheckout ".$nofraudcheckout,3,BP."/var/log/checkoutlog.log");
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

    private function updateTransactionStatus($transaction_id, $logger) {
        $nfToken    = $this->dataHelper->getNofrudCheckoutAppNfToken();
        $apiUrl     = $this->dataHelper->getStatusByUrlApiUrl().$nfToken."/".$transaction_id;
        $logger->info("\n".$apiUrl);
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
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            $logger->info("\n before call");
            $response = curl_exec($curl);
            curl_close($curl);
            $responseArray = json_decode($response, true);
            $logger->info("\n Nofraud response :".print_r($responseArray,true));
            $logger->info("\n after call");
            return $responseArray;
        } catch(\Exception $e) {
            $logger->info("\n".$e->getMessage());
        }
    }

    public function updateOrdersFromNoFraudApiResult($orders, $storeId, $logger)
    {
        foreach ($orders as $order) {
            try {
                if ($order && $order->getPayment()->getMethod() == 'nofraud') {
                    $transactionId = $this->getRefundTransactionId($order);
                    $logger->info("\n transactionId ".$transactionId." <=> ".$order->getId());
                    if($transactionId !== false){
                        $logger->info("\n Found transactionId ".$transactionId);
                        $nofraudCheckoutResponse = $this->updateTransactionStatus($transactionId, $logger);
                        $logger->info("\n inside transactionId ".print_r($nofraudCheckoutResponse));
                        if ( $nofraudCheckoutResponse && isset($nofraudCheckoutResponse["Errors"]) ){
                            continue;
                        } elseif ( $nofraudCheckoutResponse && isset($nofraudCheckoutResponse["decision"]) ){
                            $order->setNofraudCheckoutScreened(true);
                            if (isset($nofraudCheckoutResponse['decision'])) {
                                $statusName = $nofraudCheckoutResponse['decision'];
                            }else{
                                $statusName = 'error';
                            }
                            error_log("\n status Name ".$statusName." <=> ".$order->getId(),3,BP."/var/log/bothenable.log");
                            if (isset($statusName)) {
                                $newStatus = $this->dataHelper->getCustomStatusConfig($statusName, $storeId);
                                if (!empty($newStatus)) {
                                    $newState = $this->getStateFromStatus($newStatus);
                                    error_log("\n new state ".$newState." <=> ".$order->getId(),3,BP."/var/log/bothenable.log");
                                    if ($newState == Order::STATE_HOLDED) {
                                        $order->hold();
                                    } else if ($newState) {
                                        $order->setStatus($newStatus)->setState($newState);
                                        if( isset($nofraudCheckoutResponse['decision']) && ($nofraudCheckoutResponse['decision'] == 'pass') ){
                                            $order->setNofraudCheckoutStatus($nofraudCheckoutResponse['decision']);
                                        }
                                    }
                                }
                            }
                            $order->save();
                            error_log("\n newStatus ".$newStatus." <=> ".$order->getId(),3,BP."/var/log/bothenable.log");
                        }
                    }
                }
            } catch (\Exception $exception) {
                $logger->info("Error for Order#".$order['increment_id']);
                $logger->info($exception->getMessage());
            }
        }
    }

    public function getStateFromStatus($state)
    {
        $statuses = $this->orderStatusCollection->create()->joinStates();
        if (empty($this->stateIndex)) {
            foreach ($statuses as $status) {
                $this->stateIndex[$status->getStatus()] = $status->getState();
            }
        }
        return $this->stateIndex[$state] ?? null;
    }
}

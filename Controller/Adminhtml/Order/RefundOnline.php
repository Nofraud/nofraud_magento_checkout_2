<?php
namespace NoFraud\Checkout\Controller\Adminhtml\Order;

class RefundOnline extends \Magento\Backend\App\Action 
{
	/**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \NoFraud\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->checkoutHelper = $checkoutHelper;
        $this->_curl = $curl;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
    * Execute 
    */
    public function execute() {
        $returns = [];
        $returns = ["success" => false, "message" => "We can\'t save the credit memo right now."];
    	try {
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                $returns["value"] = (float) $creditmemo->getGrandTotal();
                $postData = [
                    "amount" => $creditmemo->getGrandTotal(),
                    "authId" => (string) $this->getRequest()->getParam('authId'),
                ];
                $refundResponse = $this->makeRefund($postData);
                if($refundResponse && isset($refundResponse["success"]) && $refundResponse["success"] == true) {
                    $returns["success"] = true;  
                } else if($refundResponse && isset($refundResponse["error_message"])) {
                    $returns["message"] = $refundResponse["error_message"];
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $returns["message"] = $e->getMessage();
            $returns["success"] = false;
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $returns["message"] = $e->getMessage();
            $returns["success"] = false;
        }
        return $this->resultJsonFactory->create()->setData($returns);
    }

    /**
    * Provide user Level permission
    */
    protected function _isAllowed() {
    	return true;
    }

    /**
    * Refund from NoFraud
    */
    private function makeRefund($data) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/refund-api-'.date("d-m-Y").'.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        
        $logger->info("== Request information ==");
        $logger->info(print_r($data, true));
        $logger->info(print_r($this->getRequest()->getParams(), true));
        
        $returnsFund = [];
        $returnsFund = ["success" => false];
        
        $apiUrl = $this->checkoutHelper->getRefundApiUrl();
        $apikey = $this->checkoutHelper->getRefundApiKey();
        
        $logger->info($apikey);
        $logger->info($apiUrl);
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
                    "Content-Type: application/json",
                    "x-nf-api-token: {$apikey}"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $logger->info("== Response Information ==");
            $logger->info(print_r($response,true));

            if($response) {
                $responseArray = json_decode($response, true);
                if($responseArray && isset($responseArray["success"]) && $responseArray["success"] == true) {
                    $returnsFund["success"] = true;
                } else if($responseArray && isset($responseArray["errorMsg"])) {
                    $returnsFund["error_message"] = $responseArray["errorMsg"];
                }
            } else {
                $returnsFund = ["error_message" => "No Response from API endpoint.", "success" => false];    
            }
        } catch(\Exception $e) {
            $returnsFund = ["error_message" => $e->getMessage(), "success" => false];
        }
        return $returnsFund;
    }
}
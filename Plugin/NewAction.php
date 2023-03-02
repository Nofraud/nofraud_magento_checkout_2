<?php
namespace NoFraud\Checkout\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface;

class NewAction
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    protected $_url;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \NoFraud\Checkout\Helper\Data $checkoutHelper,
        OrderRepositoryInterface $orderRepository = null
    )
    {
        $this->messageManager   = $messageManager;
        $this->responseFactory  = $responseFactory;
        $this->_url             = $url;
        $this->checkoutHelper   = $checkoutHelper;
        $this->orderRepository  = $orderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
    }

    /**
     * before execute controller plugin
     */

    public function beforeExecute(\Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction $subject)
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        try {
            $order         = $this->orderRepository->get($orderId);
            $authorizedId  = $this->getOrderAuthorizedId($order);
            if($authorizedId) {
                $this->captureNofraudTranscation($authorizedId);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Authrozied Id.' . $authorizedId)
                );
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The order does not allow an invoice to be created.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirectToOrderPage($orderId);
        }
        return null;
    }

    /**
     * redirect to order grid
     */

    public function _redirectToOrderPage($orderId)
    {
        $redirectionUrl = $this->_url->getUrl('sales/order/view', ['order_id' => $orderId]);
        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
    }

    /**
     * get payment Authorized Id
     */
    public function getOrderAuthorizedId($order)
    {
        $nofraudcheckout = $order->getData("nofraudcheckout");
        if(!$nofraudcheckout) {
            return false;
        }
        $nofraudcheckoutArray = json_decode($nofraudcheckout, true);
        if(isset($nofraudcheckoutArray["transaction_id"])) {
            $transaction_id = explode("#",$nofraudcheckoutArray["transaction_id"]);
            return $transaction_id[0] ?? "";
        }
        return false;
    }

    /**
     * Capture from NoFraud
     */
    private function captureNofraudTranscation($authorizedId) {

        $apiUrl     = $this->checkoutHelper->getCaptureTransactionApiUrl();
        $apikey     = $this->checkoutHelper->getCaptureApiKey();

        $data = [
            "authId" => (string) $authorizedId,
        ];

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/capture-transaction-api-'.date("d-m-Y").'.log');
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
                    "Content-Type: application/json",
                    "X-NF-HOOK-AUTH: {$apikey}"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $logger->info("== Response Information ==");
            $logger->info(print_r($response,true));

            if($response) {
                $responseArray = json_decode($response, true);
                if($responseArray && isset($responseArray["success"]) && $responseArray["success"] !== true) {
                    $this->_messageManager->addSuccess(__($responseArray["message"]));
                } else {
                    $this->_messageManager->addError(__($responseArray["message"]));
                }
            } else {
                $this->_messageManager->addError(__("No Response from API endpoint.."));
            }
        } catch(\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
?>
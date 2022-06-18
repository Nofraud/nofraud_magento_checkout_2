<?php
namespace NoFraud\Checkout\Block\Adminhtml\Order\Creditmemo;

class RefundButton extends \Magento\Backend\Block\Template
{
	/**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Framework\Registry $registry, 
    	array $data = []
    ) {
    	$this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
    * get Current Order
    */
    public function getCurrentCreditmemo() {
    	return $this->_registry->registry("current_creditmemo");
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
            return $transaction_id[0] ?? "";
        }
        return false;
    }
}
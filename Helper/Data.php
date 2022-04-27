<?php

namespace NoFraud\Checkout\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_config;
    protected $_storeManager;
    
    public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
	
	public function getMerchantId()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/merchant',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

	public function getAccessTokenNotLogin()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/access_token_not_login',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
	public function getApiSourceJs()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/api_source_js',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
}

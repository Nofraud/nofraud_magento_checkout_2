<?php
namespace NoFraud\Checkout\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_config;

    protected $_storeManager;

    const PROD_API_SOURCE_JS = "https://cdn-checkout.nofraud.com/scripts/nf-src-magento.js";

    const STAG_API_SOURCE_JS = "https://cdn-checkout-qe2.nofraud-test.com/scripts/nf-src-magento.js";

    const DEV_API_SOURCE_JS = "https://dynamic-checkout-test.nofraud-test.com/latest/scripts/nf-src-magento.js";

    const PROD_REFUND_API_URL = "https://dynamic-api-checkout.nofraud.com/api/v1/hooks/refund/";

    const STAG_REFUND_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/refund/";

    const DEV_REFUND_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/refund/";

    const PROD_PORTAL_BASE_URL = "https://portal-qe2.nofraud-test.com";

    const STAG_PORTAL_BASE_URL = " https://portal-qe2.nofraud-test.com";

    const DEV_PORTAL_BASE_URL = "https://portal.nofraud.com";

    public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * get Nofruad checkout mode
     */
    public function getNofraudAdvanceListMode()
    {
        return $this->scopeConfig->getValue(
            'nofraud/advance/list_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
    * get Merchant Id
    */
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
	
    /**
    * get API Source JS URL
    */
	public function getApiSourceJs()
    {
        $checkoutMode = $this->getNofraudAdvanceListMode();

        if( strcmp($checkoutMode,"prod") === 0 ){

            return self::PROD_API_SOURCE_JS;

        }elseif( strcmp($checkoutMode,"stag") === 0 ){

            return self::STAG_API_SOURCE_JS;

        }elseif( strcmp($checkoutMode,"dev") === 0 ) {

            return self::DEV_API_SOURCE_JS;

        }
    }
	
    /**
    * get Refund APi URL
    */
    public function getRefundApiUrl()
    {
        $checkoutMode = $this->getNofraudAdvanceListMode();

        $merchantId   = $this->getMerchantId();

        if( strcmp($checkoutMode,"prod") === 0 ){

            return self::PROD_REFUND_API_URL.$merchantId;

        }elseif( strcmp($checkoutMode,"stag") === 0 ){

            return self::STAG_REFUND_API_URL.$merchantId;

        }elseif( strcmp($checkoutMode,"dev") === 0 ) {

            return self::DEV_REFUND_API_URL.$merchantId;

        }

    }

    /**
     * get Cancel APi URL
     */
    public function getCancelTransactionApiUrl()
    {
        $checkoutMode = $this->getNofraudAdvanceListMode();

        $merchantId   = $this->getMerchantId();

        if( strcmp($checkoutMode,"prod") === 0 ){

            return self::PROD_PORTAL_BASE_URL.$merchantId;

        }elseif( strcmp($checkoutMode,"stag") === 0 ){

            return self::STAG_PORTAL_BASE_URL.$merchantId;

        }elseif( strcmp($checkoutMode,"dev") === 0 ) {

            return self::DEV_PORTAL_BASE_URL.$merchantId;

        }

    }

    /**
    * get Refund APi Key
    */
    public function getRefundApiKey()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

<?php
namespace NoFraud\Checkout\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_config;

    protected $_storeManager;

    protected $orderStatusesKeys = [
        'pass',
        'review',
        'fail',
        'error',
    ];

    const PROD_API_SOURCE_JS = "https://cdn-checkout.nofraud.com/scripts/nf-src-magento.js";

    const STAG_API_SOURCE_JS = "https://cdn-checkout-qe2.nofraud-test.com/scripts/nf-src-magento.js";

    const DEV_API_SOURCE_JS = "https://dynamic-checkout-test.nofraud-test.com/latest/scripts/nf-src-magento.js";

    const PROD_REFUND_API_URL = "https://dynamic-api-checkout.nofraud.com/api/v1/hooks/refund/";

    const STAG_REFUND_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/refund/";

    const DEV_REFUND_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/refund/";

    const PROD_CAPTURE_API_URL = "https://dynamic-api-checkout.nofraud.com/api/v1/hooks/capture/";

    const STAG_CAPTURE_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/capture/";

    const DEV_CAPTURE_API_URL = "https://dynamic-checkout-api-staging2.nofraud-test.com/api/v1/hooks/capture/";

    const PROD_PORTAL_BASE_URL = "https://portal.nofraud.com/api/v1/transaction-update/cancel-transaction";

    const STAG_PORTAL_BASE_URL = " https://portal-qe2.nofraud-test.com/api/v1/transaction-update/cancel-transaction";

    const DEV_PORTAL_BASE_URL = "https://portal-qe2.nofraud-test.com/api/v1/transaction-update/cancel-transaction";

    const PROD_NFAPI_BASE_URL = "https://api.nofraud.com/status_by_url/";

    const STAG_NFAPI_BASE_URL = "https://api-qe2.nofraud-test.com/status_by_url/";

    const DEV_NFAPI_BASE_URL = "https://api-qe2.nofraud-test.com/status_by_url/";

    const ORDER_STATUSES = 'nofraud/order_statuses';

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
	public function getEnabled()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/enabled',
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
     * Get Capture APi URL
     */
    public function getCaptureTransactionApiUrl()
    {
        $checkoutMode = $this->getNofraudAdvanceListMode();
        $merchantId   = $this->getMerchantId();
        if( strcmp($checkoutMode,"prod") === 0 ){
            return self::PROD_CAPTURE_API_URL.$merchantId;
        }elseif( strcmp($checkoutMode,"stag") === 0 ){
            return self::STAG_CAPTURE_API_URL.$merchantId;
        }elseif( strcmp($checkoutMode,"dev") === 0 ) {
            return self::DEV_CAPTURE_API_URL.$merchantId;
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
        if( strcmp($checkoutMode,"prod") === 0 ){
            return self::PROD_PORTAL_BASE_URL;
        }elseif( strcmp($checkoutMode,"stag") === 0 ){
            return self::STAG_PORTAL_BASE_URL;
        }elseif( strcmp($checkoutMode,"dev") === 0 ) {
            return self::DEV_PORTAL_BASE_URL;
        }
    }

    /**
     * get Status By Url APi URL
     */
    public function getStatusByUrlApiUrl()
    {
        $checkoutMode = $this->getNofraudAdvanceListMode();
        if( strcmp($checkoutMode,"prod") === 0 ){
            return self::PROD_NFAPI_BASE_URL;
        }elseif( strcmp($checkoutMode,"stag") === 0 ){
            return self::STAG_NFAPI_BASE_URL;
        }elseif( strcmp($checkoutMode,"dev") === 0 ) {
            return self::DEV_NFAPI_BASE_URL;
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

    /**
     * get Cancel Transaction nf_token
     */

    public function getNofrudCheckoutAppNfToken()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/nf_token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * get Cancel Transaction nf_token
     */
    
    public function getCanelTransactionNfToken()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/nf_token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get Capture APi Key
     */
    public function getCaptureApiKey()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomStatusConfig($statusName, $storeId = null)
    {
        if (!in_array($statusName,$this->orderStatusesKeys)) {
            return;
        }
        $path = self::ORDER_STATUSES . '/' . $statusName;
        return $this->_getConfigValueByStoreId($path, $storeId);
    }

    private function _getConfigValueByStoreId($path, $storeId)
    {
        if (is_null($storeId)) {
            return $this->scopeConfig->getValue($path);
        }
        $value = $this->scopeConfig->getValue($path, 'store', $storeId);
        if(empty($value)){
            $value = $this->scopeConfig->getValue($path);
        }
        return $value;
    }
}

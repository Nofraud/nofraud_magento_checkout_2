<?php

namespace NoFraud\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Locale\Resolver;

class NoFraudCheckoutData extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * NoFruad Active or not config path
     */
    const XML_PATH_ENABLED = 'nofraud/general/enabled';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $_quoteIdMaskFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Resolver $localeResolver
    ){
        $this->scopeConfig          = $scopeConfig;
        $this->_checkoutSession     = $checkoutSession;
        $this->_cart                = $cart;
        $this->_quoteIdMaskFactory  = $quoteIdMaskFactory;
        $this->_customerSession     = $customerSession;
        $this->_storeManager        = $storeManager;
        $this->localeResolver       = $localeResolver;
    }

    public function getConfig($config_path){
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSectionData() {
        $isLoggedIn     = $this->_customerSession->isLoggedIn();
        $quoteIdMask    = $this->_quoteIdMaskFactory->create();

        if($isLoggedIn){
            $quoteId    = $this->_cart->getQuote()->getId();
            $cartId     = $quoteId;
        }else{
            $quoteId    = $this->_checkoutSession->getQuote()->getId();
            $cartId     = $quoteIdMask->load($quoteId,'quote_id')->getMaskedId();
        }

        $isNofraudenabled = (int) $this->getConfig(self::XML_PATH_ENABLED);
        $currencyCode     = $this->getCurrentCurrencyCode();
        $localeCode       = $this->getCurrentLocale();
        $storeCode        = $this->getCurrentStoreCode();
        return [
            'quote_id'              => $cartId,
            'is_logged'             => $isLoggedIn,
            'isNofraudenabled'      => $isNofraudenabled,
            'currencycode'          => $currencyCode,
            'languagecode'          => $localeCode,
            'storecode'             => $storeCode
        ];
    }

    /**
    * Get store currency code
    *
    * @return string
    */
    public function getCurrentCurrencyCode() {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
    * Get store language code
    *
    * @return string
    */
    public function getCurrentLocale(){
        $currentLocaleCode = $this->localeResolver->getLocale(); 
        $languageCode = strstr($currentLocaleCode, '_', true);
        return $languageCode;
    }

    /**
     * Get store store code
     *
     * @return string
     */
    public function getCurrentStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }
}
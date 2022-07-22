<?php

namespace NoFraud\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

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
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ){
        $this->scopeConfig          = $scopeConfig;
        $this->_checkoutSession     = $checkoutSession;
        $this->_cart                = $cart;
        $this->_quoteIdMaskFactory  = $quoteIdMaskFactory;
        $this->_customerSession     = $customerSession;
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
        error_log("\n quoteId: ".$cartId,3,BP."/var/log/NFC.log");

        error_log("\n is logged in: ".$isLoggedIn,3,BP."/var/log/NFC.log");

        $isNofraudenabled = (int) $this->getConfig(self::XML_PATH_ENABLED);
        
        return [
            'quote_id' => $cartId,
            'is_logged' => $isLoggedIn,
            'isNofraudenabled' => $isNofraudenabled
        ];
    }
}
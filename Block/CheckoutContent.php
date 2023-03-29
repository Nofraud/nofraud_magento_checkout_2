<?php

namespace NoFraud\Checkout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\QuoteIdMaskFactory;

class CheckoutContent extends Template
{
    protected $checkoutSession;

    protected $scopeConfig;

    protected $_customerSession;

    protected $_cart;

    protected $_quoteIdMaskFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CheckoutSession      $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        CustomerSession      $customerSession,
        Cart                 $cart,
        QuoteIdMaskFactory   $quoteIdMaskFactory,
        array $data = []
    ) {
        $this->checkoutSession      = $checkoutSession;
        $this->scopeConfig          = $scopeConfig;
        $this->_customerSession     = $customerSession;
        $this->_cart                = $cart;
        $this->_quoteIdMaskFactory  = $quoteIdMaskFactory;
        parent::__construct($context, $data);
    }

    /**
     * Process Checkout session and return QuoteId.
     *
     * @return  string $quoteId
     */
    // public function getCustomerQuoteId()
    // {
    //     $quoteId = $this->checkoutSession->getQuote()->getId();
    //     return $quoteId;
    // }

    public function getCustomerQuoteId()
    {
        $isLoggedIn     = $this->_customerSession->isLoggedIn();
        $quoteIdMask    = $this->_quoteIdMaskFactory->create();
        if ($isLoggedIn) {
            $quoteId    = $this->_cart->getQuote()->getId();
            $cartId     = $quoteId;
        } else {
            $quoteId    = $this->checkoutSession->getQuote()->getId();
            $cartId     = $quoteIdMask->load($quoteId, 'quote_id')->getMaskedId();
        }
        return $cartId;
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
}

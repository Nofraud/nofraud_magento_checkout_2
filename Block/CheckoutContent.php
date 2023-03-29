<?php

namespace NoFraud\Checkout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CheckoutContent extends Template
{
    protected $checkoutSession;

    protected $scopeConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CheckoutSession      $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig     = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Process Checkout session and return QuoteId.
     *
     * @return  string $quoteId
     */
    public function getCustomerQuoteId()
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        return $quoteId;
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

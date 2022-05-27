<?php

namespace NoFraud\Checkout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class NoFraud implements ArgumentInterface
{
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        return $this->scopeConfig->getValue(
            'nofraud/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

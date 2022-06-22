<?php

namespace NoFraud\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigChangeObserver implements ObserverInterface
{
    const XML_PATH_TELEPHONE_SHOW = 'customer/address/telephone_show';

    protected $scopeConfig;

    /**
     * ConfigChange constructor.
     * @param WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    public function execute(EventObserver $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $Showtelephone =   $this->scopeConfig->getValue(self::XML_PATH_TELEPHONE_SHOW, $storeScope);

        if ($Showtelephone != 'opt') {
            $this->configWriter->save(self::XML_PATH_TELEPHONE_SHOW, 'opt');
            return $this;
        }
    }
}

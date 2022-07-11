<?php

namespace NoFraud\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;


class Custom extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * NoFruad Active or not config path
     */
    const XML_PATH_ENABLED = 'nofraud/general/enabled';

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig($config_path){
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSectionData() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLogged = $customerSession->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        $quoteIdMask = $objectManager->get('Magento\Quote\Model\QuoteIdMaskFactory')->create();

        if($isLogged){
            $cartObj = $objectManager->get('\Magento\Checkout\Model\Cart');
            $quoteId = $cartObj->getQuote()->getId();
            $cartId = $quoteId;
        }else{
            $quoteId     = $objectManager->get('Magento\Checkout\Model\Session')->getQuote()->getId();
            $cartId      = $quoteIdMask->load($quoteId,'quote_id')->getMaskedId();
        }
        error_log("\n quoteId: ".$cartId,3,BP."/var/log/NFC.log");

        error_log("\n is logged in: ".$isLogged,3,BP."/var/log/NFC.log");

        $isNofraudenabled = (int) $this->getConfig(self::XML_PATH_ENABLED);
        
        return [
            'quote_id' => $cartId,
            'is_logged' => $isLogged,
            'isNofraudenabled' => $isNofraudenabled
        ];
    }
}
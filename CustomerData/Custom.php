<?php

namespace NoFraud\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;


class Custom extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    public function getSectionData() {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->get('Magento\Framework\App\Http\Context');
		$isLogged = $customerSession->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
		
		$quoteIdMask = $objectManager->get('Magento\Quote\Model\QuoteIdMaskFactory')->create();
		//$quoteId = @$this->getQuote()->getId();
		$quoteId = $objectManager->get('Magento\Checkout\Model\Session')->getQuote()->getId();
        return [
            'quote_id' => $quoteIdMask->load($quoteId,'quote_id')->getMaskedId(),
            'is_logged' => $isLogged
        ];
    }
}
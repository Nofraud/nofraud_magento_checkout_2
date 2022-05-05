<?php

namespace NoFraud\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;


class Custom extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    public function getSectionData123() {

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
    public function getSectionData() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLogged = $customerSession->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        $quoteIdMask = $objectManager->get('Magento\Quote\Model\QuoteIdMaskFactory')->create();

        if($isLogged)
        {
            $cartObj = $objectManager->get('\Magento\Checkout\Model\Cart');
            $quoteId = $cartObj->getQuote()->getId();
            //error_log("\n quoteId ".$quoteId,3,BP."/var/log/NFC.log");
            //$cartId  = $quoteIdMask->load($quoteId,'quote_id')->getMaskedId();
            //error_log("\n cartId ".$cartId,3,BP."/var/log/NFC.log");
            $cartId = $quoteId;
        }
        else
        {
            $quoteId     = $objectManager->get('Magento\Checkout\Model\Session')->getQuote()->getId();
            $cartId      = $quoteIdMask->load($quoteId,'quote_id')->getMaskedId();

        }
        error_log("quoteId".$cartId,3,BP."/var/log/NFC.log");

        error_log("is logged in".$isLogged,3,BP."/var/log/NFC.log");

        return [
            'quote_id' => $cartId,
            'is_logged' => $isLogged
        ];
    }
}
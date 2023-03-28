<?php

namespace NoFraud\Checkout\Plugin;

class GiftCardPlugin
{
    public function afterCollect(
        \Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Quote\GiftCard $subject,
        $result,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if ($quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            $amGiftcardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
            $giftCards       = $amGiftcardQuote->getGiftCards();
            $amGiftCards     = $total->getAmGiftCards();

            if ($amGiftCards) {
                foreach ($giftCards as &$giftCard) {
                    foreach ($amGiftCards as $amGiftCard) {
                        if ($amGiftCard['id'] == $giftCard['id']) {
                            $usedAmount = $amGiftCard['amount'];
                            $giftCard['applied'] = $usedAmount;
                        }
                    }
                }
                $amGiftcardQuote->setGiftCards($giftCards);
            }
        }
        return $result;
    }
}

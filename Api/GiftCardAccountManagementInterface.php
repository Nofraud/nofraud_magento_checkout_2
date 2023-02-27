<?php

namespace NoFraud\Checkout\Api;

interface GiftCardAccountManagementInterface
{
    /**
     * Add gift card to the cart.
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     * @return array
     */
    public function applyGiftCardToCart();
}

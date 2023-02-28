<?php

namespace NoFraud\Checkout\Api;

interface GiftCardAccountManagementInterface
{
    /**
     * Add gift card to the cart.
     * @param boolean $is_loggedin
     * @param string|int $cartId
     * @param string $giftCardCode
     * @return array
     */
    public function applyGiftCardToCart();

     /**
     * Remove gift card from the cart.
     * @param boolean $is_loggedin
     * @param string|int $cartId
     * @param string $giftCardCode
     * @return array
     */
    public function removeGiftCardFromCart();
}

<?php

namespace NoFraud\Checkout\Api;

interface CartManagementInterface
{
    /**
     * @param int $cartId
     * @return bool
     */
    public function removeAddresses($cartId);
}

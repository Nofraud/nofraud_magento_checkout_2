<?php

namespace NoFraud\Checkout\Api;

interface ValidateMerchantShopInterface
{
    /**
     * return status messages
     * @api
     * @param mixed $data
     * @return array
     */
    public function validateMerchantShop();
}

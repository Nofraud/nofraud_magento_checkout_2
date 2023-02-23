<?php

namespace NoFraud\Checkout\Api;

interface StoreCreditInterface
{
    /**
     * @param int $customerId
     * @return array
     */
    public function getstoreCreditByCustomerId();
}

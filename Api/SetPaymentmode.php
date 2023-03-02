<?php
namespace NoFraud\Checkout\Api;
interface SetPaymentmode
{
    /**
     * return status messages
     * @api
     * @param mixed $data
     * @return array
     */
    public function paymentmodeConfiguration($data);
}
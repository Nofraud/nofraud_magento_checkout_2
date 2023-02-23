<?php
namespace NoFraud\Checkout\Api;
interface SetConfiguration
{
    /**
     * return placed order status
     * @api
     * @param mixed $data
     * @return array
     */
    public function enableConfiguration($data);
}
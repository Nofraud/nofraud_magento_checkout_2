<?php
namespace NoFraud\Checkout\Api;
interface SetOrderAttributes
{
	/**
     * return placed order status
     * @api
     * @param mixed $data
     * @return array
     */
    public function updateOrderAttributes($data);
}
<?php
namespace NoFraud\Checkout\Api;

interface PhonenumberInterface
{
    /**
     * return status messages
     * @api
     * @param mixed $data
     * @return array
     */
    public function getPhonenumberMode();

    /**
     * return placed order status
     * @api
     * @param mixed $data
     * @return array
     */
    public function setPhonenumberMode($data);
}

<?php
namespace NoFraud\Checkout\Api;
interface GetProductImage
{
    /**
     * @api
     * @param  string $sku
     * @return array
     */
    public function getProductImageUrl($sku);
}
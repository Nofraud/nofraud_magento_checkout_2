<?php

namespace NoFraud\Checkout\Api;

interface CurrencyInformationInterface
{
    /**
     * @param string
     * @return array
     */
    public function getCurrencyInformation($currencyCode);
}

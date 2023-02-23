<?php

namespace NoFraud\Checkout\Api;

interface GiftCardAccountRepositoryInterface
{
    /**
     * @param int
     * @return array
     */
    public function getCertificateDetails();
}

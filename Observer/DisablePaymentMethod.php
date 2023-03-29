<?php

namespace NoFraud\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class DisablePaymentMethod implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $paymentCode = $method->getCode();
        if ($paymentCode == 'nofraud') {
            $result->setData('is_available', false);
        }
    }
}

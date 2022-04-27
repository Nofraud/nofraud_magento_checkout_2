<?php
 
namespace NoFraud\Checkout\Model;
 
/**
 * Pay In Store payment method model
 */
class NoFraud extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'nofraud';
}
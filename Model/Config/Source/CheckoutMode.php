<?php
 
namespace NoFraud\Checkout\Model\Config\Source;

class CheckoutMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'prod', 'label' => __('Production')],
            ['value' => 'stag', 'label' => __('Sandbox - Staging/Testing')],
            ['value' => 'dev', 'label' => __('Sandbox - Dev')]
        ];
    }
}
<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\ValidateMerchantShopInterface;

use Magento\Framework\Module\Manager;

use Magento\Framework\ObjectManagerInterface;

class ValidateMerchantShop implements ValidateMerchantShopInterface
{
    protected $request;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        Manager                 $moduleManager,
        ObjectManagerInterface  $objectManager
    ) {
        $this->request         =  $request;
        $this->moduleManager   =  $moduleManager;
        $this->objectManager   =  $objectManager;
    }

    public function validateMerchantShop()
    {
        if ($this->moduleManager->isEnabled('NoFraud_Checkout')) {
            try {
                      $response = [
                        [
                            "code"            => 200,
                            "validated"       => true
                        ],
                    ];
            } catch (\Exception $e) {
                $response = [
                    [
                        "code" => 404,
                        "validated" => false
                    ],
                ];
            }
        } else {
            $response = [
                [
                    "code" => 404,
                    "validated" => false
                ],
            ];
        }
        return $response;
    }
}

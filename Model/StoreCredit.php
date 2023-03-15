<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\StoreCreditInterface;

use Magento\Framework\Module\Manager;

use Magento\Framework\ObjectManagerInterface;

class StoreCredit implements StoreCreditInterface
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

    /**
     * @var ResourceModel\Collection
     */
    private $storeCreditCollection;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        Manager                 $moduleManager,
        ObjectManagerInterface  $objectManager
    ) {
        $this->request         =  $request;
        $this->moduleManager   =  $moduleManager;
        $this->objectManager   =  $objectManager;
    }

    public function getstoreCreditByCustomerId()
    {
        if ($this->moduleManager->isEnabled('Amasty_StoreCredit')) {
            try {
                $body        = $this->request->getBodyParams();
                $customerId  = $body['data']['customer_id'];
                $storeCreditCollection = $this->objectManager->get("\Amasty\StoreCredit\Model\StoreCredit\ResourceModel\Collection");

                if ($storeCredit = $storeCreditCollection->getByCustomerId($customerId)) {
                    $response = [
                        [
                            "code"            => 'success',
                            "store_credit"    => $storeCredit['store_credit']
                        ],
                    ];
                } else {
                    $response = [
                        [
                            "code"    => 'error',
                            "message" => 'Not Found Store Credit'
                        ],
                    ];
                }
            } catch (\Exception $e) {
                $response = [
                    [
                        "code" => 'error',
                        "message" => $e->getMessage(),
                    ],
                ];
            }
        } else {
            $response = [
                [
                    "code" => 'error',
                    "message" => "StoreCredit extension not enable",
                ],
            ];
        }
        return $response;
    }
}

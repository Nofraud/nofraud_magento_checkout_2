<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\StoreCreditInterface;

use Amasty\StoreCredit\Model\StoreCredit\ResourceModel\Collection;

class StoreCredit implements StoreCreditInterface
{
    protected $request;

    /**
     * @var ResourceModel\Collection
     */
    private $storeCreditCollection;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        Collection                             $storeCreditCollection
    ) {
        $this->request               = $request;
        $this->storeCreditCollection = $storeCreditCollection;
    }

    public function getstoreCreditByCustomerId()
    {
        $body = $this->request->getBodyParams();
        try {
            $customerId  = $body['data']['customer_id'];
            if ($storeCredit = $this->storeCreditCollection->getByCustomerId($customerId)) {
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
        return $response;
    }
}

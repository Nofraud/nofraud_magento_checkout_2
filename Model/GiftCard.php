<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\GiftCardAccountRepositoryInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCard\Api\CodeRepositoryInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterfaceFactory;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account as AccountResource;

class GiftCard implements GiftCardAccountRepositoryInterface
{
    /**
     * @var CodeRepositoryInterface
     */
    private $codeRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Model storage
     * @var array
     */
    private $accounts = [];

    /**
     * @var GiftCardAccountInterfaceFactory
     */
    private $accountFactory;

    /**
     * @var AccountResource
     */
    private $resource;

    protected $request;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        CodeRepositoryInterface         $codeRepository,
        CollectionFactory               $collectionFactory,
        GiftCardAccountInterfaceFactory $accountFactory,
        AccountResource                 $resource
    ) {
        $this->codeRepository    = $codeRepository;
        $this->collectionFactory = $collectionFactory;
        $this->accountFactory    = $accountFactory;
        $this->resource          = $resource;
        $this->request           = $request;
    }

    public function getById(int $id): GiftCardAccountInterface
    {
        if (!isset($this->accounts[$id])) {
            /** @var GiftCardAccountInterface $account */
            $account = $this->accountFactory->create();
            $this->resource->load($account, $id);

            if (!$account->getAccountId()) {
                throw new NoSuchEntityException(__('Account with specified ID "%1" not found.', $id));
            }
            if ($codeId = $account->getCodeId()) {
                $code = $this->codeRepository->getById($codeId);
                $account->setCodeModel($code);
            }
            $this->accounts[$id] = $account;
        }

        return $this->accounts[$id];
    }

    public function getCertificateDetails()
    {
        $body      = $this->request->getBodyParams();
        $giftCode  = $body['data']['gift_code'];
        try{
            $results   = $this->getByCode($giftCode);
            if($results === false){
                $response = [
                    [
                        "code" => 'error',
                        "message" => "specified code ".$giftCode." not found",
                    ],
                ];
            }else{
                $response = [
                    [
                        "code" => 'success',
                        "message" => $results['current_value']
                    ],
                ];
            }
        }catch (\Exception $e) {
            $response = [
                [
                    "code" => 'error',
                    "message" => $e->getMessage(),
                ],
            ];
        }
        return $response;
    }

    public function getByCode(string $code): GiftCardAccountInterface
    {
        $code = $this->codeRepository->getByCode($code);

        if ($code->getStatus() !== Status::USED) {
            return false;
        }
        $account = $this->collectionFactory->create()
            ->addFieldToFilter(GiftCardAccountInterface::CODE_ID, $code->getCodeId())
            ->getFirstItem();

        return $this->getById((int)$account->getAccountId());
    }
}

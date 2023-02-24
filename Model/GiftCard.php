<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\GiftCardAccountRepositoryInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class GiftCard implements GiftCardAccountRepositoryInterface
{
    /**
     * Model storage
     * @var array
     */
    private $accounts = [];

    /**
     * @var Request
     */
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
        Request                 $request,
        Manager                 $moduleManager,
        ObjectManagerInterface  $objectManager
    ) {
        $this->request        = $request;
        $this->moduleManager  = $moduleManager;
        $this->objectManager  = $objectManager;
    }

    public function getById(int $id): GiftCardAccountInterface
    {
        if (!isset($this->accounts[$id])) {
            $accountFactory  = $this->objectManager->get("\Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterfaceFactory");
            $account         = $accountFactory->create();

            $resource = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account");
            $resource->load($account, $id);

            if (!$account->getAccountId()) {
                throw new NoSuchEntityException(__('Account with specified ID "%1" not found.', $id));
            }
            if ($codeId = $account->getCodeId()) {
                $codeRepository = $this->objectManager->get("\Amasty\GiftCard\Api\CodeRepositoryInterface");

                $code = $codeRepository->getById($codeId);
                $account->setCodeModel($code);
            }
            $this->accounts[$id] = $account;
        }

        return $this->accounts[$id];
    }

    public function getCertificateDetails()
    {
        if ($this->moduleManager->isEnabled('Amasty_GiftCardAccount') && $this->moduleManager->isEnabled('Amasty_GiftCard')) {
            try {
                $body      = $this->request->getBodyParams();
                $giftCode  = $body['data']['gift_code'];
                $results   = $this->getByCode($giftCode);

                if ($results === false) {
                    $response = [
                        [
                            "code" => 'error',
                            "message" => "specified code " . $giftCode . " not found",
                        ],
                    ];
                } else {
                    $response = [
                        [
                            "code" => 'success',
                            "message" => $results['current_value']
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
                    "message" => "Gift card extension not enable",
                ],
            ];
        }
        return $response;
    }

    public function getByCode(string $code): GiftCardAccountInterface
    {
        $codeRepository  = $this->objectManager->get("\Amasty\GiftCard\Api\CodeRepositoryInterface");

        $code = $codeRepository->getByCode($code);

        if ($code->getStatus() !== Status::USED) {
            return false;
        }
        $collectionFactory  = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory");
        $account = $collectionFactory->create()
            ->addFieldToFilter(GiftCardAccountInterface::CODE_ID, $code->getCodeId())
            ->getFirstItem();

        return $this->getById((int)$account->getAccountId());
    }
}

<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\GiftCardAccountManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class GiftCardAccountManagement implements GiftCardAccountManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

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

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    public function __construct(
        Manager                 $moduleManager,
        CartRepositoryInterface $quoteRepository,
        Request                 $request,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        ObjectManagerInterface  $objectManager
    ) {
        $this->moduleManager            = $moduleManager;
        $this->quoteRepository          = $quoteRepository;
        $this->request                  = $request;
        $this->maskedQuoteIdToQuoteId   = $maskedQuoteIdToQuoteId;
        $this->objectManager            = $objectManager;
    }

    public function applyGiftCardToCart()
    {
        if ($this->moduleManager->isEnabled('Amasty_GiftCardAccount') && $this->moduleManager->isEnabled('Amasty_GiftCard')) {
            $body          = $this->request->getBodyParams();
            $giftCardCode  = $body['data']['gift_code'];
            $maskedHashId  = $body['data']['cart_id'];

            if(isset($giftCardCode) && isset($maskedHashId)) {
                $accountRepository = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\Repository");
                $gCardCartProcessor = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor");

                $giftCardCode = trim($giftCardCode);
                try {
                    if (isset($body['data']['is_loggedin']) && $body['data']['is_loggedin']){
                        $quote = $this->quoteRepository->getActive($maskedHashId);
                    }else{
                        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedHashId);
                        $quote  = $this->quoteRepository->getActive($cartId);
                    }

                    $giftCard = $accountRepository->getByCode($giftCardCode);
                    $gCardCartProcessor->applyToCart($giftCard, $quote);
                    $response = [
                        [
                            "code" => 'success',
                            "message" => "Gift Card \"$giftCardCode\" was added"
                        ],
                    ];
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
                        "message" => "please provide gift code and cart id",
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
    public function removeGiftCardFromCart()
    {
        if ($this->moduleManager->isEnabled('Amasty_GiftCardAccount') && $this->moduleManager->isEnabled('Amasty_GiftCard')) {
            $body          = $this->request->getBodyParams();
            $giftCardCode  = $body['data']['gift_code'];
            $maskedHashId  = $body['data']['cart_id'];

            if(isset($giftCardCode) && isset($maskedHashId)) {
                $accountRepository  = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\Repository");
                $gCardCartProcessor = $this->objectManager->get("\Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor");

                $giftCardCode = trim($giftCardCode);
                try {
                    if (isset($body['data']['is_loggedin']) && $body['data']['is_loggedin']){
                        $quote = $this->quoteRepository->getActive($maskedHashId);
                    }else{
                        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedHashId);
                        $quote  = $this->quoteRepository->getActive($cartId);
                    }
                    $giftCard = $accountRepository->getByCode($giftCardCode);
                    $gCardCartProcessor->removeFromCart($giftCard, $quote);
                    $response = [
                        [
                            "code" => 'success',
                            "message" => "Gift Card \"$giftCardCode\" was removed from the cart"
                        ],
                    ];
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
}

<?php
namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CartManagement implements CartManagementInterface
{
    protected $cartRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    public function removeAddresses($cartId)
    {
        $quote = $this->cartRepository->getActive($cartId);

        // Attempt to reset the shipping address
        try {
            if ($quote->getShippingAddress()->getId()) {
                $shippingAddress = $quote->getShippingAddress();
                $this->resetAddress($shippingAddress);
                $quote->setShippingAddress($shippingAddress);
            }

            if ($quote->getBillingAddress()->getId()) {
                $billingAddress = $quote->getBillingAddress();
                $this->resetAddress($billingAddress);
                $quote->setBillingAddress($billingAddress);
            }
            $quote->collectTotals();
           $this->cartRepository->save($quote);

        } catch (\Exception $e) {
            throw new LocalizedException(__('Unable to remove addresses: %1', $e->getMessage()));
        }

        return true;
    }

    private function resetAddress(AddressInterface $address)
    {
        // Resetting provided fields to null or default values. Some fields like 'country_id' require a valid default othervise it fail to store.
        $address->setStreet(['N/A']) // Street expects an array
        ->setCity('N/A') // Magento's internal logic prevents from setting to null
        ->setRegion('N/A') // Magento's internal logic prevents from setting to null
        ->setRegionId(null)
        ->setPostcode('000000') // Magento's internal logic prevents from setting to null
        ->setCountryId('US') // Magento's internal logic prevents from setting to null
        ->setCompany('N/A') // Magento's internal logic prevents from setting to null
        ->setFax('N/A') // Magento's internal logic prevents from setting to null
        ->setSaveInAddressBook(0)
        ->setPrefix(null)
        ->setVatId(null)
        ->setSameAsBilling(1)
        ->setCollectShippingRates(0)
        ->setShippingDescription('N/A') // Magento's internal logic prevents from setting to null
        ->setWeight(0)
        ->setSubtotalWithDiscount(0.0000)
        ->setBaseSubtotalWithDiscount(0.0000)
        ->setTaxAmount(0.0000)
        ->setBaseTaxAmount(0.0000)
        ->setShippingAmount(0.0000)
        ->setBaseShippingAmount(0.0000)
        ->setShippingTaxAmount(0.0000)
        ->setBaseShippingTaxAmount(0.0000)
        ->setDiscountAmount(0.0000)
        ->setBaseDiscountAmount(0.0000)
        ->setGrandTotal($address->getSubtotal())
        ->setBaseGrandTotal($address->getBaseSubtotal())
        ->setCustomerNotes(null)
        ->setAppliedTaxes([])
        ->setDiscountDescription(null)
        ->setShippingDiscountAmount(0.0000)
        ->setBaseShippingDiscountAmount(0.0000)
        ->setSubtotalInclTax(0.0000)
        ->setBaseSubtotalTotalInclTax(0.0000)
        ->setDiscountTaxCompensationAmount(0.0000)
        ->setBaseDiscountTaxCompensationAmount(0.0000)
        ->setShippingDiscountTaxCompensationAmount(0.0000)
        ->setBaseShippingDiscountTaxCompensationAmount(0.0000)
        ->setShippingInclTax(0.0000)
        ->setBaseShippingInclTax(0.0000)
        // Additional settings for VAT and shipping related fields
        ->setVatIsValid(null)
        ->setVatRequestId(null)
        ->setVatRequestDate(null)
        ->setVatRequestSuccess(null)
        ->setValidatedCountryCode(null)
        ->setValidatedVatNumber(null)
        ->setGiftMessageId(null)
        ->setFreeShipping(0) // Assuming this should be reset as well
        ->setShippingMethod('N/A'); // Magento's internal logic prevents from setting to null
    }
}

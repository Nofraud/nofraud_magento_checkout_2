<?php
namespace NoFraud\Checkout\Model;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

class ShippingInformationManagement extends \Magento\Checkout\Model\ShippingInformationManagement
{
	
    public function saveAddressInformation(
        $cartId,
        ShippingInformationInterface $addressInformation
    ): PaymentDetailsInterface {
        /** @var Quote $quote */
		
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customoverride.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		
		
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);

        $address = $addressInformation->getShippingAddress();
        $this->validateAddress($address);
		
		// custom code
		$currentCurrency = $quote->getQuoteCurrencyCode();
		$logger->info('currency : ' . $currentCurrency);
		
        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        try {
            $billingAddress = $addressInformation->getBillingAddress();
            if ($billingAddress) {
                if (!$billingAddress->getCustomerAddressId()) {
                    $billingAddress->setCustomerAddressId(null);
                }
                $this->addressValidator->validateForCart($quote, $billingAddress);
                $quote->setBillingAddress($billingAddress);
            }

            $this->addressValidator->validateForCart($quote, $address);
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $address->setLimitCarrier($carrierCode);
            $methodCode = $addressInformation->getShippingMethodCode();
            $quote = $this->prepareShippingAssignment($quote, $address, $carrierCode . '_' . $methodCode);

            $quote->setIsMultiShipping(false);
			
			$logger->info('set new ');
			
			$logger->info('before save : ' . $quote->getQuoteCurrencyCode());
            //$this->quoteRepository->save($quote);
			$quote->save();
			$quote->setQuoteCurrencyCode($currentCurrency)->save();
			$logger->info('new currency');
			$logger->info($quote->getQuoteCurrencyCode());
			
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            throw new InputException(
                __(
                    'The shipping information was unable to be saved. Error: "%message"',
                    ['message' => $e->getMessage()]
                )
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }

        $shippingAddress = $quote->getShippingAddress();

        if (!$quote->getIsVirtual()
            && !$shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())
        ) {
            $errorMessage = $methodCode ?
                __('Carrier with such method not found: %1, %2', $carrierCode, $methodCode)
                : __('The shipping method is missing. Select the shipping method and try again.');
            throw new NoSuchEntityException(
                $errorMessage
            );
        }

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
		
        return $paymentDetails;
    }
	
	private function validateAddress(?AddressInterface $address): void
    {
        if (!$address || !$address->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
    }
	
	 private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method): CartInterface
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }
}

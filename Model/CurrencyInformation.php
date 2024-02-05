<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\CurrencyInformationInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\Currency;

class CurrencyInformation implements CurrencyInformationInterface
{
    const REDIXPRICEDATA = 1000;
    const STANDARD = 8;
    const RIGHT    = 16;
    const LEFT     = 32;

    /**
     * 
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var Currency
     */
    protected $currency;

    protected $priceHelper;

    public function __construct(
        CurrencyFactory  $currencyFactory,
        Currency         $currency,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->currency        = $currency;
        $this->priceHelper     = $priceHelper;
    }

    public function getCurrencyInformation($currencyCode)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/currencyInformation.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('currencyInformation Start');

        try {
            $currency = $this->currencyFactory->create()->load($currencyCode);
            

            $currencyResponse = $this->currency->getCurrency($currencyCode);

            $logger->info('currencyResponse - ' . json_encode($currencyResponse));

            $symbolPositionNum  = ((array)$currencyResponse)[chr(0) . '*' . chr(0) . 'options'];

            $logger->info('currencyResponse - Cast - ' . json_encode($symbolPositionNum));

            if ($symbolPositionNum['position'] == self::STANDARD) {
                $symbolPosition = 'STANDARD';
            } elseif ($symbolPositionNum['position'] == self::RIGHT) {
                $symbolPosition = 'RIGHT';
            } elseif ($symbolPositionNum['position'] == self::LEFT) {
                $symbolPosition = 'LEFT';
            }

            $logger->info('symbolPosition - ' . json_encode($symbolPosition));

            $priceFormeter = $this->priceHelper->currency(self::REDIXPRICEDATA);
            $radix =  strip_tags($priceFormeter);
            $radixseparator = substr($radix, 6, 1);
            $thousandSeparator = substr($radix, 2, 1);
            $response = [
                [
                    "code"              => 'success',
                    'symbol'            => $currency->getCurrencySymbol(),
                    'symbolPosition'    => $symbolPosition,
                    'radixSeparator'    => $radixseparator,
                    'thousandSeparator' => $thousandSeparator
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
        return $response;
    }
}

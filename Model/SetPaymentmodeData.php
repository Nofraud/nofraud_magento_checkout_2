<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\SetPaymentmode;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SetPaymentmodeData implements SetPaymentmode
{
    protected $logger;

    protected $configWriter;

    const PAYMENT_ACTION = "nofraud/advance/payment_action";

    public function __construct(WriterInterface $configWriter) {
        $this->configWriter = $configWriter;
    }

    /**
     * @param $path
     * @param $value
     */
    public function SetData($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    public function paymentmodeConfiguration($data)
    {
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/enableConfiguration.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('enableConfiguration');
		$logger->info(json_encode($data));

        $paymentMode    = $data["manual_capture"];

        if (isset($paymentMode)) {
            try {
                if($paymentMode == true){
                    $this->SetData(self::PAYMENT_ACTION, "authorize");
                }else{
                    $this->SetData(self::PAYMENT_ACTION, "authorize_capture");
                }

                $response = [
                    [
                        "code" => 'success',
                        "message" => 'Payment mode updated successfully !',
                    ],
                ];
            }catch(\Exception $e) {
                $response = [
                    [
                        "code" => 'error',
                        "message" => $e->getMessage(),
                    ],
                ];
            }
        }
        return $response;
    }
}
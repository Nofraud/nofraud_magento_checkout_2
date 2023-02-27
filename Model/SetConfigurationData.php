<?php
namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\SetConfiguration;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SetConfigurationData implements SetConfiguration
{
    protected $logger;

    protected $configWriter;

    const ENABLED = "nofraud/general/enabled";

    const MERCHANT_Id = "nofraud/general/merchant";

    const NF_TOKEN    = "nofraud/general/nf_token";

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

    public function enableConfiguration($data)
    {
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/enableConfiguration.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('enableConfiguration');
		$logger->info(json_encode($data));

        $enabled        = $data["enabled"];
        $merchant_id    = $data["merchant_id"];
        $nf_token       = $data["nf_token"];

        if (isset($enabled) && isset($merchant_id) && isset($nf_token)) {
            try {
                if($enabled=="yes"){
                    $this->SetData(self::ENABLED, 1);
                }else{
                    $this->SetData(self::ENABLED, 0);
                }
                $this->SetData(self::MERCHANT_Id, $merchant_id);
                $this->SetData(self::NF_TOKEN, $nf_token);

                $response = [
                    [
                        "code" => 'success',
                        "message" => 'Extension, Merchant Id and Nf Token updated successfully !',
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
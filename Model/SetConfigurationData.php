<?php
namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\SetConfiguration;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class SetConfigurationData implements SetConfiguration
{
    protected $logger;

    protected $configWriter;

    protected $cacheTypeList;

    protected $cacheFrontendPool;

    const ENABLED = "nofraud/general/enabled";

    const MERCHANT_Id = "nofraud/general/merchant";

    const NF_TOKEN    = "nofraud/general/nf_token";

    public function __construct(
    WriterInterface $configWriter,
    TypeListInterface $cacheTypeList,
    Pool $cacheFrontendPool
    ) {
        $this->configWriter         = $configWriter;
        $this->cacheTypeList        = $cacheTypeList;
        $this->cacheFrontendPool    = $cacheFrontendPool;
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
                if($enabled == true){
                    $this->SetData(self::ENABLED, 1);
                }else{
                    $this->SetData(self::ENABLED, 0);
                }
                $this->SetData(self::MERCHANT_Id, $merchant_id);
                $this->SetData(self::NF_TOKEN, $nf_token);
                $this->flushCache();
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

    public function flushCache() {
        $_types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice'
        ];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
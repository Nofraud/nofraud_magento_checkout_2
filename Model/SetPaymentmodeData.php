<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\SetPaymentmode;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class SetPaymentmodeData implements SetPaymentmode
{
    protected $logger;

    protected $configWriter;

    protected $cacheTypeList;

    protected $cacheFrontendPool;

    const PAYMENT_ACTION = "nofraud/advance/payment_action";

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
                $this->flushCache();
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
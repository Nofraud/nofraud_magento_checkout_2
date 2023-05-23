<?php

namespace NoFraud\Checkout\Model;

use NoFraud\Checkout\Api\PhonenumberInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class PhonenumberData implements PhonenumberInterface
{
    protected $logger;

    protected $configWriter;

    protected $cacheTypeList;

    protected $cacheFrontendPool;

    public $scopeConfig;

    const TELEPHONE_SHOW = "customer/address/telephone_show";

    const VALUE_NO = '';

    const VALUE_OPTIONAL = 'opt';
    
    const VALUE_REQUIRED = 'req';

    public function __construct(
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter         = $configWriter;
        $this->cacheTypeList        = $cacheTypeList;
        $this->cacheFrontendPool    = $cacheFrontendPool;
        $this->scopeConfig 	    = $scopeConfig;
    }

    public function getPhoneConfiguration() {
        return $this->scopeConfig->getValue(
                    self::TELEPHONE_SHOW,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                );
    }

    /**
     * @param $path
     * @param $value
     */
    public function SetData($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    public function getPhonenumberMode()
    {
	    $data   = $this->getPhoneConfiguration(); 
	
        if (isset($data)) {
            try {
                $response = [
                    [
                        "code" => 'success',
                        "value" => $data,
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
        } else {
            $response = [
                        [
                            "code" => 'success',
                            "value" => "no",
                        ],
                    ];
	    }
        return $response;
    }

    public function setPhonenumberMode($data)
    {
        $required = $data["required"];
        
        if (isset($required)) {
            try {
                if ($required == "req") {
		            $this->SetData(self::TELEPHONE_SHOW, self::VALUE_REQUIRED);
                } else if ($required == "opt") {
                    $this->SetData(self::TELEPHONE_SHOW, self::VALUE_OPTIONAL);
                } else {
                    $this->SetData(self::TELEPHONE_SHOW, self::VALUE_NO);
                }
                $this->flushCache();
                $response = [
                    [
                        "code" => 'success',
                        "message" => 'Telephone number updated successfully !',
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

    public function flushCache() 
    {
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

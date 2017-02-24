<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;

class General extends AbstractHelper
{

    const MODULE_CODE = 'Magmodules_WebwinkelKeur';
    const XML_PATH_EXTENSION_ENABLED = 'magmodules_webwinkelkeur/general/enabled';
    const XML_PATH_API_WEBSHOP_ID = 'magmodules_webwinkelkeur/api/webshop_id';
    const XML_PATH_SIDEBAR_ENABLED = 'magmodules_webwinkelkeur/sidebar/enabled';
    const XML_PATH_SIDEBAR_LANGUAGE = 'magmodules_webwinkelkeur/sidebar/language';

    protected $moduleList;
    protected $metadata;
    protected $storeManager;
    protected $objectManager;
    protected $config;

    /**
     * General constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $metadata
     * @param Config $config
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->config = $config;
        parent::__construct($context);
    }

    public function getEnabledSidebar()
    {
        if ($this->getEnabled()) {
            return $this->getStoreValue(self::XML_PATH_SIDEBAR_ENABLED);
        }

        return false;
    }

    /**
     * General check if Extension is enabled
     * @param null $storeId
     * @return mixed
     */
    public function getEnabled($storeId = null)
    {
        return $this->getStoreValue(self::XML_PATH_EXTENSION_ENABLED, $storeId);
    }

    /**
     * Get Configuration data
     * @param $path
     * @param $scope
     * @param null $storeId
     * @return mixed
     */
    public function getStoreValue($path, $storeId = null, $scope = null)
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * @param $path
     * @param $websiteId
     * @return mixed
     */
    public function getWebsiteValue($path, $websiteId)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getWebshopId()
    {
        return $this->getStoreValue(self::XML_PATH_API_WEBSHOP_ID);
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->getStoreValue(self::XML_PATH_SIDEBAR_LANGUAGE);
    }
    
    /**
     * Set configuration data function
     * @param $value
     * @param $key
     * @param null $storeId
     */
    public function setConfigData($value, $key, $storeId = null)
    {
        if ($storeId) {
            $this->config->saveConfig($key, $value, 'stores', $storeId);
        } else {
            $this->config->saveConfig($key, $value, 'default', 0);
        }
    }

    /**
     * Create error response array for usage in config (manual import)
     * @param $msg
     * @return array
     */
    public function createResponseError($msg)
    {
        $response = ['status' => 'error', 'msg' => $msg];

        return $response;
    }

    /**
     * Returns current version of the extension
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleInfo = $this->moduleList->getOne(self::MODULE_CODE);

        return $moduleInfo['setup_version'];
    }

    /**
     * Returns current version of Magento
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->metadata->getVersion();
    }
}

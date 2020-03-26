<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebwinkelKeur\Magento2\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class General extends AbstractHelper {
    const MODULE_CODE = 'WebwinkelKeur_Magento2';

    const XPATH_EXTENSION_ENABLED = 'webwinkelkeur_magento2/general/enabled';

    const XPATH_API_WEBSHOP_ID = 'webwinkelkeur_magento2/api/webshop_id';

    const XPATH_SIDEBAR_ENABLED = 'webwinkelkeur_magento2/sidebar/enabled';

    const XPATH_SIDEBAR_LANGUAGE = 'webwinkelkeur_magento2/sidebar/language';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * General constructor.
     *
     * @param Context                  $context
     * @param ObjectManagerInterface   $objectManager
     * @param StoreManagerInterface    $storeManager
     * @param ModuleListInterface      $moduleList
     * @param ProductMetadataInterface $metadata
     * @param Config                   $config
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

    public function getEnabledSidebar() {
        if ($this->getEnabled()) {
            return $this->getStoreValue(self::XPATH_SIDEBAR_ENABLED);
        }

        return false;
    }

    /**
     * General check if Extension is enabled
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnabled($storeId = null) {
        return $this->getStoreValue(self::XPATH_EXTENSION_ENABLED, $storeId);
    }

    /**
     * Get Configuration data
     *
     * @param      $path
     * @param      $scope
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreValue($path, $storeId = null, $scope = null) {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * @param $path
     * @param $websiteId
     *
     * @return mixed
     */
    public function getWebsiteValue($path, $websiteId) {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getWebshopId() {
        return $this->getStoreValue(self::XPATH_API_WEBSHOP_ID);
    }

    /**
     * @return mixed
     */
    public function getLanguage() {
        return $this->getStoreValue(self::XPATH_SIDEBAR_LANGUAGE);
    }

    /**
     * Set configuration data function
     *
     * @param      $value
     * @param      $key
     * @param null $storeId
     */
    public function setConfigData($value, $key, $storeId = null) {
        if ($storeId) {
            $this->config->saveConfig($key, $value, 'stores', $storeId);
        } else {
            $this->config->saveConfig($key, $value, 'default', 0);
        }
    }

    /**
     * Create error response array for usage in config (manual import)
     *
     * @param $msg
     *
     * @return array
     */
    public function createResponseError($msg) {
        return ['status' => 'error', 'msg' => $msg];
    }

    /**
     * Returns current version of the extension
     *
     * @return mixed
     */
    public function getExtensionVersion() {
        $moduleInfo = $this->moduleList->getOne(self::MODULE_CODE);

        return $moduleInfo['setup_version'];
    }

    /**
     * Returns current version of Magento
     *
     * @return string
     */
    public function getMagentoVersion() {
        return $this->metadata->getVersion();
    }
}

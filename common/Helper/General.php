<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Valued\Magento2\Setup\ExtensionBase;

class General extends AbstractHelper {

    const XPATH_EXTENSION_ENABLED = '_magento2/general/enabled';
    const XPATH_API_WEBSHOP_ID = '_magento2/api/webshop_id';
    const XPATH_SIDEBAR_ENABLED = '_magento2/sidebar/enabled';
    const XPATH_SIDEBAR_LANGUAGE = '_magento2/sidebar/language';

    private $extension;

    private $moduleList;

    private $metadata;

    private $storeManager;

    private $objectManager;

    private $config;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata,
        Config $config,
        ExtensionBase $extension
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->config = $config;
        $this->extension = $extension;
        parent::__construct($context);
    }

    public function getEnabledSidebar() {
        if ($this->getEnabled()) {
            return $this->getStoreValue($this->extension->getSlug() . self::XPATH_SIDEBAR_ENABLED);
        }

        return false;
    }

    public function getEnabled($storeId = null) {
        return $this->getStoreValue($this->extension->getSlug() . self::XPATH_EXTENSION_ENABLED, $storeId);
    }

    public function getStoreValue($path, $storeId = null, $scope = null) {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    public function getWebsiteValue($path, $websiteId) {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getWebshopId() {
        return $this->getStoreValue($this->extension->getSlug() . self::XPATH_API_WEBSHOP_ID);
    }

    public function getLanguage() {
        return $this->getStoreValue($this->extension->getSlug() . self::XPATH_SIDEBAR_LANGUAGE);
    }

    public function setConfigData($value, $key, $storeId = null) {
        if ($storeId) {
            $this->config->saveConfig($key, $value, 'stores', $storeId);
        } else {
            $this->config->saveConfig($key, $value, 'default', 0);
        }
    }

    public function createResponseError($msg) {
        return ['status' => 'error', 'msg' => $msg];
    }

    public function getExtensionVersion() {
        $moduleInfo = $this->moduleList->getOne($this->extension->getModuleCode());

        return $moduleInfo['setup_version'];
    }

    public function getMagentoVersion() {
        return $this->metadata->getVersion();
    }
}

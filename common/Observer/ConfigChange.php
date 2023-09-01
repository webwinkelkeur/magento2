<?php

namespace Valued\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Url;
use Valued\Magento2\Model\Api as ApiModel;
use Valued\Magento2\Setup\ExtensionBase;

class ConfigChange implements ObserverInterface {
    /** @var ApiModel */
    private $apiModel;

    /** @var Url */
    private $urlBuilder;

    /** @var ExtensionBase */
    private $extension;

    public function __construct(
        ApiModel $apiModel,
        Url $urlBuilder,
        ExtensionBase $extension
    ) {
        $this->apiModel = $apiModel;
        $this->urlBuilder = $urlBuilder;
        $this->extension = $extension;
    }

    public function execute(Observer $observer): void {
        $storeId = $observer->getEvent()->getData('store') ?: null;
        $syncUrl = $this->urlBuilder->getUrl(sprintf('%s/sync', $this->extension->getSlug()));
        $this->apiModel->sendSyncUrl($syncUrl, $storeId);
    }
}

<?php
namespace Valued\Magento2\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Sidebar extends Template implements BlockInterface {
    private $generalHelper;

    private $extension;

    private $cspNonceProvider;

    public function __construct(
        Context          $context,
        GeneralHelper    $generalHelper,
        ExtensionBase    $extension,
        CspNonceProvider $cspNonceProvider,
        array            $data = []
    ) {
        $this->generalHelper = $generalHelper;
        $this->extension = $extension;
        $this->cspNonceProvider = $cspNonceProvider;
        parent::__construct($context, $data);
    }

    public function getEnabledSidebar(): bool {
        return !!$this->generalHelper->getEnabledSidebar();
    }

    public function getSidebarDomain(): string {
        return $this->extension->getDashboardDomain();
    }

    public function getWebshopId() {
        return $this->generalHelper->getWebshopId();
    }

    public function getLanguage() {
        return $this->generalHelper->getLanguage();
    }

    public function getNonce(): string {
        return $this->cspNonceProvider->generateNonce();
    }
}

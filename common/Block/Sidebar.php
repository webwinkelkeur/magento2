<?php
namespace Valued\Magento2\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Sidebar extends Template implements BlockInterface {
    private $generalHelper;

    private $extension;

    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        ExtensionBase $extension,
        array $data = []
    ) {
        $this->generalHelper = $generalHelper;
        $this->extension = $extension;
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
}

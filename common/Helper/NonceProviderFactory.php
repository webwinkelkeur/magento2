<?php

namespace Valued\Magento2\Helper;

use Magento\Framework\ObjectManagerInterface;

class NonceProviderFactory {
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager) {
        $this->objectManager = $objectManager;
    }

    public function create(): NonceProviderInterface {
        if (class_exists('Magento\Csp\Helper\CspNonceProvider')) {
            return $this->objectManager->create('Valued\Magento2\Helper\NonceProvider');
        } else {
            return $this->objectManager->create('Valued\Magento2\Helper\NonceProviderFallback');
        }
    }
}
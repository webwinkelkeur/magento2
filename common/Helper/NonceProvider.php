<?php

namespace Valued\Magento2\Helper;

use Magento\Csp\Helper\CspNonceProvider;

class NonceProvider implements NonceProviderInterface {
    private $cspNonceProvider;

    public function __construct(CspNonceProvider $cspNonceProvider) {
        $this->cspNonceProvider = $cspNonceProvider;
    }

    public function getNonce(): string {
        return $this->cspNonceProvider->generateNonce();
    }
}
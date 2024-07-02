<?php

namespace Valued\Magento2\Helper;

class NonceProviderFallback implements NonceProviderInterface {

    public function getNonce(): ?string {
        return null;
    }
}
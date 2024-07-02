<?php

namespace Valued\Magento2\Helper;

interface NonceProviderInterface {
    public function getNonce(): ?string;
}
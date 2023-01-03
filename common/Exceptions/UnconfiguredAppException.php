<?php

namespace Valued\Magento2\Exceptions;

class UnconfiguredAppException extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 501;
    }
}

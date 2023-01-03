<?php

namespace Valued\Magento2\Exceptions;

class BadRequestSyncException extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 400;
    }
}

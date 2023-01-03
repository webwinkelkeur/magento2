<?php

namespace Valued\Magento2\Exceptions;

class UnauthorizedException extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 401;
    }
}

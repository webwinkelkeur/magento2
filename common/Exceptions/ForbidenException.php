<?php

namespace Valued\Magento2\Exceptions;

class ForbidenException extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 403;
    }
}

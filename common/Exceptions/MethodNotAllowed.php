<?php

namespace Valued\Magento2\Exceptions;

class MethodNotAllowed extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 405;
    }
}

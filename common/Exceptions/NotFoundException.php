<?php

namespace Valued\Magento2\Exceptions;


class NotFoundException extends ProductReviewSyncException {
    public function getHttpResponseCode(): int {
        return 404;
    }
}

<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Valued\Magento2\Cron;

use Valued\Magento2\Model\Api as ApiModel;

class ImportReviews {
    /**
     * @var ApiModel
     */
    private $apiModel;

    /**
     * ImportReviews constructor.
     *
     * @param ApiModel $apiModel
     */
    public function __construct(ApiModel $apiModel) {
        $this->apiModel = $apiModel;
    }

    /**
     * Execute import of reviews though API model
     */
    public function execute() {
        $type = 'cron';
        $this->apiModel->getReviews($type);
    }
}

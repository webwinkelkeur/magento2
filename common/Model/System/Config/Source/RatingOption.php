<?php

namespace Valued\Magento2\Model\System\Config\Source;

use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

use Magento\Framework\Data\OptionSourceInterface;

class RatingOption implements OptionSourceInterface {
    private $ratingCollection;

    public function __construct(RatingCollection $ratingCollection) {
        $this->ratingCollection = $ratingCollection;
    }


    public function toOptionArray() {
        $options = [];
        foreach ($this->ratingCollection as $ratingOption) {
            $options[] = [
                'value' => $ratingOption->getId(),
                'label' => $ratingOption->getRatingCode(),
            ];
        }

        return $options;
    }


}

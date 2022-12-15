<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Model\System\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttribute implements OptionSourceInterface {
    private $attributeCollection;

    public function __construct(ProductAttributeCollection $attributeCollection) {
        $this->attributeCollection = $attributeCollection;
    }


    public function toOptionArray() {
        $options = [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
        ];

        foreach ($this->attributeCollection as $attribute) {
            if (!$attribute instanceof Attribute) {
                continue;
            }
            $options[] = [
                'value' => $attribute->getName(),
                'label' => $attribute->getStoreLabel(),
            ];
        }

        return $options;
    }


}

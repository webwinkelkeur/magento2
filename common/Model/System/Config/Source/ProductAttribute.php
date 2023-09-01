<?php

namespace Valued\Magento2\Model\System\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttribute implements OptionSourceInterface {
    /** @var ProductAttributeCollection */
    private $attributeCollection;

    public function __construct(ProductAttributeCollection $attributeCollection) {
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray(): array {
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

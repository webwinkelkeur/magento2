<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Language implements ArrayInterface {
    /**
     * @return array
     */
    public function toOptionArray() {
        $language = [];
        $language[] = ['value' => '', 'label' => __('Use default')];
        $language[] = ['value' => 'cus', 'label' => __('Based on customer country')];
        $language[] = ['value' => 'nld', 'label' => __('Dutch')];
        $language[] = ['value' => 'eng', 'label' => __('English')];
        $language[] = ['value' => 'deu', 'label' => __('German')];
        $language[] = ['value' => 'fra', 'label' => __('French')];
        $language[] = ['value' => 'spa', 'label' => __('Spanish')];

        return $language;
    }
}

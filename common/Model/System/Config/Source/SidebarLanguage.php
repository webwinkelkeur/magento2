<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SidebarLanguage implements ArrayInterface {
    /**
     * @return array
     */
    public function toOptionArray() {
        $language = [];
        $language[] = ['value' => '', 'label' => __('Use default')];
        $language[] = ['value' => 'nl', 'label' => __('Dutch')];
        $language[] = ['value' => 'en', 'label' => __('English')];
        $language[] = ['value' => 'de', 'label' => __('German')];
        $language[] = ['value' => 'fr', 'label' => __('French')];
        $language[] = ['value' => 'es', 'label' => __('Spanish')];

        return $language;
    }
}

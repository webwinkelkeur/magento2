<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Valued\Magento2\Helper\Reviews as ReviewsHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Summary extends Template implements BlockInterface {
    private $extension;

    private $reviewHelper;

    public function __construct(
        Context $context,
        ReviewsHelper $reviewHelper,
        ExtensionBase $extension,
        array $data = []
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->extension = $extension;
        parent::__construct($context, $data);
    }

    public function _construct() {
        $template = $this->getData('template');
        parent::_construct();
        $this->setTemplate($template);
    }

    public function getRichSnippets() {
        return $this->getData('rich_snippets');
    }

    public function getSummaryData() {
        $storeId = $this->_storeManager->getStore()->getId();
        $summaryData = $this->reviewHelper->getSummaryData($storeId);
        if ($summaryData) {
            $iso = $this->getData('webwinkel_url');
            if (empty($iso)) {
                $iso = 'default';
            }

            $summaryData['review_url'] = '#';

            foreach ($summaryData['link'] as $key => $url) {
                if ($key == $iso) {
                    $summaryData['review_url'] = $url;
                }
            }

            $summaryData['iso'] = $iso;
        }

        return $summaryData;
    }

    public function getSlug() {
        return $this->extension->getSlug();
    }
}

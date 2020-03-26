<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebwinkelKeur\Magento2\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use WebwinkelKeur\Magento2\Helper\Reviews as ReviewsHelper;

class Summary extends Template implements BlockInterface {
    /**
     * @var ReviewsHelper
     */
    private $reviewHelper;

    /**
     * Summary constructor.
     *
     * @param Context       $context
     * @param ReviewsHelper $reviewHelper
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ReviewsHelper $reviewHelper,
        array $data = []
    ) {
        $this->reviewHelper = $reviewHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set template file, see getThemePath
     */
    public function _construct() {
        $template = $this->getData('template');
        parent::_construct();
        $this->setTemplate($template);
    }

    /**
     * Rich Snippets check from Widget
     * @return mixed
     */
    public function getRichSnippets() {
        return $this->getData('rich_snippets');
    }

    /**
     * Get summary data from review helper by storeId
     * @return array
     */
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
}

<?php
/**
 * Copyright © 2016 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Backend\Block\Template\Context;
use Magmodules\WebwinkelKeur\Helper\Reviews as ReviewsHelper;

class Summary extends Template implements BlockInterface
{

    protected $rev;

    /**
     * Summary constructor.
     * @param Context $context
     * @param ReviewsHelper $revHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ReviewsHelper $revHelper,
        array $data = []
    ) {
        $this->rev = $revHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set template file, see getThemePath
     */
    protected function _construct()
    {
        $template = $this->getData('template');
        parent::_construct();
        $this->setTemplate($template);
    }

    /**
     * Rich Snippets check from Widget
     * @return mixed
     */
    public function getRichSnippets()
    {
        return $this->getData('rich_snippets');
    }

    /**
     * Get summary data from review helper by storeId
     * @return array
     */
    public function getSummaryData()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $summaryData = $this->rev->getSummaryData($storeId);
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

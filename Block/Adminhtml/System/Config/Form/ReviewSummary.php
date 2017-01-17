<?php
/**
 * Copyright © 2016 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magmodules\WebwinkelKeur\Helper\Reviews as ReviewsHelper;

class ReviewSummary extends Field
{

    protected $rev;
    protected $request;
    protected $_template = 'Magmodules_WebwinkelKeur::system/config/fieldset/summary.phtml';

    /**
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
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function getReviewSummary()
    {
        $summaryData = [];
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($websiteId || $storeId) {
            if ($data = $this->rev->getSummaryData($storeId, $websiteId)) {
                $summaryData[] = $data;
            }
        } else {
            $summaryData = $this->rev->getAllSummaryData();
        }

        return $summaryData;
    }

    /**
     * @param AbstractElement $element
     * @return bool
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return bool
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}

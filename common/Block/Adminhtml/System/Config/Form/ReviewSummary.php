<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Valued\Magento2\Helper\Reviews as ReviewsHelper;
use Valued\Magento2\Setup\ExtensionBase;

class ReviewSummary extends Field {
    /**
     * @var string
     */
    protected $_template = '%s::system/config/fieldset/summary.phtml';

    private $extension;

    /**
     * @var ReviewsHelper
     */
    private $reviewHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * ReviewSummary constructor.
     *
     * @param Context       $context
     * @param ReviewsHelper $reviewHelper
     * @param ExtensionBase $extension
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ReviewsHelper $reviewHelper,
        ExtensionBase $extension,
        array $data = []
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->request = $context->getRequest();
        $this->extension = $extension;
        $this->_template = sprintf($this->_template, $this->extension->getModuleCode());
        parent::__construct($context, $data);
    }

    /**
     * @return null
     */
    public function getCacheLifetime() {
        return null;
    }

    /**
     * @return bool
     */
    public function getReviewSummary() {
        $summaryData = [];
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($websiteId || $storeId) {
            if ($data = $this->reviewHelper->getSummaryData($storeId, $websiteId)) {
                $summaryData[] = $data;
            }
        } else {
            $summaryData = $this->reviewHelper->getAllSummaryData();
        }

        return $summaryData;
    }

    /**
     * @param AbstractElement $element
     *
     * @return bool
     */
    public function render(AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return bool
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }
}

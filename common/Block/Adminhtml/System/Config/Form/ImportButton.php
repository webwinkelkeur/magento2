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

class ImportButton extends Field {
    protected $_template = '%s::system/config/button/button.phtml';

    private $extension;

    private $reviewHelper;

    private $request;

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

    public function render(AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    public function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }

    public function getAjaxUrl() {
        $storeId = $this->request->getParam('store', 0);
        $websiteId = $this->request->getParam('website');
        if (!empty($websiteId)) {
            return $this->getUrl($this->extension->getSlug() . '/actions/import/website/' . $websiteId);
        }
        return $this->getUrl($this->extension->getSlug() . '/actions/import/store/' . $storeId);
    }

    public function getLastImported() {
        return $this->reviewHelper->getLastImported();
    }

    public function getButtonHtml() {
        if (!$this->checkConnectorData()) {
            $buttonData = ['id' => 'import_button', 'label' => __('Manually import summary'), 'class' => 'disabled'];
        } else {
            $buttonData = ['id' => 'import_button', 'label' => __('Manually import summary')];
        }

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);

        return $button->toHtml();
    }

    public function checkConnectorData() {
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($oauthData = $this->reviewHelper->getConnectorData($storeId, $websiteId)) {
            return true;
        }

        return false;
    }

    public function getSlug() {
        return $this->extension->getSlug();
    }
}

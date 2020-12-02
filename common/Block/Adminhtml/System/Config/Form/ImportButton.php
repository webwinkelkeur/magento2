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
    /**
     * @var string
     */
    protected $_template = '%s::system/config/button/button.phtml';

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
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl() {
        $storeId = $this->request->getParam('store', 0);
        $websiteId = $this->request->getParam('website');
        if (!empty($websiteId)) {
            return $this->getUrl($this->extension->getSlug() . '/actions/import/website/' . $websiteId);
        }
        return $this->getUrl($this->extension->getSlug() . '/actions/import/store/' . $storeId);
    }

    /**
     * Get's last imported date to display as comment msg under button
     *
     * @return mixed
     */
    public function getLastImported() {
        return $this->reviewHelper->getLastImported();
    }

    /**
     * @return mixed
     */
    public function getButtonHtml() {
        if (!$this->checkConnectorData()) {
            $buttonData = ['id' => 'import_button', 'label' => __('Manually import summary'), 'class' => 'disabled'];
        } else {
            $buttonData = ['id' => 'import_button', 'label' => __('Manually import summary')];
        }

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);

        return $button->toHtml();
    }

    /**
     * @return bool
     */
    public function checkConnectorData() {
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($oauthData = $this->reviewHelper->getConnectorData($storeId, $websiteId)) {
            return true;
        }

        return false;
    }
}

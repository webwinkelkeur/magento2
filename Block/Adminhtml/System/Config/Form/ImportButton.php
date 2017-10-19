<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magmodules\WebwinkelKeur\Helper\Reviews as ReviewsHelper;

class ImportButton extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Magmodules_WebwinkelKeur::system/config/button/button.phtml';

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
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ReviewsHelper $reviewHelper,
        array $data = []
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        $storeId = $this->request->getParam('store', 0);
        $websiteId = $this->request->getParam('website');
        if (!empty($websiteId)) {
            return $this->getUrl('webwinkelkeur/actions/import/website/' . $websiteId);
        } else {
            return $this->getUrl('webwinkelkeur/actions/import/store/' . $storeId);
        }
    }

    /**
     * Get's last imported date to display as comment msg under button
     *
     * @return mixed
     */
    public function getLastImported()
    {
        return $this->reviewHelper->getLastImported();
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
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
    public function checkConnectorData()
    {
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($oauthData = $this->reviewHelper->getConnectorData($storeId, $websiteId)) {
            return true;
        }

        return false;
    }
}

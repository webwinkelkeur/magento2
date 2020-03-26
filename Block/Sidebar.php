<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebwinkelKeur\Magento2\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use WebwinkelKeur\Magento2\Helper\General as GeneralHelper;

class Sidebar extends Template implements BlockInterface {
    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * Summary constructor.
     *
     * @param Context       $context
     * @param GeneralHelper $generalHelper
     * @param array         $data
     *
     * @internal param ReviewsHelper $reviewHelper
     */
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->generalHelper = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if sidebar is enabled
     *
     * @return bool|mixed
     */
    public function getEnabledSidebar() {
        return $this->generalHelper->getEnabledSidebar();
    }

    /**
     * Returns WebshopId for _webwinkelkeur_id JS
     *
     * @return mixed
     */
    public function getWebshopId() {
        return $this->generalHelper->getWebshopId();
    }

    /**
     * @return mixed
     */
    public function getLanguage() {
        return $this->generalHelper->getLanguage();
    }
}

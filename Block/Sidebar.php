<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Backend\Block\Template\Context;
use Magmodules\WebwinkelKeur\Helper\General as GeneralHelper;

class Sidebar extends Template implements BlockInterface
{

    protected $general;
    protected $storeManager;

    /**
     * Summary constructor.
     * @param Context $context
     * @param GeneralHelper $generalHelper
     * @param array $data
     * @internal param ReviewsHelper $revHelper
     */
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->general = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if sidebar is enabled
     * @return bool|mixed
     */
    public function getEnabledSidebar()
    {
        return $this->general->getEnabledSidebar();
    }

    /**
     * Returns WebshopId for _webwinkelkeur_id JS
     * @return mixed
     */
    public function getWebshopId()
    {
        return $this->general->getWebshopId();
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->general->getLanguage();
    }
}

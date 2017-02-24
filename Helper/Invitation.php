<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magmodules\WebwinkelKeur\Helper\General as GeneralHelper;

class Invitation extends AbstractHelper
{

    const XML_PATH_INVITATION_ENABLED = 'magmodules_webwinkelkeur/invitation/enabled';
    const XML_PATH_API_WEBSHOP_ID = 'magmodules_webwinkelkeur/api/webshop_id';
    const XML_PATH_API_API_KEY = 'magmodules_webwinkelkeur/api/api_key';
    const XML_PATH_INVITATION_LANGUAGE = 'magmodules_webwinkelkeur/invitation/language';
    const XML_PATH_INVITATION_STATUS = 'magmodules_webwinkelkeur/invitation/status';
    const XML_PATH_INVITATION_DELAY = 'magmodules_webwinkelkeur/invitation/delay';
    const XML_PATH_INVITATION_BACKLOG = 'magmodules_webwinkelkeur/invitation/backlog';
    const XML_PATH_RESEND = 'magmodules_webwinkelkeur/invitation/resend_double';
    const XML_PATH_INVITATION_DEBUG = 'magmodules_webwinkelkeur/invitation/debug';

    protected $productRepository;
    protected $imgHelper;
    protected $general;
    protected $storeManager;

    /**
     * Invitation constructor.
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Image $imgHelper
     * @param General $generalHelper
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Image $imgHelper,
        GeneralHelper $generalHelper
    ) {
        $this->productRepository = $productRepository;
        $this->imgHelper = $imgHelper;
        $this->general = $generalHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Create array of invitation config data
     * @param $storeId
     * @return array|bool
     */
    public function getConfigData($storeId)
    {
        if ($this->getEnabledInvitation($storeId)) {
            $config = [];
            $config['webshop_id'] = $this->general->getStoreValue(self::XML_PATH_API_WEBSHOP_ID, $storeId);
            $config['api_key'] = $this->general->getStoreValue(self::XML_PATH_API_API_KEY, $storeId);
            $config['language'] = $this->general->getStoreValue(self::XML_PATH_INVITATION_LANGUAGE, $storeId);
            $config['status'] = $this->general->getStoreValue(self::XML_PATH_INVITATION_STATUS, $storeId);
            $config['delay'] = $this->general->getStoreValue(self::XML_PATH_INVITATION_DELAY, $storeId);
            $config['backlog'] = ($this->general->getStoreValue(self::XML_PATH_INVITATION_BACKLOG, $storeId) * 86400);
            $config['resend'] = ($this->general->getStoreValue(self::XML_PATH_RESEND, $storeId) == 1) ? 0 : 1;
            $config['noremail'] = ($this->general->getStoreValue(self::XML_PATH_RESEND, $storeId) == 1) ? 0 : 1;
            $config['debug'] = $this->general->getStoreValue(self::XML_PATH_INVITATION_DEBUG, $storeId);

            if (empty($config['backlog'])) {
                $config['backlog'] = (30 * 86400);
            }

            if (empty($config['webshop_id']) || empty($config['api_key'])) {
                return false;
            }

            return $config;
        }

        return false;
    }

    /**
     * Check if Invitation is enabled
     * @param $storeId
     * @return bool|mixed
     */
    public function getEnabledInvitation($storeId)
    {
        if ($this->getEnabled($storeId)) {
            return $this->general->getStoreValue(self::XML_PATH_INVITATION_ENABLED, $storeId);
        }

        return true;
    }

    /**
     * Check if extension is enabled
     * @param $storeId
     * @return mixed
     */
    public function getEnabled($storeId)
    {
        return $this->general->getEnabled($storeId);
    }
}

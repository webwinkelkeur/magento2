<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebwinkelKeur\Magento2\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use WebwinkelKeur\Magento2\Helper\General as GeneralHelper;

class Invitation extends AbstractHelper
{

    const XPATH_INVITATION_ENABLED = 'magmodules_webwinkelkeur/invitation/enabled';
    const XPATH_API_WEBSHOP_ID = 'magmodules_webwinkelkeur/api/webshop_id';
    const XPATH_API_API_KEY = 'magmodules_webwinkelkeur/api/api_key';
    const XPATH_INVITATION_LANGUAGE = 'magmodules_webwinkelkeur/invitation/language';
    const XPATH_INVITATION_STATUS = 'magmodules_webwinkelkeur/invitation/status';
    const XPATH_INVITATION_DELAY = 'magmodules_webwinkelkeur/invitation/delay';
    const XPATH_INVITATION_BACKLOG = 'magmodules_webwinkelkeur/invitation/backlog';
    const XPATH_RESEND = 'magmodules_webwinkelkeur/invitation/resend_double';
    const XPATH_INVITATION_DEBUG = 'magmodules_webwinkelkeur/invitation/debug';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Image
     */
    private $imgHelper;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Invitation constructor.
     *
     * @param Context               $context
     * @param ProductRepository     $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Image                 $imgHelper
     * @param General               $generalHelper
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
        $this->generalHelper = $generalHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Create array of invitation config data
     *
     * @param $storeId
     *
     * @return array|bool
     */
    public function getConfigData($storeId)
    {
        if ($this->getEnabledInvitation($storeId)) {
            $config = [];
            $config['webshop_id'] = $this->generalHelper->getStoreValue(self::XPATH_API_WEBSHOP_ID, $storeId);
            $config['api_key'] = $this->generalHelper->getStoreValue(self::XPATH_API_API_KEY, $storeId);
            $config['language'] = $this->generalHelper->getStoreValue(self::XPATH_INVITATION_LANGUAGE, $storeId);
            $config['status'] = $this->generalHelper->getStoreValue(self::XPATH_INVITATION_STATUS, $storeId);
            $config['delay'] = $this->generalHelper->getStoreValue(self::XPATH_INVITATION_DELAY, $storeId);
            $config['backlog'] = ($this->generalHelper->getStoreValue(self::XPATH_INVITATION_BACKLOG, $storeId) * 86400);
            $config['resend'] = ($this->generalHelper->getStoreValue(self::XPATH_RESEND, $storeId) == 1) ? 0 : 1;
            $config['noremail'] = ($this->generalHelper->getStoreValue(self::XPATH_RESEND, $storeId) == 1) ? 0 : 1;
            $config['debug'] = $this->generalHelper->getStoreValue(self::XPATH_INVITATION_DEBUG, $storeId);

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
     *
     * @param $storeId
     *
     * @return bool|mixed
     */
    public function getEnabledInvitation($storeId)
    {
        if ($this->getEnabled($storeId)) {
            return $this->generalHelper->getStoreValue(self::XPATH_INVITATION_ENABLED, $storeId);
        }

        return true;
    }

    /**
     * Check if extension is enabled
     *
     * @param $storeId
     *
     * @return mixed
     */
    public function getEnabled($storeId)
    {
        return $this->generalHelper->getEnabled($storeId);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return mixed
     */
    public function getCustomerName($order)
    {
        if ($order->getCustomerId()) {
            return $order->getCustomerName();
        }

        $firstname = $order->getBillingAddress()->getFirstname();
        $middlename = $order->getBillingAddress()->getMiddlename();
        $lastname = $order->getBillingAddress()->getLastname();

        if (!empty($middlename)) {
            return $firstname . ' ' . $middlename . ' ' . $lastname;
        } else {
            return $firstname . ' ' . $lastname;
        }
    }
}

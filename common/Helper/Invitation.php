<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Invitation extends AbstractHelper {
    const XPATH_INVITATION_ENABLED = '_magento2/invitation/enabled';
    const XPATH_API_WEBSHOP_ID = '_magento2/api/webshop_id';
    const XPATH_API_API_KEY = '_magento2/api/api_key';
    const XPATH_INVITATION_LANGUAGE = '_magento2/invitation/language';
    const XPATH_INVITATION_STATUS = '_magento2/invitation/status';
    const XPATH_INVITATION_DELAY = '_magento2/invitation/delay';
    const XPATH_INVITATION_BACKLOG = '_magento2/invitation/backlog';
    const XPATH_RESEND = '_magento2/invitation/resend_double';
    const XPATH_CONSENT_FLOW = '_magento2/invitation/consent_flow';
    const XPATH_INVITATION_DEBUG = '_magento2/invitation/debug';
    const XPATH_PRODUCT_REVIEWS = '_magento2/invitation/product_reviews';
    const XPATH_RATING_OPTIONS = '_magento2/invitation/rating_options';
    const XPATH_GTIN_KEY = '_magento2/invitation/gtin_key';

    private $extension;

    private $productRepository;

    private $imgHelper;

    private $generalHelper;

    private $storeManager;

    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Image $imgHelper,
        GeneralHelper $generalHelper,
        ExtensionBase $extension
    ) {
        $this->productRepository = $productRepository;
        $this->imgHelper = $imgHelper;
        $this->generalHelper = $generalHelper;
        $this->storeManager = $storeManager;
        $this->extension = $extension;
        parent::__construct($context);
    }

    public function getConfigData($storeId) {
        if ($this->getEnabledInvitation($storeId)) {
            $config = [];
            $config['webshop_id'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_API_WEBSHOP_ID,
                $storeId
            );
            $config['api_key'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_API_API_KEY,
                $storeId
            );
            $config['language'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_LANGUAGE,
                $storeId
            );
            $config['status'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_STATUS,
                $storeId
            );
            $config['delay'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_DELAY,
                $storeId
            );
            $config['backlog'] = ($this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_BACKLOG,
                $storeId
                ) * 86400);
            $config['resend'] = ($this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_RESEND,
                $storeId) == 1
            ) ? 0 : 1;
            $config['noremail'] = ($this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_RESEND,
                $storeId) == 1
            ) ? 0 : 1;
            $config['consent_flow'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_CONSENT_FLOW,
                $storeId
            );
            $config['debug'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_DEBUG,
                $storeId
            );

            $config['product_reviews'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_PRODUCT_REVIEWS,
                $storeId
            );
            $config['rating_options'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_RATING_OPTIONS,
                $storeId
            );
            $config['gtin_key'] = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_GTIN_KEY,
                $storeId
            );

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

    public function getEnabledInvitation($storeId) {
        if ($this->getEnabled($storeId)) {
            return $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_INVITATION_ENABLED,
                $storeId
            );
        }

        return false;
    }

    public function getEnabled($storeId) {
        return $this->generalHelper->getEnabled($storeId);
    }
    
    public function getCustomerName($order) {
        if ($order->getCustomerId()) {
            return $order->getCustomerName();
        }

        $firstname = $order->getBillingAddress()->getFirstname();
        $middlename = $order->getBillingAddress()->getMiddlename();
        $lastname = $order->getBillingAddress()->getLastname();

        if (!empty($middlename)) {
            return $firstname . ' ' . $middlename . ' ' . $lastname;
        }
        return $firstname . ' ' . $lastname;
    }
}

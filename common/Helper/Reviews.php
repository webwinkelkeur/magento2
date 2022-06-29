<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Helper;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Reviews extends AbstractHelper {
    const XPATH_REVIEWS_ENABLED = '_magento2/reviews/enabled';
    const XPATH_REVIEWS_WEBSHOP_ID = '_magento2/api/webshop_id';
    const XPATH_REVIEWS_API_KEY = '_magento2/api/api_key';
    const XPATH_REVIEWS_RESULT = '_magento2/reviews/result';
    const XPATH_REVIEWS_LAST_IMPORT = '_magento2/reviews/last_import';

    private $extension;

    private $datetime;

    private $timezone;

    private $storeManager;

    private $generalHelper;

    private $cacheTypeList;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $datetime,
        TimezoneInterface $timezone,
        GeneralHelper $generalHelper,
        TypeListInterface $cacheTypeList,
        ExtensionBase $extension
    ) {
        $this->datetime = $datetime;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->generalHelper = $generalHelper;
        $this->cacheTypeList = $cacheTypeList;
        $this->extension = $extension;
        parent::__construct($context);
    }

    public function getUniqueConnectorData() {
        $stores = $this->storeManager->getStores();
        $connectorData = [];
        foreach ($stores as $store) {
            if ($data = $this->getConnectorData($store->getId())) {
                $connectorData[$data['webshop_id']] = $data;
            }
        }

        return $connectorData;
    }

    public function getConnectorData($storeId = 0, $websiteId = null) {
        $connectorData = [];

        if ($websiteId) {
            $reviewsEnabled = $this->generalHelper->getWebsiteValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_ENABLED,
                $websiteId
            );
            $webshopId = $this->generalHelper->getWebsiteValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_WEBSHOP_ID,
                $websiteId
            );
            $apiKey = $this->generalHelper->getWebsiteValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_API_KEY,
                $websiteId
            );
        } else {
            $reviewsEnabled = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_ENABLED,
                $storeId
            );
            $webshopId = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_WEBSHOP_ID,
                $storeId
            );
            $apiKey = $this->generalHelper->getStoreValue(
                $this->extension->getSlug() . self::XPATH_REVIEWS_API_KEY,
                $storeId
            );
        }

        if ($reviewsEnabled && $webshopId && $apiKey) {
            $connectorData = [
                'store_id' => $storeId,
                'webshop_id' => $webshopId,
                'api_key' => $apiKey,
            ];
        }

        return $connectorData;
    }

    public function saveReviewResult($result, $type = 'cron') {
        $summaryData = [];
        foreach ($result as $key => $row) {
            $error = '';
            if ($row['webshop']['status'] != 'success') {
                $error = $row['webshop']['msg'];
            }
            if ($row['ratings_summary']['status'] != 'success') {
                $error = $row['ratings_summary']['msg'];
            }
            if (empty($error)) {
                $rating = $row['ratings_summary']['ratings_summary'];
                $webshop = $row['webshop']['webshop'];
                $summaryData[$key]['status'] = 'success';
                $summaryData[$key]['type'] = $type;
                $summaryData[$key]['name'] = $webshop['name'];
                $summaryData[$key]['logo'] = $webshop['logo'];
                foreach ($webshop['languages'] as $language) {
                    $iso = $language['iso'];
                    $url = $language['url'];
                    $summaryData[$key]['link'][$iso] = $url;
                    if ($language['main']) {
                        $summaryData[$key]['link']['default'] = $url;
                        $summaryData[$key]['iso'] = $language['iso'];
                    }
                }
                $summaryData[$key]['total_reviews'] = $rating['amount'];
                $summaryData[$key]['score'] = number_format((float) $rating['rating_average'], 1, '.', '');
                $summaryData[$key]['score_max'] = '10';
                $summaryData[$key]['percentage'] = round($rating['rating_average'] * 10) . '%';
            } else {
                $summaryData[$key]['status'] = 'error';
                $summaryData[$key]['msg'] = $error;
            }
        }
        $updateMsg = $this->datetime->gmtDate() . ' (' . $type . ')';
        $this->generalHelper->setConfigData(
            json_encode($summaryData),
           $this->extension->getSlug() . self::XPATH_REVIEWS_RESULT
        );
        $this->generalHelper->setConfigData(
            $updateMsg,
            $this->extension->getSlug() . self::XPATH_REVIEWS_LAST_IMPORT
        );

        return $summaryData;
    }

    public function getSummaryData($storeId) {
        $webshopId = $this->generalHelper->getStoreValue(
            $this->extension->getSlug() . self::XPATH_REVIEWS_WEBSHOP_ID,
            $storeId
        );

        if (!$reviews_result = $this->generalHelper->getStoreValue($this->extension->getSlug() . self::XPATH_REVIEWS_RESULT, $storeId)) {
            return false;
        }

        $data = json_decode($reviews_result, true);

        if (!empty($data[$webshopId]['status'])) {
            if ($data[$webshopId]['status'] == 'success') {
                return $data[$webshopId];
            }
        }

        return false;
    }

    public function getAllSummaryData() {
        $reviews_result = $this->generalHelper->getStoreValue($this->extension->getSlug() . self::XPATH_REVIEWS_RESULT);
        if (!$reviews_result) {
            return [];
        }
        return json_decode($reviews_result, true);
    }

    public function getLastImported() {
        return $this->generalHelper->getStoreValue($this->extension->getSlug() .  self::XPATH_REVIEWS_LAST_IMPORT);
    }
}

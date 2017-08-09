<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\WebwinkelKeur\Helper;

use Magmodules\WebwinkelKeur\Helper\General as GeneralHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class Reviews extends AbstractHelper
{

    const XML_PATH_REVIEWS_ENABLED = 'magmodules_webwinkelkeur/reviews/enabled';
    const XML_PATH_REVIEWS_WEBSHOP_ID = 'magmodules_webwinkelkeur/api/webshop_id';
    const XML_PATH_REVIEWS_API_KEY = 'magmodules_webwinkelkeur/api/api_key';
    const XML_PATH_REVIEWS_RESULT = 'magmodules_webwinkelkeur/reviews/result';
    const XML_PATH_REVIEWS_LAST_IMPORT = 'magmodules_webwinkelkeur/reviews/last_import';

    protected $datetime;
    protected $timezone;
    protected $storeManager;
    protected $general;
    protected $config;
    protected $cacheTypeList;

    /**
     * Reviews constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $datetime
     * @param TimezoneInterface $timezone
     * @param General $generalHelper
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $datetime,
        TimezoneInterface $timezone,
        GeneralHelper $generalHelper,
        TypeListInterface $cacheTypeList
    ) {
        $this->datetime = $datetime;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->general = $generalHelper;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    /**
     * Get array of unique connectors
     * @return array
     */
    public function getUniqueConnectorData()
    {
        $stores = $this->storeManager->getStores();
        $connectorData = [];
        foreach ($stores as $store) {
            if ($data = $this->getConnectorData($store->getId())) {
                $connectorData[$data['webshop_id']] = $data;
            }
        }

        return $connectorData;
    }

    /**
     * @param int $storeId
     * @param null $websiteId
     * @return array
     */
    public function getConnectorData($storeId = 0, $websiteId = null)
    {
        $connectorData = [];

        if ($websiteId) {
            $reviewsEnabled = $this->general->getWebsiteValue(self::XML_PATH_REVIEWS_ENABLED, $websiteId);
            $webshopId = $this->general->getWebsiteValue(self::XML_PATH_REVIEWS_WEBSHOP_ID, $websiteId);
            $apiKey = $this->general->getWebsiteValue(self::XML_PATH_REVIEWS_API_KEY, $websiteId);
        } else {
            $reviewsEnabled = $this->general->getStoreValue(self::XML_PATH_REVIEWS_ENABLED, $storeId);
            $webshopId = $this->general->getStoreValue(self::XML_PATH_REVIEWS_WEBSHOP_ID, $storeId);
            $apiKey = $this->general->getStoreValue(self::XML_PATH_REVIEWS_API_KEY, $storeId);
        }

        if ($reviewsEnabled && $webshopId && $apiKey) {
            $connectorData = [
                'store_id' => $storeId,
                'webshop_id' => $webshopId,
                'api_key' => $apiKey
            ];
        }

        return $connectorData;
    }

    /**
     * Save results to config
     * @param $result
     * @param string $type
     * @return array
     */
    public function saveReviewResult($result, $type = 'cron')
    {
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
                $summaryData[$key]['score'] = number_format((float)$rating['rating_average'], 1, '.', '');
                $summaryData[$key]['score_max'] = '10';
                $summaryData[$key]['percentage'] = round($rating['rating_average'] * 10) . '%';
            } else {
                $summaryData[$key]['status'] = 'error';
                $summaryData[$key]['msg'] = $error;
            }
        }
        $updateMsg = $this->datetime->gmtDate() . ' (' . $type . ')';
        $this->general->setConfigData(json_encode($summaryData), self::XML_PATH_REVIEWS_RESULT);
        $this->general->setConfigData($updateMsg, self::XML_PATH_REVIEWS_LAST_IMPORT);

        return $summaryData;
    }

    /**
     * Summay data getter for block usage
     * @param int $storeId
     * @return mixed
     */
    public function getSummaryData($storeId)
    {
        $webshopId = $this->general->getStoreValue(self::XML_PATH_REVIEWS_WEBSHOP_ID, $storeId);
        $data = json_decode($this->general->getStoreValue(self::XML_PATH_REVIEWS_RESULT, $storeId), true);
        if (!empty($data[$webshopId]['status'])) {
            if ($data[$webshopId]['status'] == 'success') {
                return $data[$webshopId];
            }
        }

        return false;
    }

    /**
     * Array of all stored summay data
     * @return mixed
     */
    public function getAllSummaryData()
    {
        return json_decode($this->general->getStoreValue(self::XML_PATH_REVIEWS_RESULT), true);
    }

    /**
     * Last imported date
     * @return mixed
     */
    public function getLastImported()
    {
        $lastImported = $this->general->getStoreValue(self::XML_PATH_REVIEWS_LAST_IMPORT);

        return $lastImported;
    }
}

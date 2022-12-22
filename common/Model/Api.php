<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Helper\Invitation as InvitationHelper;
use Valued\Magento2\Helper\Reviews as ReviewsHelper;
use Valued\Magento2\Setup\ExtensionBase;

class Api {
    const REVIEWS_URL = 'https://%s/api/1.0/ratings_summary.json?id=%s&code=%s';
    const INVITATION_URL = 'https://%s/api/1.0/invitations.json?id=%s&code=%s';
    const WEBSHOP_URL = 'https://%s/api/1.0/webshop.json?id=%s&code=%s';
    const SYNC_URL = 'https://%s/webshops/magento_sync_url';
    const GTIN_KEY = 'gtin_key';

    const DEFAULT_TIMEOUT = 5;

    private $extension;

    private $invitationHelper;

    private $reviewHelper;

    private $curl;

    private $logger;

    private $generalHelper;

    private $date;

    private $productRepository;

    public function __construct(
        ReviewsHelper $reviewHelper,
        GeneralHelper $generalHelper,
        InvitationHelper $invitationHelper,
        Curl $curl,
        DateTime $dateTime,
        LoggerInterface $logger,
        ExtensionBase $extension,
        ProductRepository $productRepository,
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->generalHelper = $generalHelper;
        $this->invitationHelper = $invitationHelper;
        $this->curl = $curl;
        $this->date = $dateTime;
        $this->logger = $logger;
        $this->extension = $extension;
        $this->productRepository = $productRepository;
    }

    public function getReviews($type) {
        $connectorData = $this->reviewHelper->getUniqueConnectorData();
        $result = [];
        foreach ($connectorData as $key => $data) {
            $result[$key]['ratings_summary'] = $this->updateReviewStats($data);
            $result[$key]['webshop'] = $this->updateWebshopData($data);
        }
        return $this->reviewHelper->saveReviewResult($result, $type);
    }

    public function updateReviewStats($data) {
        try {
            $url = sprintf(
                self::REVIEWS_URL,
                $this->extension->getDashboardDomain(),
                $data['webshop_id'],
                $data['api_key']
            );
            $curl = $this->curl;
            $curl->addOption(CURLOPT_URL, $url);
            $curl->addOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->addOption(CURLOPT_SSL_VERIFYPEER, false);
            $curl->connect($url);
            $response = $curl->read();
            $result = json_decode($response, true);

            if (!empty($result['status'])) {
                if ($result['status'] == 'error') {
                    return $this->generalHelper->createResponseError($result['message']);
                }
                return ['status' => 'success', 'ratings_summary' => $result['data']];
            }
            return $this->generalHelper->createResponseError(__('General Error'));
        } catch (\Exception $e) {
            return $this->generalHelper->createResponseError($e);
        }
    }

    public function updateWebshopData($data) {
        try {
            $url = sprintf(
                self::WEBSHOP_URL,
                $this->extension->getDashboardDomain(),
                $data['webshop_id'],
                $data['api_key']
            );
            $curl = $this->curl;
            $curl->addOption(CURLOPT_URL, $url);
            $curl->addOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->addOption(CURLOPT_SSL_VERIFYPEER, false);
            $curl->connect($url);
            $response = $curl->read();
            $result = json_decode($response, true);
            if (!empty($result['status'])) {
                if ($result['status'] == 'error') {
                    return $this->generalHelper->createResponseError($result['message']);
                }
                return ['status' => 'success', 'webshop' => $result['data']];
            }
            return $this->generalHelper->createResponseError(__('General Error'));
        } catch (\Exception $e) {
            return $this->generalHelper->createResponseError($e);
        }
    }

    public function sendInvitation(Order $order) {
        $storeId = $order->getStoreId();

        $config = $this->invitationHelper->getConfigData($storeId);
        if (empty($config)) {
            return false;
        }

        if ($order->getStatus() != $config['status']) {
            return false;
        }

        $date_diff = (time() - $this->date->strToTime($order->getCreatedAt()));
        if ($date_diff > $config['backlog']) {
            return false;
        }

        $request['email'] = $order->getCustomerEmail();
        $request['order'] = $order->getIncrementId();
        $request['delay'] = $config['delay'];
        $request['customer_name'] = $this->invitationHelper->getCustomerName($order);
        $request['client'] = 'magento2';
        $request['noremail'] = $config['noremail'];
        $orderItems = $order->getItems();
        $orderData = [
            'products' => $this->getProducts($orderItems, $config)
        ];
        $request['order_data'] = json_encode($orderData);

        if (!empty($config['language'])) {
            $request['language'] = $config['language'];
            if ($config['language'] == 'cus') {
                $lanArray = ['NL' => 'nld', 'EN' => 'eng', 'DE' => 'deu', 'FR' => 'fra', 'ES' => 'spa'];
                if (!empty($address)) {
                    if (isset($lanArray[$address->getCountry()])) {
                        $request['language'] = $lanArray[$address->getCountry()];
                    }
                }
            }
        }
        return $this->postInvitation($request, $config);
    }

    private function getProducts($orderItems, $config) {
        if (empty($config['product_reviews'])) {
            return [];
        }

        $products = [];
        foreach ($orderItems as $item) {
            $id = $item->getProductId();
            $product = $this->productRepository->getById($id);
            $products[] = [
                'id' => $id,
                'name' => $product->getName(),
                'url' => $product->getUrlModel()->getUrl($product),
                'image_url' => $this->getProductImageUrl($product),
                'sku' => $product->getSku(),
                'gtin' => $this->getProductGtinValue($product, $config)
            ];
        }

        return $products;
    }

    private function getProductImageUrl($product) {
        return $product->getResource()->getAttribute('image')->getFrontend()->getUrl($product);
    }

    private function getProductGtinValue($product, $config) {
        if (empty($config[self::GTIN_KEY])) {
            return null;
        }

        $value = $product->getData($config[self::GTIN_KEY]);
        if (!is_numeric($value)) {
            return null;
        }

        return $value;
    }

    public function postInvitation($request, $config) {
        $url = sprintf(
            self::INVITATION_URL, $this->extension->getDashboardDomain(),
            $config['webshop_id'],
            $config['api_key']
        );
        try {
            $curl = $this->curl;
            $curl->addOption(CURLOPT_RETURNTRANSFER, true);
            $curl->addOption(CURLOPT_FOLLOWLOCATION, true);
            $curl->addOption(CURLOPT_POST, true);
            $curl->addOption(CURLOPT_POSTFIELDS, $request);
            $curl->addOption(CURLOPT_URL, $url);
            $curl->addOption(CURLOPT_HEADER, false);
            $curl->addOption(CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT);
            $curl->connect($url);
            $response = json_decode($curl->read(), true);
            if (!empty($response['status'])) {
                $status = $response['status'];
                $message = $response['message'];
            } else {
                $status = 'unknown';
                $message = 'unknown error';
            }
            if (!empty($config['debug'])) {
                $debugMsg = $this->extension->getName() . ' - Invitation #' . $request['order'] . ' ';
                $debugMsg .= '(Status: ' . $status . ', Msg: ' . $message . ', ';
                $debugMsg .= 'Url: ' . $url . ', Data: ' . json_encode($request) . ')';
                $this->logger->debug($debugMsg);
            }
            if ($status != 'unknown') {
                return $response;
            }
        } catch (\Exception $e) {
            if (!empty($config['debug'])) {
                $debugMsg = $this->extension->getName() . ' - Invitation #' . $request['order'] . ' ';
                $debugMsg .= '(Error: ' . $e . ', Request: ' . $url . ' Data: ' . json_encode($request) . ')';
                $this->logger->debug($debugMsg);
            }
        }

        return false;
    }

    public function sendSyncUrl($syncUrl, $storeId) {
        $config = $this->invitationHelper->getConfigData($storeId);
        if (empty($config['product_reviews'])) {
            return;
        }

        $url = sprintf(self::SYNC_URL, $this->extension->getDashboardDomain());
        $data = [
            'webshop_id' => $config['webshop_id'],
            'api_key' => $config['api_key'],
            'url' => $syncUrl,

        ];

        try {
            $this->doSendSyncUrl($url, $data);
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('(Error: "%s" URL: %s)', $e->getMessage(), $url));
        }
    }

    private function doSendSyncUrl($url, $data) {
        $curl = $this->curl;
        $curl->addOption(CURLOPT_RETURNTRANSFER, true);
        $curl->addOption(CURLOPT_FOLLOWLOCATION, true);
        $curl->addOption(CURLOPT_POST, true);
        $curl->addOption(CURLOPT_POSTFIELDS, json_encode($data));
        $curl->addOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $curl->addOption(CURLOPT_FAILONERROR, true);
        $curl->addOption(CURLOPT_URL, $url);
        $curl->connect($url);
        $curl->read();
        if ($curl->getErrno()) {
            throw new \Exception($curl->getError());
        }
    }
}

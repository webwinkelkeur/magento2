<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Model;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Valued\Magento2\Helper\General as GeneralHelper;
use Valued\Magento2\Helper\Invitation as InvitationHelper;
use Valued\Magento2\Helper\Reviews as ReviewsHelper;

class Api {
    //TODO make generic
    const REVIEWS_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/ratings_summary.json?id=%s&code=%s';
    //TODO make generic
    const INVITATION_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json?id=%s&code=%s';
    //TODO make generic
    const WEBSHOP_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/webshop.json?id=%s&code=%s';

    const DEFAULT_TIMEOUT = 5;

    /**
     * @var InvitationHelper
     */
    private $inviationHelper;

    /**
     * @var ReviewsHelper
     */
    private $reviewHelper;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * Api constructor.
     *
     * @param ReviewsHelper    $reviewHelper
     * @param GeneralHelper    $generalHelper
     * @param InvitationHelper $inviationHelper
     * @param Curl             $curl
     * @param DateTime         $dateTime
     * @param LoggerInterface  $logger
     */
    public function __construct(
        ReviewsHelper $reviewHelper,
        GeneralHelper $generalHelper,
        InvitationHelper $inviationHelper,
        Curl $curl,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->generalHelper = $generalHelper;
        $this->inviationHelper = $inviationHelper;
        $this->curl = $curl;
        $this->date = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Get Reviews by looping unique connector data
     *
     * @param $type
     *
     * @return array
     */
    public function getReviews($type) {
        $connectorData = $this->reviewHelper->getUniqueConnectorData();
        $result = [];
        foreach ($connectorData as $key => $data) {
            $result[$key]['ratings_summary'] = $this->updateReviewStats($data);
            $result[$key]['webshop'] = $this->updateWebshopData($data);
        }
        return $this->reviewHelper->saveReviewResult($result, $type);
    }

    /**
     * Get summary data from WebwinkelKeur API
     *
     * @param $data
     *
     * @return array|mixed
     */
    public function updateReviewStats($data) {
        try {
            $url = sprintf(self::REVIEWS_URL, $data['webshop_id'], $data['api_key']);
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

    /**
     * @param $data
     *
     * @return array|mixed
     */
    public function updateWebshopData($data) {
        try {
            $url = sprintf(self::WEBSHOP_URL, $data['webshop_id'], $data['api_key']);
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

    /**
     * SendInviation function for Orders
     *
     * @param Order $order
     *
     * @return bool|mixed
     */
    public function sendInvitation(Order $order) {
        $storeId = $order->getStoreId();

        $config = $this->inviationHelper->getConfigData($storeId);
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
        $request['customer_name'] = $this->inviationHelper->getCustomerName($order);
        $request['client'] = 'magento2';
        $request['noremail'] = $config['noremail'];

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

    /**
     * Post order data for invitation
     *
     * @param $request
     * @param $config
     *
     * @return bool|mixed
     */
    public function postInvitation($request, $config) {
        $url = sprintf(self::INVITATION_URL, $config['webshop_id'], $config['api_key']);
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
                //TODO make generic
                $debugMsg = 'WebwinkelKeur - Invitation #' . $request['order'] . ' ';
                $debugMsg .= '(Status: ' . $status . ', Msg: ' . $message . ', ';
                $debugMsg .= 'Url: ' . $url . ', Data: ' . json_encode($request) . ')';
                $this->logger->debug($debugMsg);
            }
            if ($status != 'unknown') {
                return $response;
            }
        } catch (\Exception $e) {
            if (!empty($config['debug'])) {
                //TODO make generic
                $debugMsg = 'WebwinkelKeur - Invitation #' . $request['order'] . ' ';
                $debugMsg .= '(Error: ' . $e . ', Request: ' . $url . ' Data: ' . json_encode($request) . ')';
                $this->logger->debug($debugMsg);
            }
        }

        return false;
    }
}

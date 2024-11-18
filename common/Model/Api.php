<?php

namespace Valued\Magento2\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection\Interceptor;
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
    const ORDER_CONSENT_URL = 'https://%s/api/2.0/order_permissions.json?id=%s&code=%s&orderNumber=%s';
    const GTIN_KEY = 'gtin_key';
    const DEFAULT_TIMEOUT = 5;

    /** @var ExtensionBase */
    private $extension;

    /** @var InvitationHelper */
    private $invitationHelper;

    /** @var ReviewsHelper */
    private $reviewHelper;

    private $curl;

    /** @var LoggerInterface */
    private $logger;

    /** @var GeneralHelper */
    private $generalHelper;

    /** @var DateTime */
    private $date;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ModuleListInterface */
    private $moduleList;

    /** @var ProductMetadataInterface */
    private $productMetadata;

    public function __construct(
        ReviewsHelper            $reviewHelper,
        GeneralHelper            $generalHelper,
        InvitationHelper         $invitationHelper,
        DateTime                 $dateTime,
        LoggerInterface          $logger,
        ExtensionBase            $extension,
        ProductRepository        $productRepository,
        ModuleListInterface      $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->generalHelper = $generalHelper;
        $this->invitationHelper = $invitationHelper;
        $this->date = $dateTime;
        $this->logger = $logger;
        $this->extension = $extension;
        $this->productRepository = $productRepository;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
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

            $result = $this->doRequest($url, 'GET');
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

            $result = $this->doRequest($url, 'GET');
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

        $order_number = $order->getIncrementId();

        if ($config['consent_flow'] && !$this->hasConsent($order_number, $config)) {
            $this->logger->debug(sprintf('Invitation was not created for order (%s) as customer did not give a consent', $order_number));
            return false;
        }

        $date_diff = (time() - $this->date->strToTime($order->getCreatedAt()));
        if ($date_diff > $config['backlog']) {
            return false;
        }

        $request['email'] = $order->getCustomerEmail();
        $request['order'] = $order_number;
        $request['delay'] = $config['delay'];
        $request['customer_name'] = $this->invitationHelper->getCustomerName($order);
        $request['client'] = 'magento2';
        $request['platform_version'] = $this->getPlatformVersion();
        $request['plugin_version'] = $this->getPluginVersion();
        $request['noremail'] = $config['noremail'];
        $orderItems = $order->getItemsCollection([], true);
        $orderData = [
            'products' => $this->getProducts($orderItems, $config, $storeId)
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

    private function getProducts(Interceptor $orderItems, array $config, ?int $storeId): array {
        $products = [];
        foreach ($orderItems->getItems() as $item) {
            $id = $item->getProductId();
            try {
                $product = $this->productRepository->getById($id, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $this->logger->debug(sprintf('Could not find product with ID (%d)', $id));
                continue;
            }
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

    private function getProductImageUrl(Product $product): ?string {
        if (!$imageAttribute = $product->getResource()->getAttribute('image')) {
            return null;
        }
        return $imageAttribute->getFrontend()->getUrl($product);
    }

    private function getProductGtinValue(Product $product, array $config): ?string {
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

        $options = [
            CURLOPT_POSTFIELDS => $request,
        ];

        try {
            $response = $this->doRequest($url, 'POST', $options);
        } catch (\Exception $e) {
            if (!empty($config['debug'])) {
                $this->logInvitationError($request, $url, $e, $config);
            }
            return false;
        }

        if (empty($response['status'])) {
            $this->logInvitationDebugMessage($request, 'unknown', 'unknown error', $url, $config);
            return false;
        }

        $this->logInvitationDebugMessage($request, $response['status'], $response['message'], $url, $config);
        return $response;
    }

    public function sendSyncUrl(string $syncUrl, ?int $storeId): void {
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
        $options = [
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type:application/json'],
        ];

        try {
            $this->doRequest($url, 'POST', $options);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Sending sync URL to Dashboard failed with error %s', $e->getMessage()));
        }
    }

    private function hasConsent(string $order_number, array $config): bool {
        $url = sprintf(
            self::ORDER_CONSENT_URL,
            $this->extension->getDashboardDomain(),
            $config['webshop_id'],
            $config['api_key'],
            $order_number
        );

        try {
            $response_data = $this->doRequest($url, 'GET');
        } catch (\Exception $e) {
            $message = sprintf(
                'Checking consent for order %s failed: %s',
                $order_number,
                $e->getMessage()
            );
            $this->logMessage($message, $config);
            return false;
        }

        return $response_data['has_consent'] ?? false;
    }


    private function doRequest(string $url, string $method, array $options = []): ?array {
        $curl = $this->getCurl($url, $method, $options);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception(
                sprintf('(%s) %s', curl_errno($curl), curl_error($curl))
            );
        }

        return json_decode($response, true);
    }

    private function getCurl(string $url, string $method, array $options = []) {
        if (!$this->curl) {
            $this->curl = curl_init();
        } else {
            curl_reset($this->curl);
        }

        $default_options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
        ];

        if (!curl_setopt_array($this->curl, $default_options + $options)) {
            throw new \Exception(sprintf("Could not set cURL options: (%s) %s", curl_errno($this->curl), curl_error($this->curl)));
        }

        return $this->curl;
    }

    private function logInvitationError(array $request, string $url, \Exception $e, array $config): void {
        $message = $this->extension->getName() . ' - Invitation #' . $request['order'] . ' ';
        $message .= '(Error: ' . $e . ', Request: ' . $url . ' Data: ' . json_encode($request) . ')';
        $this->logMessage($message, $config);
    }

    private function logInvitationDebugMessage(array $request, string $status, string $message, string $url, array $config): void {
        $debug_message = $this->extension->getName() . ' - Invitation #' . $request['order'] . ' ';
        $debug_message .= '(Status: ' . $status . ', Msg: ' . $message . ', ';
        $debug_message .= 'Url: ' . $url . ', Data: ' . json_encode($request) . ')';
        $this->logMessage($debug_message, $config);
    }

    private function logMessage(string $message, array $config): void {
        if (empty($config['debug'])) {
            return;
        }
        $this->logger->debug($message);
    }

    private function getPluginVersion(): ?string {
        return $this->moduleList->getOne($this->extension->getModuleCode())['setup_version'] ?? null;
    }

    private function getPlatformVersion(): ?string {
        return $this->productMetadata->getVersion();
    }
}

<?php

namespace Valued\Magento2\Block;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Valued\Magento2\Helper\Invitation as InvitationHelper;
use Valued\Magento2\Setup\ExtensionBase;

class OrderConsentData extends Template implements BlockInterface {

    private $extension;
    private $orderFactory;

    private $checkoutSession;

    private $configs;

    public function __construct(
        Context $context,
        InvitationHelper $invitationHelper,
        StoreManagerInterface $storeManager,
        ExtensionBase $extension,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        array $data = []
    ) {
        $this->invitationHelper = $invitationHelper;
        $this->storeManager = $storeManager;
        $this->extension = $extension;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->configs = $invitationHelper->getConfigData($this->storeManager->getStore()->getId());
        parent::__construct($context, $data);
    }

    public function getConsentData(): ?string {
        $store_id = $this->storeManager->getStore()->getId();
        $config = $this->invitationHelper->getConfigData($store_id);

        if (!$config) {
            return null;
        }

        $webshop_id = trim($config['webshop_id']);

        if (!$webshop_id) {
            return null;
        }

        $order = $this->getOrder();
        $consent_data = [
            'webshopId' => $webshop_id,
            'orderNumber' => $order->getIncrementId(),
            'email' => $order->getCustomerEmail(),
            'firstName' => $order->getBillingAddress()->getFirstname(),
            'inviteDelay' => $config['delay'],
        ];

        return json_encode($consent_data);
    }


    private function getOrder(): Order {
        return $this->orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId());
    }

    public function consentFlowEnabled(): bool {
        return $this->configs['consent_flow'] ?? false;
    }

    public function getSystemKey(): string {
        return $this->extension->getSlug();
    }
}
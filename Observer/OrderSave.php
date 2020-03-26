<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebwinkelKeur\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use WebwinkelKeur\Magento2\Model\Api as ApiModel;

class OrderSave implements ObserverInterface {
    /**
     * @var ApiModel
     */
    private $apiModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderSave constructor.
     *
     * @param ApiModel        $apiModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiModel $apiModel,
        LoggerInterface $logger
    ) {
        $this->apiModel = $apiModel;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer) {
        try {
            $order = $observer->getEvent()->getOrder();
            $this->apiModel->sendInvitation($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->logger->debug('exception');
        }
    }
}

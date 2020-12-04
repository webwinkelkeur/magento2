<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Valued\Magento2\Controller\Adminhtml\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Valued\Magento2\Helper\Reviews as ReviewsHelper;
use Valued\Magento2\Model\Api as ApiModel;
use Valued\Magento2\Setup\ExtensionBase;

class Import extends Action {
    private $extension;

    private $resultJsonFactory;

    private $apiModel;

    private $request;

    private $reviewHelper;

    private $cacheTypeList;

    public function __construct(
        Context $context,
        ApiModel $apiModel,
        JsonFactory $resultJsonFactory,
        ReviewsHelper $reviewHelper,
        TypeListInterface $cacheTypeList,
        ExtensionBase $extension
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->apiModel = $apiModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $context->getRequest();
        $this->cacheTypeList = $cacheTypeList;
        $this->extension = $extension;
        parent::__construct($context);
    }

    public function execute() {
        $msg = [];
        $imports = $this->apiModel->getReviews('manual');

        if (!empty($imports)) {
            foreach ($imports as $key => $data) {
                if ($data['status'] == 'success') {
                    $returnMsg = __(
                        '%1: Score %2/%3 with %4 reviews',
                        $data['name'],
                        $data['score'],
                        $data['score_max'],
                        $data['total_reviews']
                    );
                    $msg[$key] = '<span class="webwinkelkeur-success-import">' . $returnMsg . '</span>';
                }
                if ($data['status'] == 'error') {
                    $returnMsg = __('Webshop ID: %1<br> %2', $key, $data['msg']);
                    $msg[$key] = '<span class="webwinkelkeur-error-import">' . $returnMsg . '</span>';
                }
            }
        } else {
            $returnMsg = __('Empty result');
            $msg[] = '<span class="webwinkelkeur-error-import">' . $returnMsg . '</span>';
        }

        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');
        $displayMsg = '';
        if ($storeId || $websiteId) {
            $connectorData = $this->reviewHelper->getConnectorData($storeId, $websiteId);
            if (!empty($connectorData['webshop_id'])) {
                if (!empty($msg[$connectorData['webshop_id']])) {
                    $displayMsg = $msg[$connectorData['webshop_id']];
                }
            } else {
                $returnMsg = __('No updates found for this storeview');
                $displayMsg = '<span class="webwinkelkeur-error-import">' . $returnMsg . '</span>';
            }
        } else {
            $displayMsg = implode($msg);
        }

        $this->cacheTypeList->cleanType('config');

        $result = $this->resultJsonFactory->create();

        return $result->setData(['success' => true, 'msg' => $displayMsg]);
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed($this->extension->getModuleCode() . '::config');
    }
}

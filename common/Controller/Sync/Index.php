<?php

namespace Valued\Magento2\Controller\Sync;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Valued\Magento2\Exceptions\BadRequestSyncException;
use Valued\Magento2\Exceptions\MethodNotAllowed;
use Valued\Magento2\Exceptions\ProductReviewSyncException;
use Valued\Magento2\Exceptions\UnauthorizedException;
use Valued\Magento2\Model\ProductReview as ProductReviewModel;

class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface, CsrfAwareActionInterface {
    /** @var ProductReviewModel */
    private $productReviewModel;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ProductReviewModel $productReviewModel
    ) {
        $this->productReviewModel = $productReviewModel;
        parent::__construct($context);
    }

    public function execute(): Response {
        $response = new Response();

        try {
            $reviewId = $this->syncProductReview();
        } catch (ProductReviewSyncException $e) {
            $response->setBody($e->getMessage())
                ->setStatusCode($e->getHttpResponseCode())
                ->setHeader('Content-Type', 'text/plain');
            return $response;
        }

        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode(['review_id' => $reviewId], JSON_PARTIAL_OUTPUT_ON_ERROR));
        return $response;
    }

    public function syncProductReview(): ?int {
        if (!$this->getRequest()->isPost()) {
            throw new MethodNotAllowed();
        }

        $input = trim(file_get_contents('php://input'));
        if (!$input) {
            throw new BadRequestSyncException('Empty request data');
        }

        if (!$input = json_decode($input, true)) {
            throw new BadRequestSyncException('Invalid JSON data provided');
        }

        if (!$this->hasCredentialFields($input) || $this->credentialsEmpty($input)) {
            throw new UnauthorizedException('Missing credential fields');
        }

        return $this->productReviewModel->sync($input);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): bool {
        return true;
    }

    private function hasCredentialFields(array $input): bool {
        return isset($input['webshop_id']) && isset($input['api_key']);
    }

    private function credentialsEmpty(array $input): bool {
        return !trim($input['webshop_id']) || !trim($input['api_key']);
    }
}

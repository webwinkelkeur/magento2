<?php

namespace Valued\Magento2\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Registry;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as RatingVoteCollection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Valued\Magento2\Controller\Sync\BadRequestSyncException;
use Valued\Magento2\Controller\Sync\UnauthorizedException;
use Valued\Magento2\Helper\Invitation as InvitationHelper;

class ProductReview {

    private $inviationHelper;

    private $logger;

    private $productRepository;

    private $reviewFactory;

    private $ratingFactory;

    private $customerInterface;

    private $registry;

    private $ratingCollection;

    private $ratingVoteCollection;

    public function __construct(
        InvitationHelper $inviationHelper,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        CustomerRepositoryInterface $customerInterface,
        Registry $registry,
        RatingCollection $ratingCollection,
        RatingVoteCollection $ratingVoteCollection
    ) {
        $this->inviationHelper = $inviationHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->customerInterface = $customerInterface;
        $this->registry = $registry;
        $this->ratingCollection = $ratingCollection;
        $this->ratingVoteCollection = $ratingVoteCollection;
    }

    public function sync($requestData) {
        $productReview = $requestData['product_review'];
        $product = $this->productRepository->getById($productReview['product_id']);
        $storeId = $product->getStoreId();
        $config = $this->inviationHelper->getConfigData($storeId);
        $this->isAuthorized($config, $requestData['webshop_id'], $requestData['api_key']);

        if ($productReview['deleted']) {
            $this->registry->register('isSecureArea', true);
            $this->reviewFactory->create()->setId($productReview['id'])->delete();
            return null;
        }

        $review = $this->reviewFactory->create()
            ->setId($productReview['id'])
            ->setEntityPkValue($productReview['product_id'])
            ->setStatusId(Review::STATUS_APPROVED)
            ->setTitle($productReview['title'])
            ->setDetail($productReview['review'])
            ->setEntityId($storeId)
            ->setStores($storeId)
            ->setCustomerId($this->getCustomerId($productReview['reviewer']['email']))
            ->setNickname($productReview['reviewer']['name'])
            ->save();

        $this->saveReviewRatings($review, $productReview, $config);

        $review->aggregate();

        $this->logger->debug(sprintf('Saved product review with ID (%d)', $review->getId()));
        return $review->getId();
    }

    private function isAuthorized($config, $webshop_id, $api_key): void {
        if ($config['webshop_id'] == $webshop_id && $config['api_key'] == $api_key) {
            return;
        }
        throw new UnauthorizedException('Incorrect credentials');
    }

    private function getCustomerId($email): ?int {
        if (!$customer = $this->customerInterface->get($email, null)) {
            return null;
        }
        return $customer->getId();
    }

    private function saveReviewRatings($review, $productReview, $config): void {
        $arrRatingId = $this->getRatings($productReview['rating'], $config);
        $votes = $this->ratingVoteCollection
            ->setReviewFilter($review->getId())
            ->addOptionInfo()
            ->load()
            ->addRatingOptions();
        foreach ($arrRatingId as $ratingId => $optionId) {
            if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                $this->ratingFactory->create()
                    ->setVoteId($vote->getId())
                    ->setReviewId($review->getId())
                    ->updateOptionVote($optionId);
            } else {
                $this->ratingFactory->create()
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->addOptionVote($optionId, $review->getEntityPkValue());
            }
        }
    }

    private function getRatings($rating_value, $config) {
        if (!isset($config['rating_options'])) {
            throw new BadRequestSyncException('Rating options not selected');
        }

        $ratings = [];
        $ratingOptions = explode(',', $config['rating_options']);
        foreach ($ratingOptions as $ratingOption) {
            if (!$this->ratingFactory->create()->load($ratingOption)->toArray()) {
                continue;
            }
            $ratings[$ratingOption] = $ratingOption * 5 - (5 - $rating_value);
        }

        if (!$ratings) {
            throw new BadRequestSyncException('No valid rating option found');
        }

        return $ratings;
    }
}



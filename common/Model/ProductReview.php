<?php

namespace Valued\Magento2\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Review\Model\ResourceModel\Review as ResourceModel;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as RatingVoteCollection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Valued\Magento2\Exceptions\ForbidenException;
use Valued\Magento2\Exceptions\NotFoundException;
use Valued\Magento2\Exceptions\UnauthorizedException;
use Valued\Magento2\Exceptions\UnconfiguredAppException;
use Valued\Magento2\Helper\Invitation as InvitationHelper;

class ProductReview {
    /** @var InvitationHelper */
    private $invitationHelper;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ReviewFactory */
    private $reviewFactory;

    /** @var RatingFactory */
    private $ratingFactory;

    /** @var CustomerRepositoryInterface */
    private $customerInterface;

    /** @var Registry */
    private $registry;

    /** @var RatingVoteCollection */
    private $ratingVoteCollection;

    /** @var ResourceModel */
    private $resourceModel;

    public function __construct(
        InvitationHelper $invitationHelper,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        CustomerRepositoryInterface $customerInterface,
        Registry $registry,
        RatingVoteCollection $ratingVoteCollection,
        ResourceModel $resourceModel
    ) {
        $this->invitationHelper = $invitationHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->customerInterface = $customerInterface;
        $this->registry = $registry;
        $this->ratingVoteCollection = $ratingVoteCollection;
        $this->resourceModel = $resourceModel;
    }

    public function sync(array $requestData): ?int {
        $productReview = $requestData['product_review'];

        try {
            $product = $this->productRepository->getById($productReview['product_id']);
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(sprintf('Could not find product with ID (%d)', $productReview['product_id']));
        }

        $config = $this->invitationHelper->getConfigData($product->getStoreId());
        $this->isAuthorized($config, $requestData['webshop_id'], $requestData['api_key']);

        if (!$config['product_reviews']) {
            throw new ForbidenException('Product review sync is disabled');
        }

        if ($productReview['deleted']) {
            $this->registry->register('isSecureArea', true);
            $review = $this->reviewFactory->create()->setId($productReview['id']);
            $this->resourceModel->delete($review);
            $this->logger->debug(sprintf('Delete review with ID (%d)', $productReview['product_id']));
            return null;
        }

        ($review = $this->reviewFactory->create())
            ->setId($productReview['id'])
            ->setEntityPkValue($productReview['product_id'])
            ->setStatusId(Review::STATUS_APPROVED)
            ->setTitle($productReview['title'])
            ->setDetail($productReview['review'])
            ->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
            ->setStores($product->getStoreIds())
            ->setCustomerId($this->getCustomerId($productReview['reviewer']['email']))
            ->setNickname($productReview['reviewer']['name'])
            ->setCreatedAt($productReview['created']);

        $this->resourceModel->save($review);
        $review->setCreatedAt($productReview['created']);
        $this->resourceModel->save($review);
        $this->saveReviewRatings($review, $productReview, $config);

        $review->aggregate();

        $this->logger->debug(sprintf('Saved product review with ID (%d)', $review->getId()));
        return $review->getId();
    }

    private function isAuthorized(array $config, int $webshop_id, string $api_key): void {
        if ($config['webshop_id'] == $webshop_id && hash_equals($config['api_key'], $api_key)) {
            return;
        }
        throw new UnauthorizedException('Incorrect credentials');
    }

    private function getCustomerId(string $email): ?int {
        try {
            return $this->customerInterface->get($email, null)->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    private function saveReviewRatings(Review $review, array $productReview, array $config): void {
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

    private function getRatings(int $rating_value, array $config): array {
        if (!isset($config['rating_options'])) {
            throw new UnconfiguredAppException('Rating options not selected');
        }
        /*
         $_ratingOptions = array(
             1 => array(1 => 1,  2 => 2,  3 => 3,  4 => 4,  5 => 5),   //quality
             2 => array(1 => 6,  2 => 7,  3 => 8,  4 => 9,  5 => 10),  //value
             3 => array(1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15),  //price
             4 => array(1 => 16, 2 => 17, 3 => 18, 4 => 19, 5 => 20)   //rating
        );*/
        $_ratingOptions = [];
        $ratingOptions = explode(',', $config['rating_options']);
        foreach ($ratingOptions as $ratingOption) {
            if (!$this->ratingFactory->create()->load($ratingOption)->toArray()) {
                continue;
            }
            $_ratingOptions[$ratingOption] = $ratingOption * 5 - (5 - $rating_value);
        }

        if (!$_ratingOptions) {
            throw new NotFoundException('No valid rating option found');
        }

        return $_ratingOptions;
    }
}



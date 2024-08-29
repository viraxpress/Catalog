<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_Catalog
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

namespace ViraXpress\Catalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewSummaryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class SummaryRatings implements ArgumentInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Review
     */
    protected $review;

    /**
     * @var ReviewSummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreManagerInterface
     */
    protected $reviewCollectionFactory;

    /**
     * Constructor.
     *
     * @param ProductRepository $productRepository
     * @param Review $review
     * @param ReviewSummaryFactory $reviewSummaryFactory
     * @param StoreManagerInterface $storeManager
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        Review $review,
        ReviewSummaryFactory $reviewSummaryFactory,
        StoreManagerInterface $storeManager,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->review = $review;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->storeManager = $storeManager;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * Example method in the view model.
     *
     * @param int $itemId
     * @return string
     */
    public function getRatingSummary($itemId)
    {
        try {
            $product = $this->productRepository->getById($itemId->getEntityId());
            $storeId = $this->storeManager->getStore()->getId();

            $this->review->getEntitySummary($product, $storeId);

            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            return $ratingSummary;
        } catch (\Exception $e) {
            return "Product not found";
        }
    }
}

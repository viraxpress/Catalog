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

namespace ViraXpress\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\LayoutFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $registry;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productrepository;

    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param LayoutFactory $layoutFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productrepository
     * @param StoreManagerInterface $storemanager
     * @param CollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        Registry $registry,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productrepository,
        StoreManagerInterface $storemanager,
        CollectionFactory $reviewCollectionFactory
    ) {
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->layoutFactory = $layoutFactory;
        $this->scopeConfig = $scopeConfig;
        $this->productrepository = $productrepository;
        $this->storeManager =  $storemanager;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * Get Current Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get Current Url
     */
    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }

    /**
     * Get Sort Url
     *
     * @param string $paramName
     * @param string $paramValue
     */
    public function getSortUrl($paramName, $paramValue)
    {
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $newUrl = $this->urlBuilder->getUrl($currentUrl, ['_query' => [$paramName => $paramValue]]);
        return $newUrl;
    }

    /**
     * Retrieve page URL by defined parameters
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_fragment'] = null;
        $urlParams['_query'] = $params;

        return $this->urlBuilder->getUrl($this->urlBuilder->getCurrentUrl(), $urlParams);
    }

    /**
     * Get Product Image Url
     *
     * @param int $productId
     */
    public function getProductImageUrl($productId)
    {
        $store = $this->storeManager->getStore();
        $product = $this->productrepository->getById($productId);
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
        return $productImageUrl;
    }

    /**
     * Get Review Ratings
     *
     * @param int $productId
     */
    public function getReviewRatings($productId)
    {
        $reviewsCollection = $this->reviewCollectionFactory->create()->addFieldToSelect('*')
        ->addStoreFilter($this->storeManager->getStore()->getId())
        ->addStatusFilter(Review::STATUS_APPROVED)
        ->addEntityFilter('product', $productId)
        ->setDateOrder()
        ->addRateVotes();

        $ratings = [];
        if (count($reviewsCollection) > 0) {
            foreach ($reviewsCollection->getItems() as $review) {
                foreach ($review->getRatingVotes() as $vote) {
                    $ratings[] = $vote->getPercent();
                }
            }
        }

        if (!empty($ratings)) {
            return round(array_sum($ratings) / count($ratings)) . '%';
        }

        return null;
    }
}

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

namespace ViraXpress\Catalog\CustomerData;

use ViraXpress\Catalog\Helper\Data as ImageHelper;
use Magento\Catalog\CustomerData\SectionSourceInterface;
use Magento\Catalog\Helper\Product\Compare as CompareHelper;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class CompareProducts extends \Magento\Catalog\CustomerData\CompareProducts
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @var $scopeConfig
     */
    private $scopeConfig;

    /**
     * @var $scopeConfig
     */
    private $storeManager;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var CompareHelper
     */
    protected $helper;

    /**
     * @var ProductUrl
     */
    protected $productUrl;

    /**
     * @param CompareHelper $helper
     * @param ProductUrl $productUrl
     * @param OutputHelper $outputHelper
     * @param ScopeConfigInterface|null $scopeConfig
     * @param StoreManagerInterface|null $storeManager
     * @param ImageHelper|null $imageHelper
     */
    public function __construct(
        CompareHelper $helper,
        ProductUrl $productUrl,
        OutputHelper $outputHelper,
        ?ScopeConfigInterface $scopeConfig = null,
        ?StoreManagerInterface $storeManager = null,
        ?ImageHelper $imageHelper = null
    ) {
        parent::__construct($helper, $productUrl, $outputHelper, $scopeConfig, $storeManager);
        $this->helper = $helper;
        $this->productUrl = $productUrl;
        $this->outputHelper = $outputHelper;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->imageHelper = $imageHelper ?? ObjectManager::getInstance()->get(ImageHelper::class);
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $count = $this->helper->getItemCount();
        return [
            'count' => $count,
            'countCaption' => $count == 1 ? __('1 item') : __('%1 items', $count),
            'listUrl' => $this->helper->getListUrl(),
            'items' => $count ? $this->getItems() : [],
            'websiteId' => $this->storeManager->getWebsite()->getId()
        ];
    }

    /**
     * Get the list of compared product items
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItems()
    {
        $items = [];
        $productsScope = $this->scopeConfig->getValue(
            'catalog/recently_products/scope',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($this->helper->getItemCollection() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'product_url' => $this->productUrl->getUrl($item),
                'name' => $this->outputHelper->productAttribute($item, $item->getName(), 'name'),
                'remove_url' => $this->helper->getPostDataRemove($item),
                'productScope' => $productsScope,
                'image_url' => $this->imageHelper->getProductImageUrl($item->getId())
            ];
        }
        return $items;
    }
}

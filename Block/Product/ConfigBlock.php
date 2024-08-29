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

namespace ViraXpress\Catalog\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Helper\Image;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class ConfigBlock extends Template
{
    /**
     * LIST PER PAGE
     */
    public const LIST_PER_PAGE = 'catalog/frontend/list_per_page';

    /**
     * GRID_PER_PAGE
     */
    public const GRID_PER_PAGE = 'catalog/frontend/grid_per_page';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get Default List Per Page
     */
    public function getDefaultListPerPage()
    {
        return $this->scopeConfig->getValue(self::LIST_PER_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Default Grid Per Page
     */
    public function getDefaultGridPerPage()
    {
        return $this->scopeConfig->getValue(self::GRID_PER_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get image helper
     *
     * @return mixed
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * Get configuration value by configuration path.
     *
     * @param string $configPath The configuration path.
     * @return mixed The configuration value.
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the base URL for media files.
     *
     * @return string The base URL for media files.
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}

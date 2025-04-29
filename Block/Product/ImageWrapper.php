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

class ImageWrapper extends Template
{
    /**
     * @var Context
     */         
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * Render the inner image template
     *
     * @return string
     */
    public function getImageHtml()
    {
        return $this->getLayout()
            ->createBlock(\Magento\Framework\View\Element\Template::class)
            ->setTemplate('ViraXpress_Catalog::product/image.phtml')
            ->setData($this->getData())
            ->toHtml();
    }

    /**
     * Get the product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * Get the product image URL
     *
     * @return string
     */
    public function getProductImageUrl()
    {
        return $this->getData('product_image_url');
    }

    /**
     * Get the image width
     *
     * @return int
     */ 
    public function getImageWidth()
    {
        return $this->getData('image_width');
    }

    /**
     * Get the image height
     *
     * @return int
     */ 
    public function getImageHeight()
    {
        return $this->getData('image_height');
    }

    /**
     * Get the product image
     *
     * @return string
     */     
    public function getProductImage()
    {
        return $this->getData('product_image');
    }
}

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

namespace ViraXpress\Catalog\Block\Product\ProductList;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Session;
use Magento\Framework\Url\EncoderInterface;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\App\ObjectManager;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder = null;

    /**
     * List of available view types
     *
     * @var array
     */
    protected $_availableMode = [];

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    protected $_enableViewSwitcher = true;

    /**
     * @var bool
     */
    protected $_isExpanded = true;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $_orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction = ProductList::DEFAULT_SORT_DIRECTION;

    /**
     * Default View mode
     *
     * @var string
     */
    protected $_viewMode = null;

    /**
     * @var bool $_paramsMemorizeAllowed
     * @deprecated 103.0.1
     */
    protected $_paramsMemorizeAllowed = true;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/list/toolbar.phtml';

    /**
     * @var Config
     */
    protected $_catalogConfig;

    /**
     * @var Session
     * @deprecated 103.0.1
     */
    protected $catalogSession;

    /**
     * @var ToolbarModel
     */
    protected $toolbarModel;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var ProductList
     */
    protected $productListHelper;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var PostHelper
     */
    protected $postDataHelper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param PostHelper $postDataHelper
     * @param array $data
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param HttpContext|null $httpContext
     * @param FormKey|null $formKey
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        Config $catalogConfig,
        ToolbarModel $toolbarModel,
        EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        PostHelper $postDataHelper,
        array $data = [],
        ToolbarMemorizer $toolbarMemorizer = null,
        HttpContext $httpContext = null,
        FormKey $formKey = null
    ) {
        parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper);
        $this->toolbarMemorizer = $toolbarMemorizer ?: ObjectManager::getInstance()->get(
            ToolbarMemorizer::class
        );
        $this->httpContext = $httpContext ?: ObjectManager::getInstance()->get(
            \Magento\Framework\App\Http\Context::class
        );
        $this->formKey = $formKey ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Data\Form\FormKey::class
        );
    }

    /**
     * Set the relevance sorting for the catalog search page
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->getOrderField();

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarMemorizer->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::ORDER_PARAM_NAME, $order, $defaultOrder);
        }

        if ($this->getRequest()->getParam('product_list_order') == 'relevance' || empty($this->getRequest()->getParam('product_list_order')) && $this->getRequest()->getRouteName() == 'catalogsearch' && $this->getRequest()->getControllerName() == 'result') {
            $order = 'relevance';
        }

        if ($this->getRequest()->getParam('product_list_order') == 'relevance') {
            $order = 'relevance';
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }
}

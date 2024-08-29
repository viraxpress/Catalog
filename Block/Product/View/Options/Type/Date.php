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

namespace ViraXpress\Catalog\Block\Product\View\Options\Type;

use DateTimeZone;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product\Option\Type\Date as CatalogProductOptionTypeDate;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\Type\Date
{
    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    protected $_fillLeadingZeros = true;

    /**
     * @var CatalogProductOptionTypeDate
     */
    protected $catalogProductOptionTypeDate;

    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param CatalogProductOptionTypeDate $catalogProductOptionTypeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     * @param FilterFactory|null $filterFactory
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        CatalogProductOptionTypeDate $catalogProductOptionTypeDate,
        ScopeConfigInterface $scopeConfig,
        array $data = [],
        ?FilterFactory $filterFactory = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        $this->filterFactory = $filterFactory ?? ObjectManager::getInstance()->get(FilterFactory::class);
        parent::__construct($context, $pricingHelper, $catalogData, $catalogProductOptionTypeDate, $data, $filterFactory);
    }

    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
        return $this->getDropDownsDateHtml();
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $option = $this->getOption();
        $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());

        $yearStart = $this->catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->catalogProductOptionTypeDate->getYearEnd();

        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);
        $value = null;
        if (is_array($values)) {
            $date = $this->getInternalDateString($values);
            if ($date !== null) {
                $dateFilter = $this->filterFactory->create('date', ['format' => $escapedDateFormat]);
                $value = $dateFilter->outputFilter($date);
            } elseif (isset($values['date'])) {
                $value = $values['date'];
            }
        }

        $firstDay = (int)$this->_scopeConfig->getValue('general/locale/firstday', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $calendarData = [
            "getImage" => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            "getDateFormat" => $escapedDateFormat,
            "getValue" => $value,
            "getFirstDay" => $firstDay,
            "yearEnd" => $yearEnd,
            "yearStart" => $yearStart,
            'change_month' => 'true',
            'change_year' => 'true',
            'show_on' => 'both'
        ];
        return $calendarData;
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @param int|null $value
     * @return mixed
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        $this->setSkipJsReloadPrice(1);

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $require = $option->getIsRequire() ? ' required' : '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setId(
            'options_' . $this->getOption()->getId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require . ' datetime-'. $this->getOption()->getId()
        )->setExtraParams()->setName(
            'options[' . $option->getId() . '][' . $name . ']'
        );
        $enableViraxpress = $this->scopeConfig->getValue('viraxpress_config/general/enable_viraxpress', ScopeInterface::SCOPE_STORE);
        $extraParams = 'style="width:auto"';
        if ($enableViraxpress) {
            $extraParams .= ' @change="changeCustomizableOption(event)"';
        } else {
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($this->getOption()->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $extraParams .= $require;

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
            $value = is_array($values) ? $this->parseDate($values, $name) : null;
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }

    /**
     * Parse option value and return the requested part
     *
     * @param array $value
     * @param string $part [year, month, day, hour, minute, day_part]
     * @return string|null
     */
    private function parseDate(array $value, string $part): ?string
    {
        $result = null;
        if (!empty($value['date']) && !empty($value['date_internal'])) {
            $formatDate = explode(' ', $value['date_internal']);
            $date = explode('-', $formatDate[0]);
            $value['year'] = $date[0];
            $value['month'] = $date[1];
            $value['day'] = $date[2];
        }

        if (isset($value[$part])) {
            $result = (string) $value[$part];
        }

        return $result;
    }

    /**
     * Get internal date format of provided value
     *
     * @param array $value
     * @return string|null
     */
    private function getInternalDateString(array $value): ?string
    {
        $result = null;
        if (!empty($value['date']) && !empty($value['date_internal'])) {
            $dateTimeZone = new DateTimeZone($this->_localeDate->getConfigTimezone());
            $dateTimeObject = date_create_from_format(
                DateTime::DATETIME_PHP_FORMAT,
                $value['date_internal'],
                $dateTimeZone
            );
            if ($dateTimeObject !== false) {
                $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
            }
        } elseif (!empty($value['day']) && !empty($value['month']) && !empty($value['year'])) {
            $dateTimeObject = $this->_localeDate->date();
            $dateTimeObject->setDate((int) $value['year'], (int) $value['month'], (int) $value['day']);
            $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
        }
        return $result;
    }
}

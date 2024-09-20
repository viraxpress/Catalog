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

declare(strict_types=1);

namespace ViraXpress\Catalog\Block\Product\View\Options\Type\Select;

use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Represent needed logic for dropdown and multi-select
 */
class Multiple extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple
{

    /**
     * @inheritdoc
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $option = $this->getOption();
        $optionType = $option->getType();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
        $require = $option->getIsRequire() ? ' required' : '';
        $scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $enableViraxpress = $scopeConfig->getValue('viraxpress_config/general/enable_viraxpress', ScopeInterface::SCOPE_STORE);
        $extraParams = '';
        /** @var Select $select */
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => 'select_' . $option->getId(),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );
        $select = $this->insertSelectOption($select, $option);
        $select = $this->processSelectOption($select, $option);
        if ($optionType === Option::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }
        $extraParams .= $require;
        if ($enableViraxpress) {
            $extraParams .= ' x-on:change="changeCustomizableOption(event)"';
        } else {
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);
        if ($configValue) {
            $select->setValue($configValue);
        }
        return $select->getHtml();
    }

    /**
     * Returns select with inserted option give as a parameter
     *
     * @param Select $select
     * @param Option $option
     * @return Select
     */
    private function insertSelectOption(Select $select, Option $option): Select
    {
        $require = $option->getIsRequire() ? ' required' : '';
        if ($option->getType() === Option::OPTION_TYPE_DROP_DOWN) {
            $select->setName('options[' . $option->getId() . ']')->addOption('', __('-- Please Select --'));
        } else {
            $select->setName('options[' . $option->getId() . '][]');
            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        }

        return $select;
    }

    /**
     * Returns select with formated option prices
     *
     * @param Select $select
     * @param Option $option
     * @return Select
     */
    private function processSelectOption(Select $select, Option $option): Select
    {
        $store = $this->getProduct()->getStore();
        foreach ($option->getValues() as $_value) {
            $isPercentPriceType = $_value->getPriceType() === 'percent';
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $isPercentPriceType,
                    'pricing_value' => $_value->getPrice($isPercentPriceType)
                ],
                false
            );
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                [
                    'price' => $this->pricingHelper->currencyByStore(
                        $_value->getPrice(true),
                        $store,
                        false
                    )
                ]
            );
        }

        return $select;
    }
}

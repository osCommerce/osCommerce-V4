<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

use backend\services\ConfigurationService;
use backend\services\GroupsService;

class Points {

    public static function get_bonus_points_price($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select bonus_points_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select bonus_points_price from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . ( int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);
        if ($product['bonus_points_price'] == '' && $default != '') {
            $product['bonus_points_price'] = $default;
        }
        return $product['bonus_points_price'];
    }

    public static function get_bonus_points_cost($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select bonus_points_cost from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select bonus_points_cost from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);
        if ($product['bonus_points_cost'] == '' && $default != '') {
            $product['bonus_points_cost'] = $default;
        }
        return $product['bonus_points_cost'];
    }

    /**
     * @param int $groupId
     * @return bool|float
     */
    public static function getCurrencyCoefficient(int $groupId /*= 0/**/)
    {
        static $coefficient = null;
        if (is_array($coefficient)) {
            return $coefficient[$groupId] ?? $coefficient[0];
        }
        $coefficient = [];
        $coefficient[0] = self::getCurrencyCoefficientApp();
        try {
            /** @var GroupsService $groupsService */
            $groupsService = \Yii::createObject(GroupsService::class);
            $groupsCoefficients = $groupsService->getBonusPointsCurrencyRates($coefficient[0], $coefficient[0] === false || !\common\helpers\Extensions::isCustomerGroupsAllowed());
            foreach ($groupsCoefficients as $key => $groupsCoefficient) {
                $coefficient[$key] = $groupsCoefficient;
            }
        } catch (\Exception $e) {
            \Yii::error('Cannot get GroupsService');
        }
        return $coefficient[$groupId] ?? $coefficient[0];
    }

    public static function getCurrencyCoefficientNoCache(?int $groupId = null, ?int $platformId = null)
    {
        $coefficient = false;
        try {
            /** @var ConfigurationService $configurationService */
            $configurationService = \Yii::createObject(ConfigurationService::class);
            /** @var GroupsService $groupsService */
            $groupsService = \Yii::createObject(GroupsService::class);
            $coefficient = !\common\helpers\Acl::checkExtensionAllowed('BonusActions') ? 0 :
                    (float)$configurationService->findValue('BONUS_POINT_CURRENCY_COEFFICIENT', $platformId);
            if ($coefficient <= 0) {
                return false;
            }
            if ($groupId === null) {
                return $coefficient;
            }
            $isGroupEnabled = \common\helpers\Extensions::isCustomerGroupsAllowed();
            if ($isGroupEnabled && $groupId === 0) {
                $groupId = (int)$configurationService->findValue('DEFAULT_USER_GROUP', $platformId);
            }
            return  $groupsService->getBonusPointsGroupCurrencyRate($groupId, $coefficient, !($isGroupEnabled === 'True'));
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
        return $coefficient;
    }

    /**
     * @return bool|float
     */
    public static function getCurrencyCoefficientApp()
    {
        static $coefficient = null;
        if ($coefficient !== null) {
            return $coefficient;
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BonusActions')) {
            $coefficient = $ext::getCurrencyCoeff();
        } else {
            $coefficient = false;
        }
        return $coefficient;
    }

    /** @return bool */
    public static function canCustomerTransferToCreditAmount()
    {
        static $flag = null;
        if ($flag !== null) {
            return $flag;
        }
        $flag = false;
        if (\common\helpers\Acl::checkExtensionAllowed('BonusActions') && defined('BONUS_POINT_TO_CREDIT_AMOUNT')) {
            $flag = mb_strtolower(BONUS_POINT_TO_CREDIT_AMOUNT) === 'true';
        }
        return $flag;
    }

    /**
     * @param float $productPrice
     * @param float $defaultBonusPointPrice
     * @param int $groupId
     * @return float
     */
    public static function getBonusPointsPrice(float $productPrice, float $defaultBonusPointPrice, int $groupId /*= 0**/): float
    {
        $coefficient = self::getCurrencyCoefficient($groupId);
        return $coefficient ? ceil(($productPrice) / $coefficient) : $defaultBonusPointPrice;
    }

    /**
     * @param float $bonusPointPrice
     * @param int $groupId
     * @param \common\classes\Currencies|null $currencies
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public static function getBonusPointsPriceInCurrency(float $bonusPointPrice, int $groupId /*= 0**/, ?\common\classes\Currencies $currencies = null)
    {
        if ($currencies === null) {
            /** @var \common\classes\Currencies $currencies */
            $currencies = \Yii::$container->get('currencies');
        }
        $coefficient = self::getCurrencyCoefficient($groupId);
        return $coefficient ? $currencies->format_clear($bonusPointPrice * $coefficient) : false;
    }

    /**
     * @param float $bonusPointPrice
     * @param int $groupId
     * @param \common\classes\Currencies|null $currencies
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public static function getBonusPointsPriceInCurrencyFormatted(
        float $bonusPointPrice,
        int $groupId /*= 0/**/,
        ?\common\classes\Currencies $currencies = null
    ): string
    {
        if ($currencies === null) {
            /** @var \common\classes\Currencies $currencies */
            $currencies = \Yii::$container->get('currencies');
        }
        $cost = self::getBonusPointsPriceInCurrency(floor($bonusPointPrice), $groupId, $currencies);
        if ($cost === false) {
            return '';
        }
        return $currencies->format($cost, false);
    }

    /**
     * @param float $productPrice
     * @param int $groupId
     * @param float $defaultBonusPointPriceCost
     * @return float
     */
    public static function getBonusPointsPriceCost(float $productPrice, int $groupId, float $defaultBonusPointPriceCost): float
    {
        return self::getCurrencyCoefficient($groupId) ? $productPrice : $defaultBonusPointPriceCost;
    }

    /**
     * @param float $bonusPoints
     * @param int $groupId
     * @return float|int
     */
    public static function getBonusPointsRedeemCost(float $bonusPoints, int $groupId /*= 0/**/)
    {
        $coefficient = self::getCurrencyCoefficient($groupId);
        return $coefficient ? $bonusPoints * $coefficient : $bonusPoints;
    }

    /**
     * @param float $price
     * @return bool
     */
    public static function allowApplyBonusPoints(float $price): bool
    {
        $threshold = defined('MODULE_ORDER_TOTAL_BONUS_POINTS_MINIMUM_CART') ? (float)MODULE_ORDER_TOTAL_BONUS_POINTS_MINIMUM_CART : 0.00;
        return $threshold <= $price;
    }

    public static function getOptions($value)
    {
        return [
            'OFF' => [
                'value' => '',
                'disableInput' => 1,
                'changeInput' => 'direct'
            ],
            'ON' => [
                'value' => empty($value) ? '0.1' : $value ,
                'disableInput' => 0,
                'changeInput' => 'lazy',
            ],
        ];
    }
}

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

namespace backend\models;


use frontend\design\Info;

class ProductNameDecorator
{
    protected $config = [];

    private function __construct()
    {

    }

    /**
     * @return null|self
     */
    public static function instance()
    {
        static $obj = null;
        if ( !is_object($obj) ){
            $obj = new static();
            if ( defined('BACKEND_PRODUCT_NAME_FORMAT') ) {
                $obj->config = preg_split('/,\s?/', strval(BACKEND_PRODUCT_NAME_FORMAT), -1, PREG_SPLIT_NO_EMPTY);
            }
        }
        return $obj;
    }

    public static function getInternalName($products_id, $language_id, $platformId=null)
    {
        if ( empty($platformId) ) $platformId = \common\classes\platform::defaultId();
        $descriptionPlatformId = intval(\Yii::$app->get('platform')->getConfig($platformId)->getPlatformToDescription());
        if ( empty($language_id) ) $language_id = \common\classes\language::get_id(\Yii::$app->get('platform')->getConfig($platformId)->getDefaultLanguage());

        $products_internal_name_query = tep_db_query(
            "select if(length(pd1.products_internal_name), pd1.products_internal_name, pd.products_internal_name) as products_internal_name, ".
            " if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name ".
            "from " . TABLE_PRODUCTS_DESCRIPTION . " pd ".
            "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = pd.products_id and pd1.language_id='" . (int)$language_id . "' and pd1.platform_id = '" . (int)$descriptionPlatformId . "' ".
            "where pd.products_id = '" . (int)$products_id . "' and pd.platform_id = '".(int)$platformId."' and pd.language_id = '" . (int)$language_id . "'"
        );
        if ( tep_db_num_rows($products_internal_name_query)>0 ){
            $products_internal_name = tep_db_fetch_array($products_internal_name_query);
            if (!empty($products_internal_name['products_internal_name'])){
                return $products_internal_name['products_internal_name'];
            }
            //return $products_internal_name['products_name'];
        }
        return false;
    }

    public function useInternalNameForListing()
    {
        return in_array('Listing', $this->config);
    }

    public function useInternalNameForOrder()
    {
        return in_array('Orders', $this->config);
    }

    public function useInternalNameForPackingSlip()
    {
        return in_array('PackingSlip', $this->config);
    }

    public function useInternalNameForInvoice()
    {
        return in_array('Invoice', $this->config);
    }

    public function getUpdatedOrderProducts($products, $languageId, $platformId)
    {
        foreach ($products as $idx=>$orderProduct){
            $products[$idx]['_name'] = $products[$idx]['name'];
            $internal_name = static::getInternalName($orderProduct['id'], $languageId, $platformId);
            if ( !empty($internal_name) ) {
                $products[$idx]['name'] = $internal_name;
            }
        }
        return $products;
    }

    public function listingQueryExpression($mainTableAlias='pd', $extraTableAlias='pd1')
    {
        if ( Info::isTotallyAdmin() && $this->useInternalNameForListing() ){
            $internalColumn = "IF(LENGTH({$extraTableAlias}.products_internal_name), {$extraTableAlias}.products_internal_name, {$mainTableAlias}.products_internal_name)";
            $mainColumn = "IF(LENGTH({$extraTableAlias}.products_name), {$extraTableAlias}.products_name, {$mainTableAlias}.products_name)";
            if ( empty($extraTableAlias) ) {
                if ( !empty($mainTableAlias) ) $mainTableAlias = $mainTableAlias.'.';
                $internalColumn = $mainTableAlias.'products_internal_name';
                $mainColumn = $mainTableAlias.'products_name';
            }
            return "IF(LENGTH({$internalColumn}), {$internalColumn}, {$mainColumn})";
        }else{
            $mainColumn = "IF(LENGTH({$extraTableAlias}.products_name), {$extraTableAlias}.products_name, {$mainTableAlias}.products_name)";
            if ( empty($extraTableAlias) ) {
                if ( !empty($mainTableAlias) ) $mainTableAlias = $mainTableAlias.'.';
                $mainColumn = $mainTableAlias.'products_name';
            }
            return $mainColumn;
        }
    }

    public static function descriptionExpr($alias = 'pd')
    {
        $alias = is_null($alias) ? \common\models\ProductsDescription::tableName() : $alias;
        return new \yii\db\Expression(self::instance()->listingQueryExpression($alias, ''));
    }

}
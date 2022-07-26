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

class Api
{
    static public function generateApiKey()
    {
        $__server_part = tep_db_fetch_array(tep_db_query(
            "SELECT UUID() AS server_part"
        ));
        return strtolower(str_replace('-','',$__server_part['server_part']).\common\helpers\Password::create_random_value(16));
    }

    static public function getDepartmentServerKeyValue($departmentId, $keyName)
    {
        $value = null;
        $get_kv_r = tep_db_query(
            "SELECT key_value ".
            "FROM " . TABLE_EP_HOLBI_SOAP_SERVER_KV_STORAGE . " ".
            "WHERE departments_id='" . (int)$departmentId . "' ".
            " AND key_name='".tep_db_input($keyName)."'"
        );
        if ( tep_db_num_rows($get_kv_r)>0 ) {
            $get_kv = tep_db_fetch_array($get_kv_r);
            $value = $get_kv['key_value'];
            tep_db_free_result($get_kv_r);
        }
        return $value;
    }

    static public function setDepartmentServerKeyValue($departmentId, $keyName, $value)
    {
        tep_db_query(
            "INSERT INTO ep_holbi_soap_server_kv_storage (departments_id, key_name, key_value) ".
            "VALUES ('".(int)$departmentId."', '".tep_db_input($keyName)."','".tep_db_input($value)."') ".
            "ON DUPLICATE KEY UPDATE key_value='".tep_db_input($value)."'"
        );
    }

    static public function updateCustomerModifyTime($customerId=null)
    {
        tep_db_query(
            "UPDATE ".TABLE_CUSTOMERS." c ".
            "  INNER JOIN ".TABLE_ADDRESS_BOOK." ab ON ab.customers_id=c.customers_id ".
            "  SET c._api_time_modified = GREATEST(c._api_time_modified,IFNULL(ab._api_time_modified,0)) ".
            "WHERE 1 ".(is_numeric($customerId)?" AND c.customers_id='".(int)$customerId."' ":'')
        );
    }

    static public function allowApiCreateCategory($departmentId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE departments_id='".(int)$departmentId."' AND api_categories_allow_create !=0 "
        ));
        $allow = $check['c']>0;
        return $allow;
    }

    static public function allowApiUpdateCategory($departmentId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE departments_id='".(int)$departmentId."' AND api_categories_allow_update !=0 "
        ));
        $allow = $check['c']>0;
        return $allow;
    }

    static public function allowApiCreateProduct($departmentId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE departments_id='".(int)$departmentId."' AND api_products_allow_create !=0 "
        ));
        $allow = $check['c']>0;
        return $allow;
    }

    static public function allowApiUpdateProduct($departmentId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE departments_id='".(int)$departmentId."' AND api_products_allow_update !=0 "
        ));
        $allow = $check['c']>0;
        return $allow;
    }

    static public function allowApiRemoveProduct($departmentId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE departments_id='".(int)$departmentId."' AND api_products_allow_remove_owned !=0 "
        ));
        $allow = $check['c']>0;
        return $allow;
    }

    static public function productFlags()
    {
        return [
            [
                'label' => 'Name and description',
                'server' => 'description_server',
                'server_own' => 'description_server_own',
                'client' => 'description_client',
            ],
            [
                'label' => 'SEO',
                'server' => 'seo_server',
                'server_own' => 'seo_server_own',
                'client' => 'seo_client',
            ],
            [
                'label' => 'Prices',
                'server' => 'prices_server',
                'server_disable' => true,
                'server_own' => 'prices_server_own',
                'client' => 'prices_client',
            ],
            [
                'label' => 'Stock',
                'server' => 'stock_server',
                'server_own' => 'stock_server_own',
                'client' => 'stock_client',
            ],
            [
                'label' => 'Attributes and inventory',
                'server' => 'attr_server',
                'server_own' => 'attr_server_own',
                'client' => 'attr_client',
            ],
            [
                'label' => 'Product identifiers',
                'server' => 'identifiers_server',
                'server_own' => 'identifiers_server_own',
                'client' => 'identifiers_client',
            ],
            [
                'label' => 'Images',
                'server' => 'images_server',
                'server_own' => 'images_server_own',
                'client' => 'images_client',
            ],
            [
                'label' => 'Size and Dimensions',
                'server' => 'dimensions_server',
                'server_own' => 'dimensions_server_own',
                'client' => 'dimensions_client',
            ],
            [
                'label' => 'Properties',
                'server' => 'properties_server',
                'server_own' => 'properties_server_own',
                'client' => 'properties_client',
            ],
        ];
    }

    public static function applyDepartmentOutgoingPriceFormula($price, $disableCalculate=null)
    {
        $params = \Yii::$app->get('department')->getApiOutgoingPriceParams();
        $params['price'] = $price;
        $formula = \Yii::$app->get('department')->getApiOutgoingPriceFormula();

        $productFormula = \common\classes\ApiDepartment::get()->getCurrentResponseProductPriceFormulaData();

        if ( is_array($productFormula) && is_array($productFormula['formula']) ) {
            $formula = $productFormula['formula'];
            $params['discount'] = $productFormula['discount'];
            $params['surcharge'] = $productFormula['surcharge'];
            $params['margin'] = $productFormula['margin'];;
        }

        $result = \common\helpers\PriceFormula::apply($formula, $params);

        if ( is_numeric($result) ) {
            return $result;
        }
        return $price;
    }

}
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

namespace common\classes;


class department
{
    protected $active_department_id = 0;

    /**
     * @return int
     */
    public function getActiveDepartmentId()
    {
        return $this->active_department_id;
    }

    /**
     * @param int $active_department_id
     */
    public function setActiveDepartmentId($active_department_id)
    {
        $this->active_department_id = $active_department_id;
    }

    public static function getList($only_active=true)
    {
        $departmentList = [];
        $departments_query = tep_db_query(
            "SELECT * ".
            "FROM " . TABLE_DEPARTMENTS . " ".
            "WHERE 1 ".
            ($only_active?" AND departments_status > 0 ":'').
            "ORDER BY departments_status desc, departments_sort_order, departments_id"
        );
        while ($department = tep_db_fetch_array($departments_query)) {
            $department['id'] = $department['departments_id'];
            $department['text'] = $department['departments_store_name'];
            $departmentList[] = $department;
        }
        return $departmentList;
    }

    public static function getCatalogAssignList()
    {
        return self::getList(false);
    }

    public function hasCategory($categoryId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS assigned ".
            "FROM ".TABLE_CATEGORIES." c ".
            " INNER JOIN ".TABLE_DEPARTMENTS_CATEGORIES." dc ON c.categories_id=dc.categories_id AND dc.departments_id='".(int)$this->active_department_id."' ".
            "WHERE c.categories_id='".(int)$categoryId."' ".
            ""
        ));
        return $check['assigned']>0;
    }

    public function hasProduct($productId)
    {
        $check = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS assigned ".
            "FROM ".TABLE_PRODUCTS." p ".
            " INNER JOIN ".TABLE_DEPARTMENTS_PRODUCTS." dp ON dp.products_id=p.products_id AND dp.departments_id='".(int)$this->active_department_id."' ".
            " INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p2c.products_id=p.products_id ".
            " INNER JOIN ".TABLE_DEPARTMENTS_CATEGORIES." dc ON p2c.categories_id=dc.categories_id AND dc.departments_id='".(int)$this->active_department_id."' ".
            "WHERE p.products_id='".(int)$productId."' ".
            ""
        ));
        return $check['assigned']>0;
    }

    public static function getName($departmentId)
    {
        static $map = false;
        if ( !is_array($map) ) {
            $map = [];
            foreach( self::getList(false) as $departmentVariant){
                $map[ $departmentVariant['id'] ] = $departmentVariant['text'];
            }
        }
        return isset($map[$departmentId])?$map[$departmentId]:'--';
    }

    public function getPlatformName($externalPlatformId)
    {
        $name = '--';
        $get_name_r = tep_db_query(
            "SELECT platform_name ".
            "FROM ".TABLE_DEPARTMENTS_EXTERNAL_PLATFORMS." ".
            "WHERE departments_id='".(int)$this->active_department_id."' AND platform_id='".(int)$externalPlatformId."' "
        );
        if ( tep_db_num_rows($get_name_r)>0 ) {
            $_name = tep_db_fetch_array($get_name_r);
            $name = $_name['platform_name'];
        }
        return $name;
    }

    public function updatePlatformName($externalPlatformId, $externalPlatformName)
    {
        tep_db_query(
            "INSERT INTO ".TABLE_DEPARTMENTS_EXTERNAL_PLATFORMS." (departments_id, platform_id, platform_name) ".
            "VALUES ('".(int)$this->active_department_id."', '".(int)$externalPlatformId."', '".tep_db_input($externalPlatformName)."') ".
            "ON DUPLICATE KEY UPDATE platform_name='".tep_db_input($externalPlatformName)."' "
        );
    }

    public function getDepartmentSiteAdminBaseUrl()
    {
        $admin_url = '';

        $get_name_r = tep_db_query(
            "SELECT departments_http_server, departments_https_server, departments_enable_ssl, departments_http_catalog, departments_https_catalog ".
            "FROM ".TABLE_DEPARTMENTS." ".
            "WHERE departments_id='".(int)$this->active_department_id."'"
        );
        if ( tep_db_num_rows($get_name_r)>0 ) {
            $_name = tep_db_fetch_array($get_name_r);
            $admin_url = $_name['departments_http_server'].$_name['departments_http_catalog'].'/admin/';
            $admin_url = 'http://'.str_replace('//','/',$admin_url);
        }
        return $admin_url;

    }

    public function getApiOutgoingPriceFormula()
    {
        static $fetched = [];

        if ( !isset($fetched[(int)$this->active_department_id]) ) {
            $fetched[(int)$this->active_department_id] = false;
            $get_formula_r = tep_db_query(
                "SELECT api_outgoing_price_formula " .
                "FROM " . TABLE_DEPARTMENTS . " " .
                "WHERE departments_id='" . (int)$this->active_department_id . "' "
            );
            if ( tep_db_num_rows($get_formula_r)>0 ) {
                $get_formula = tep_db_fetch_array($get_formula_r);
                $fetched[(int)$this->active_department_id] = json_decode($get_formula['api_outgoing_price_formula'],true);
            }
        }

        return $fetched[(int)$this->active_department_id];
    }

    public function getApiOutgoingPriceParams()
    {
        static $fetched = [];

        if ( !isset($fetched[(int)$this->active_department_id]) ) {
            $fetched[(int)$this->active_department_id] = [];
            $get_formula_params_r = tep_db_query(
                "SELECT ".
                " api_outgoing_price_discount AS discount, ".
                " api_outgoing_price_surcharge AS surcharge, ".
                " api_outgoing_price_margin AS margin ".
               "FROM " . TABLE_DEPARTMENTS . " " .
                "WHERE departments_id='" . (int)$this->active_department_id . "' "
            );
            if ( tep_db_num_rows($get_formula_params_r)>0 ) {
                $fetched[(int)$this->active_department_id] = tep_db_fetch_array($get_formula_params_r);
            }
        }

        return $fetched[(int)$this->active_department_id];
    }

}
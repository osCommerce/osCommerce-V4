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


class ApiDepartment
{
    protected $currentResponseProductId = 0;

    protected function __construct()
    {

    }

    public static function get()
    {
        static $instance;
        if ( !is_object($instance) ) $instance = new self();
        return $instance;
    }

    /**
     * @return int
     */
    public function getCurrentResponseProductId()
    {
        return $this->currentResponseProductId;
    }

    /**
     * @param int $currentResponseProductId
     */
    public function setCurrentResponseProductId($currentResponseProductId)
    {
        $this->currentResponseProductId = $currentResponseProductId;
    }

    public static function getCategoryFormulaData($departmentId, $categoryId, $onlyParents=false)
    {
        $formula_data = false;
        $get_category_formula_with_parentage_r = tep_db_query(
            "SELECT dpf.formula, dpf.discount, dpf.surcharge, dpf.margin ".
            "FROM ".TABLE_CATEGORIES." node, ".TABLE_CATEGORIES." parent ".
            " LEFT JOIN departments_categories_price_formula dpf ON dpf.departments_id='".(int)$departmentId."' AND parent.categories_id=dpf.categories_id ".
            "WHERE node.categories_left BETWEEN parent.categories_left AND parent.categories_right ".
            "  AND node.categories_id = '".(int)$categoryId."' ".
            ($onlyParents?" AND parent.categories_id != '".(int)$categoryId."' ":'').
            "  AND dpf.categories_id IS NOT NULL ".
            "ORDER BY parent.categories_right ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($get_category_formula_with_parentage_r)>0 ) {
            $formula_data = tep_db_fetch_array($get_category_formula_with_parentage_r);
        }
        return $formula_data;
    }

    public function getCurrentResponseProductPriceFormulaData()
    {
        $_formula = false;
        static $_last_call = array();
        if ( $this->currentResponseProductId && \Yii::$app->get('department')->getActiveDepartmentId()>0 ) {
            $_key = intval($this->currentResponseProductId) .'&'. intval(\Yii::$app->get('department')->getActiveDepartmentId());

            if ( !isset($_last_call[$_key]) ) {
                $get_formula_r = tep_db_query(
                    "SELECT dpf.formula, dpf.discount, dpf.surcharge, dpf.margin ".
                    "FROM ".TABLE_CATEGORIES." node, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_CATEGORIES." parent ".
                    " LEFT JOIN departments_categories_price_formula dpf ON dpf.departments_id='".intval(\Yii::$app->get('department')->getActiveDepartmentId())."' AND parent.categories_id=dpf.categories_id ".
                    "WHERE node.categories_left BETWEEN parent.categories_left AND parent.categories_right ".
                    "  AND node.categories_id = p2c.categories_id ".
                    "  AND p2c.products_id='".intval($this->currentResponseProductId)."' ".
                    "  AND dpf.categories_id IS NOT NULL ".
                    "ORDER BY parent.categories_right ".
                    "LIMIT 1"
                );
                /*
                $get_formula_r = tep_db_query(
                    "SELECT dpf.* " .
                    "FROM departments_categories_price_formula dpf " .
                    " INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.categories_id=dpf.categories_id " .
                    "WHERE dpf.departments_id='" . intval(\Yii::$app->get('department')->getActiveDepartmentId()) . "' AND p2c.products_id='" . intval($this->currentResponseProductId) . "' " .
                    "LIMIT 1"
                );
                */
                if (tep_db_num_rows($get_formula_r) > 0) {
                    $_formula = tep_db_fetch_array($get_formula_r);
                    if (!empty($_formula['formula'])) {
                        $_formula['formula'] = json_decode($_formula['formula'], true);
                        if (!is_array($_formula['formula'])) $_formula = false;
                    }
                }
                $_last_call = array($_key => $_formula);
            }
            $_formula = $_last_call[$_key];

        }
        return $_formula;
    }

}
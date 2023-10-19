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

namespace backend\models\ProductEdit;


class ViewAttributes
{
    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    protected $isParentProduct = false;

    public function __construct($productInfo, $isParentProduct=false)
    {
        $this->productInfoRef = $productInfo;
        $this->isParentProduct = $isParentProduct;
    }

    public function populateView($view)
    {
        $currencies = \Yii::$container->get('currencies');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $pInfo = $this->productInfoRef;

        $selectedAttributes = [];
        if ( !empty($pInfo->options_templates_id) ){
            $query = tep_db_query(
                "select '' AS products_attributes_id, po.products_options_id, po.products_options_name, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_discount_price, pa.products_options_sort_order, pa.product_attributes_one_time, pa.products_attributes_weight, pa.products_attributes_weight_prefix, pa.products_attributes_units, pa.products_attributes_units_price, " .
                " pa.options_templates_attributes_id, ".
                " pa.default_option_value, " .
                " pa.products_attributes_filename, pa.products_attributes_maxdays, pa.products_attributes_maxcount /*,count(distinct po.products_options_id) as options_count*/ " .
                "from " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                /* "left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on pa.attributes_id=pad.attributes_id " .*/
                "where pa.options_templates_id = '" . $pInfo->options_templates_id . "' and pa.options_id = po.products_options_id and po.language_id = '" . $languages_id . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . $languages_id . "' " .
                "order by po.products_options_sort_order, po.products_options_name, pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name"
            );
            $tmp = tep_db_fetch_array(tep_db_query("SELECT count(DISTINCT options_id) AS total FROM " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES . " pa WHERE pa.options_templates_id = '" . $pInfo->options_templates_id . "'"));
            $options_count = $tmp['total'];
        }elseif ( $pInfo->products_id ) {
            $query = tep_db_query(
                "select pa.products_attributes_id, po.products_options_id, po.products_options_name, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_discount_price, pa.products_options_sort_order, pa.product_attributes_one_time, pa.products_attributes_weight, pa.products_attributes_weight_prefix, pa.products_attributes_units, pa.products_attributes_units_price, " .
                " pa.default_option_value, " .
                " pa.products_attributes_filename, pa.products_attributes_maxdays, pa.products_attributes_maxcount /*,count(distinct po.products_options_id) as options_count*/ " .
                "from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                /* "left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on pa.attributes_id=pad.attributes_id " .*/
                "where pa.products_id = '" . $pInfo->products_id . "' and pa.options_id = po.products_options_id and po.language_id = '" . $languages_id . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . $languages_id . "' " .
                "order by po.products_options_sort_order, po.products_options_name, pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name"
            );
            $tmp = tep_db_fetch_array(tep_db_query("SELECT count(DISTINCT options_id) AS total FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa WHERE pa.products_id = '" . $pInfo->products_id . "'"));
            $options_count = $tmp['total'];
        }else{
            $products_attributes_id = \Yii::$app->request->post('products_attributes_id',[]);
            if ( !is_array($products_attributes_id) ) $products_attributes_id = [];
            $options_count = count($products_attributes_id);

            $query_selected = [];
            foreach ($products_attributes_id as $opt_id=>$opt_data){
                $query_selected += array_map(function($val_id) use($opt_id) { return $opt_id.'-'.$val_id; }, array_keys($opt_data));
            }

            $query = tep_db_query(
                "select '' AS products_attributes_id, po.products_options_id, po.products_options_name, pov.products_options_values_id, pov.products_options_values_name, '0.00' AS options_values_price, '+' AS price_prefix, '' AS products_attributes_discount_price, '' AS products_options_sort_order, '' AS product_attributes_one_time, '' AS products_attributes_weight, '' AS products_attributes_weight_prefix, '' AS products_attributes_units, '' AS products_attributes_units_price, ".
                " 0 AS default_option_value, ".
                " '' AS products_attributes_filename, '' AS products_attributes_maxdays, '' AS products_attributes_maxcount /*,count(distinct po.products_options_id) as options_count*/ " .
                "from ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." o2v, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                /* "left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on pa.attributes_id=pad.attributes_id " .*/
                "where po.language_id = '" . $languages_id . "' and pov.language_id = '" . $languages_id . "' " .
                "AND o2v.products_options_id=po.products_options_id AND o2v.products_options_values_id=pov.products_options_values_id ".
                (count($query_selected)>0?"and CONCAT(po.products_options_id, '-', pov.products_options_values_id) IN ('".implode("','",$query_selected)."') ":" and 1=0 ").
                "order by po.products_options_sort_order, po.products_options_name, pov.products_options_values_sort_order, pov.products_options_values_name"
            );
        }

        $_tax = \common\helpers\Tax::get_tax_rate_value($pInfo->products_tax_class_id);

        while ($data = tep_db_fetch_array($query)) {
            $without_inventory = !!$pInfo->without_inventory;
            $is_virtual_option = \common\helpers\Attributes::is_virtual_option($data['products_options_id']);
            if (!empty($pInfo->options_templates_id)) {
                $price0 = \common\helpers\Attributes::get_template_attributes_price($data['options_templates_attributes_id'], $view->defaultCurrency, 0);
                $fullpriceTmp = $price0;
            }else
            if ( empty($pInfo->products_id) ){
                $price0 = 0;
                $fullpriceTmp = 0;
            }else
            if (!$without_inventory && \common\helpers\Extensions::isAllowed('Inventory') && ($options_count==1) && !$is_virtual_option) {
                // else price column is not shown in assigned attributes list
                // with inventory the attributes price is not saved!!! (can't be split)
                $price0 = \common\helpers\Inventory::get_inventory_price_by_uprid($pInfo->products_id . '{' . $data['products_options_id'] .'}' . $data['products_options_values_id'], 1, 0, $view->defaultCurrency);
                $fullpriceTmp = $price0 +\common\helpers\Product::get_products_price_for_edit($pInfo->products_id);
                $data['price_prefix'] = \common\helpers\Inventory::get_inventory_price_prefix_by_uprid($pInfo->products_id . '{' . $data['products_options_id'] .'}' . $data['products_options_values_id'], 1, 0, $view->defaultCurrency);
            } else {
                $price0 = \common\helpers\Attributes::get_attributes_price($data['products_attributes_id'], $view->defaultCurrency, 0);
                if ( $is_virtual_option ) {
                    $fullpriceTmp = $price0;
                }else{
                    $fullpriceTmp = $price0 + \common\helpers\Product::get_products_price_for_edit($pInfo->products_id);
                }
            }

            if ( strpos($data['price_prefix'],'%')!==false && $pInfo->products_price_full!=1 ){
                $gross_price_formatted = $net_price_formatted = \common\helpers\Output::percent($price0,'');
            }else {
                $net_price_formatted = $currencies->display_price(($pInfo->products_price_full == 1 ? $fullpriceTmp : $price0), 0, 1, false);
                $gross_price_formatted = $currencies->display_price(($pInfo->products_price_full == 1 ? $fullpriceTmp : $price0), (double)$_tax, 1, false);
            }
            $products_file_url = '';
            if (file_exists(DIR_FS_DOWNLOAD . $data['products_attributes_filename'])) {
                $products_file_url = tep_href_link(FILENAME_DOWNLOAD, 'filename=' . $data['products_attributes_filename']);
            }
            if (!isset($selectedAttributes[$data['products_options_id']])) {
                $products_options_values = [];
                $products_options_values[] = [
                    'products_attributes_id' => $data['products_attributes_id'],
                    'products_options_values_id' => $data['products_options_values_id'],
                    'products_options_values_name' => $data['products_options_values_name'],
                    'products_attributes_weight_prefix' => $data['products_attributes_weight_prefix'],
                    'products_attributes_weight' => $data['products_attributes_weight'],
                    'default_option_value' => $data['default_option_value'],
                    'products_file' => $data['products_attributes_filename'],
                    'products_file_url' => $products_file_url,
                    'products_attributes_maxdays' => $data['products_attributes_maxdays'],
                    'products_attributes_maxcount' => $data['products_attributes_maxcount'],
                    'price_prefix' => $data['price_prefix'],
                    'prices' => (!empty($pInfo->options_templates_id)?
                        \common\helpers\Attributes::get_template_attributes_prices($data["options_templates_attributes_id"], (double)$_tax):
                        \common\helpers\Attributes::get_attributes_prices($data["products_attributes_id"], (double)$_tax)),
                    'net_price_formatted' => $net_price_formatted,
                    'gross_price_formatted' => $gross_price_formatted,
                ];
                $is_virtual_option = \common\helpers\Attributes::is_virtual_option($data['products_options_id']);
                $selectedAttributes[$data['products_options_id']] = [
                    'is_virtual_option' => $is_virtual_option,
                    'disable_action' => (!$is_virtual_option && $this->isParentProduct),
                    'products_options_id' => $data['products_options_id'],
                    'products_options_name' => $data['products_options_name'],
                    'values' => $products_options_values,
                    'is_ordered_values' => !empty($data['products_options_sort_order']),
                    'ordered_value_ids' => ',' . $data['products_options_values_id'],
                ];
            } else {

                $selectedAttributes[$data['products_options_id']]['values'][] = [
                    'products_attributes_id' => $data['products_attributes_id'],
                    'products_options_values_id' => $data['products_options_values_id'],
                    'products_options_values_name' => $data['products_options_values_name'],
                    'products_attributes_weight_prefix' => $data['products_attributes_weight_prefix'],
                    'products_attributes_weight' => $data['products_attributes_weight'],
                    'default_option_value' => $data['default_option_value'],
                    'products_file' => $data['products_attributes_filename'],
                    'products_file_url' => $products_file_url,
                    'products_attributes_maxdays' => $data['products_attributes_maxdays'],
                    'products_attributes_maxcount' => $data['products_attributes_maxcount'],
                    'price_prefix' => $data['price_prefix'],
                    'prices' => (!empty($pInfo->options_templates_id)?
                        \common\helpers\Attributes::get_template_attributes_prices($data['options_templates_attributes_id'], (double)$_tax):
                        \common\helpers\Attributes::get_attributes_prices($data["products_attributes_id"], (double)$_tax)),
                    'net_price_formatted' => $net_price_formatted,
                    'gross_price_formatted' => $gross_price_formatted,
                ];
                $selectedAttributes[$data['products_options_id']]['ordered_value_ids'] .= (',' . $data['products_options_values_id']);
                $selectedAttributes[$data['products_options_id']]['is_ordered_values'] = $selectedAttributes[$data['products_options_id']]['is_ordered_values'] || !empty($data['products_options_sort_order']);
            }
        }
        foreach ($selectedAttributes as $__opt_id => $__opt_data) {
            if ($__opt_data['is_ordered_values']) {
                $selectedAttributes[$__opt_id]['ordered_value_ids'] .= ',';
            } else {
                $selectedAttributes[$__opt_id]['ordered_value_ids'] = '';
            }
        }
        $view->selectedAttributes = $selectedAttributes;
    }
}
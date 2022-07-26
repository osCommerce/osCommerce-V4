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

use yii;
use common\models\Products;

class SaveProductProperties
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function save()
    {
        $products_id = $this->product->products_id;

        tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id  = '" . (int) $products_id . "'");
        $prop_ids = Yii::$app->request->post('prop_ids', array());
        $val_ids = Yii::$app->request->post('val_ids', array());
        $val_extra = Yii::$app->request->post('val_extra', array());
        
        foreach ($prop_ids as $properties_id) {
            if (is_array($val_ids[$properties_id] ?? null)) {
                $property = tep_db_fetch_array(tep_db_query("select properties_id, properties_type, extra_values from " . TABLE_PROPERTIES . " where properties_id = '" . (int) $properties_id . "'"));
                foreach ($val_ids[$properties_id] as $values_key =>  $values_id) {
                    $sql_data_array = [];
                    $sql_data_array['products_id'] = (int) $products_id;
                    $sql_data_array['properties_id'] = (int) $properties_id;
                    if ($property['properties_type'] == 'flag') {
                        $sql_data_array['values_flag'] = (int) $values_id;
                    } else {
                        $sql_data_array['values_id'] = (int) $values_id;
                    }
                    if ($property['extra_values'] == 1 && isset($val_extra[$properties_id][$values_key])) {
                        $sql_data_array['extra_value'] = $val_extra[$properties_id][$values_key];
                    }
                    tep_db_perform(TABLE_PROPERTIES_TO_PRODUCTS, $sql_data_array);
                }
            }
        }
    }
}
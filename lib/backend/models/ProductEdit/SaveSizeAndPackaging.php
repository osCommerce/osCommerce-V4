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

class SaveSizeAndPackaging
{
    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function prepareSave()
    {
        $sql_data_array = [];
        $is_virtual = (int) Yii::$app->request->post('is_virtual');
        $bundle_volume_calc = (int) Yii::$app->request->post('bundle_volume_calc');
        if (!in_array($bundle_volume_calc, [0, 1, 2])) {
          $bundle_volume_calc = 0;
        }
        $sql_data_array['is_virtual'] = $is_virtual;
        $sql_data_array['bundle_volume_calc'] = $bundle_volume_calc;
        if ($is_virtual == 1) {
            //upload
            if (Yii::$app->request->post('delete_products_file') == 'yes') {
                $products_previous_file = Yii::$app->request->post('products_previous_file');
                $count = Products::find()->where(['products_file' => $products_previous_file])->count();
                if ($count <= 1) {
                    @unlink(DIR_FS_DOWNLOAD . $products_previous_file);
                }
                $sql_data_array['products_file'] = '';
            } else {
                $products_file_name = Yii::$app->request->post('products_file');
                if (tep_not_null($products_file_name) && ($products_file_name != 'none')) {
                    $tmp_name = \Yii::getAlias('@webroot');
                    $tmp_name .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                    $tmp_name .= $products_file_name;
                    $new_name = DIR_FS_DOWNLOAD . $products_file_name;
                    copy($tmp_name, $new_name);
                    @unlink($tmp_name);
                    $sql_data_array['products_file'] = tep_db_prepare_input($products_file_name);

                    if ($this->product->products_id) {
                        \common\helpers\Download::updateOrderedFile(
                            $this->product->products_id,
                            $sql_data_array['products_file']
                        );
                    }
                }
                //$products_file_name = Yii::$app->request->post('products_file');
                //if (tep_not_null($products_file_name) && ($products_file_name != 'none')) {
                /* $products_file = new \upload('products_file');
                  $products_file->set_destination(DIR_FS_DOWNLOAD);
                  if ($products_file->parse() && $products_file->save()) {
                  $products_file_name = $products_file->filename;
                  $sql_data_array['products_file'] = tep_db_prepare_input($products_file_name);
                  } */
                //}
            }
        } else {
            $sql_data_array['dimensions_cm'] = Yii::$app->request->post('dimensions_cm'); //string
            $sql_data_array['length_cm'] = Yii::$app->request->post('length_cm');
            $sql_data_array['width_cm'] = Yii::$app->request->post('width_cm');
            $sql_data_array['height_cm'] = Yii::$app->request->post('height_cm');
            $sql_data_array['products_weight'] = $sql_data_array['weight_cm'] = Yii::$app->request->post('weight_cm');

            $sql_data_array['dimensions_in'] = Yii::$app->request->post('dimensions_in'); //string
            $sql_data_array['length_in'] = Yii::$app->request->post('length_in');
            $sql_data_array['width_in'] = Yii::$app->request->post('width_in');
            $sql_data_array['height_in'] = Yii::$app->request->post('height_in');
            $sql_data_array['weight_in'] = Yii::$app->request->post('weight_in');

            $sql_data_array['inner_carton_size'] = Yii::$app->request->post('inner_carton_size'); //string
            $sql_data_array['inner_carton_dimensions_cm'] = Yii::$app->request->post('inner_carton_dimensions_cm'); //string
            $sql_data_array['inner_length_cm'] = Yii::$app->request->post('inner_length_cm');
            $sql_data_array['inner_width_cm'] = Yii::$app->request->post('inner_width_cm');
            $sql_data_array['inner_height_cm'] = Yii::$app->request->post('inner_height_cm');
            $sql_data_array['inner_weight_cm'] = Yii::$app->request->post('inner_weight_cm');

            $sql_data_array['inner_carton_dimensions_in'] = Yii::$app->request->post('inner_carton_dimensions_in'); //string
            $sql_data_array['inner_length_in'] = Yii::$app->request->post('inner_length_in');
            $sql_data_array['inner_width_in'] = Yii::$app->request->post('inner_width_in');
            $sql_data_array['inner_height_in'] = Yii::$app->request->post('inner_height_in');
            $sql_data_array['inner_weight_in'] = Yii::$app->request->post('inner_weight_in');

            $sql_data_array['outer_carton_size'] = Yii::$app->request->post('outer_carton_size'); //string
            $sql_data_array['outer_carton_dimensions_cm'] = Yii::$app->request->post('outer_carton_dimensions_cm'); //string
            $sql_data_array['outer_length_cm'] = Yii::$app->request->post('outer_length_cm');
            $sql_data_array['outer_width_cm'] = Yii::$app->request->post('outer_width_cm');
            $sql_data_array['outer_height_cm'] = Yii::$app->request->post('outer_height_cm');
            $sql_data_array['outer_weight_cm'] = Yii::$app->request->post('outer_weight_cm');

            $sql_data_array['outer_carton_dimensions_in'] = Yii::$app->request->post('outer_carton_dimensions_in'); //string
            $sql_data_array['outer_length_in'] = Yii::$app->request->post('outer_length_in');
            $sql_data_array['outer_width_in'] = Yii::$app->request->post('outer_width_in');
            $sql_data_array['outer_height_in'] = Yii::$app->request->post('outer_height_in');
            $sql_data_array['outer_weight_in'] = Yii::$app->request->post('outer_weight_in');

            $sql_data_array['pack_unit'] = Yii::$app->request->post('pack_unit');
            $sql_data_array['packaging'] = Yii::$app->request->post('packaging'); //string

            $sql_data_array['volume_weight_cm'] = Yii::$app->request->post('volume_weight_cm');
            $sql_data_array['volume_weight_in'] = Yii::$app->request->post('volume_weight_in');
        }
        $this->product->setAttributes($sql_data_array, false);
    }

}

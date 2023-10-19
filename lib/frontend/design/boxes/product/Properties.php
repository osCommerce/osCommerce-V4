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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Properties extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $breadcrumb;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $params = Yii::$app->request->get();

        if (!$params['products_id']) return '';

        $products_data_r = tep_db_query(
            "select p.products_id,
          p.products_model,
          p.products_ean,
          p.products_isbn,
          p.products_asin,
          p.products_upc,
          m.manufacturers_id, m.manufacturers_name, m.manufacturers_image
        from " . TABLE_PRODUCTS . " p
        left join ".TABLE_MANUFACTURERS." m on m.manufacturers_id=p.manufacturers_id
        where
          p.products_id='" . (int)$params['products_id'] . "'
    ");
        if ( tep_db_num_rows($products_data_r)==0 ) return '';

        $products_data = tep_db_fetch_array($products_data_r);
        $products_data['manufacturers_link'] = empty($products_data['manufacturers_id'])?'':tep_href_link(/*FILENAME_DEFAULT*/'catalog/index','manufacturers_id='.$products_data['manufacturers_id']);

        if (isset($this->settings[0]['category'])) {
            $category = \common\helpers\Product::getCategories($params['products_id']);
            $products_data['category'] = $category['categories_name'];
            $products_data['category_link'] = Yii::$app->urlManager->createUrl(['catalog', 'cPath' => $category['categories_id']]);
        }

        $have_product_data =
            !empty($products_data['manufacturers_name']) || !empty($products_data['products_model']) ||
            !empty($products_data['products_ean']) || !empty($products_data['products_isbn']) || !empty($products_data['products_upc']);

        if (!isset($this->settings[0]['main_property']) || $this->settings[0]['main_property'] != 'no') {
            $properties_array = array();
            $values_array = array();
            $extra_values = array();
            $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id, extra_value from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_product = '1' and p2p.products_id = '" . (int)$products_data['products_id'] . "'");
            while ($properties = tep_db_fetch_array($properties_query)) {
                if (!in_array($properties['properties_id'], $properties_array)) {
                    $properties_array[] = $properties['properties_id'];
                }
                $values_array[$properties['properties_id']][] = $properties['values_id'];
                $extra_values[$properties['properties_id']][] = $properties['extra_value'];
            }
            $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array, '', '', $extra_values);
        }
        
        if ($products_data['products_model'] && @$this->settings[0]['show_model'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'sku' => $products_data['products_model']
            ]], ['Product', 'sku']);
        }
        if ($products_data['products_ean'] && @$this->settings[0]['show_ean'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'gtin13' => $products_data['products_ean']
            ]], ['Product', 'gtin13']);
        }
        if ($products_data['products_isbn'] && @$this->settings[0]['show_isbn'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'isbn' => $products_data['products_isbn']
            ]], ['Product', 'isbn']);
        }
        if ($products_data['products_asin'] && @$this->settings[0]['show_asin'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'asin' => $products_data['products_asin']
            ]], ['Product', 'asin']);
        }
        if ($products_data['products_upc'] && @$this->settings[0]['show_upc'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'upc' => $products_data['products_upc']
            ]], ['Product', 'upc']);
        }
        if ($products_data['manufacturers_name'] && @$this->settings[0]['show_manufacturer'] != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $products_data['manufacturers_name']
                ],
            ]], ['Product', 'brand', '@type']);
        }
        if (@$this->settings[0]['show_manufacturer'] != 'no' && $products_data['manufacturers_image'] && is_file(\common\classes\Images::getFSCatalogImagesPath() . $products_data['manufacturers_image'])) {
            \frontend\design\JsonLd::addData(['Product' => [
                'brand' => [
                    'image' => Yii::$app->urlManager->createAbsoluteUrl($products_data['manufacturers_image'])
                ],
            ]], ['Product', 'brand', 'image']);
        }

        if (is_array($properties_tree_array) && count($properties_tree_array)) {

            \frontend\design\JsonLd::addData(['Product' => [
                'additionalProperty' => [
                    '@type' => 'PropertyValue',
                ],
            ]]);

            foreach ($properties_tree_array as $property) {

                $values = [];
                foreach ($property['values'] as $value) {
                    $values[] = $value;
                }

                \frontend\design\JsonLd::addData(['Product' => [
                    'additionalProperty' => [
                        'value' => [[
                            '@type' => 'PropertyValue',
                            'name' => $property['properties_name'],
                            'value' => $values,
                        ]],
                    ],
                ]]);

            }
        }

        if ( is_array($properties_tree_array) && count($properties_array)>0 || $have_product_data ) {
            return IncludeTpl::widget([
                'file' => 'boxes/product/properties.tpl',
                'params' => [
                    'products_data' => $products_data,
                    'properties_tree_array' => $properties_tree_array,
                    'path' => \common\helpers\Product::get_product_path($products_data['products_id']),
                    'settings' => $this->settings[0]
                ]
            ]);
        }
        return '';
    }

}
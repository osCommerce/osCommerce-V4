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

namespace frontend\design\boxes\catalog;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\Product;
use common\classes\Images;
use frontend\design\Info;

class Compare extends Widget
{

    public $file;
    public $params;
    public $content;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $current_category_id, $manufacturers_id;

        $compare = tep_db_prepare_input(Yii::$app->request->get('compare'));
        $currencies = Yii::$container->get('currencies');

        $error_text = '';
        if (!is_array($compare) /*|| count($compare) < 2 || count($compare) > 4*/) {
            $error_text = TEXT_PLEASE_SELECT_COMPARE;
        } else {
            $properties_array = array();
            $values_array = array();
            $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_compare = '1' and p2p.products_id in ('" . implode("','", array_map('intval', $compare)) . "')");
            while ($properties = tep_db_fetch_array($properties_query)) {
                if (!in_array($properties['properties_id'], $properties_array)) {
                    $properties_array[] = $properties['properties_id'];
                }
                $values_array[$properties['properties_id']][] = $properties['values_id'];
            }
            $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);

            $products_data_array = array();
            foreach ($compare as $products_id) {
                $products_arr = tep_db_fetch_array(tep_db_query("select products_id, products_model, products_price, products_tax_class_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
                $products_data_array[$products_id]['id'] = $products_id;

                $properties_array = array();
                $values_array = array();
                $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_compare = '1' and p2p.products_id = '" . (int)$products_id . "'");
                while ($properties = tep_db_fetch_array($properties_query)) {
                    if (!in_array($properties['properties_id'], $properties_array)) {
                        $properties_array[] = $properties['properties_id'];
                    }
                    $values_array[$properties['properties_id']][] = $properties['values_id'];
                }
                $products_data_array[$products_id]['properties_tree'] = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);
            }

            foreach ($properties_tree_array as $properties_id => $property) {
                $values_array = array();
                foreach ($products_data_array as $products_id => $products_data) {
                    if (is_array($products_data['properties_tree'][$properties_id]['values'])) {
                        $values_array[] = trim(implode(' ', $products_data['properties_tree'][$properties_id]['values']));
                    } else {
                        $values_array[] = '';
                    }
                }
                $unique_values_array = array_unique($values_array);
                if (count($unique_values_array) > 1 /* || trim($unique_values_array[0]) == '' */) {
                    $properties_tree_array[$properties_id]['vary'] = true;
                } else {
                    $properties_tree_array[$properties_id]['vary'] = false;
                }
            }
        }

        $compareCategoryId = 0;
        foreach ($_SESSION['compare'] as $catId => $compareArr) {
            if (in_array($compareArr[0], $compare)){
                $compareCategoryId = $catId;
                break;
            }
        }
        Info::addJsData(['compare' => [
            'currentCategory' => [
                'id' => $compareCategoryId,
            ]
        ]]);

        $this->settings['listing_type'] = 'compare';
        $this->settings['productsInArray'] = true;
        Info::getListProductsDetails($compare, $this->settings);

        $productListing = \frontend\design\boxes\ProductListing::widget([
            'products' => Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
            'settings' => $this->settings,
            'id' => $this->id
        ]);

        return IncludeTpl::widget(['file' => 'boxes/catalog/compare.tpl', 'params' => [
            'error_text' => $error_text,
            'products_data_array' => $products_data_array,
            'properties_tree_array' => $properties_tree_array,
            'standAlonePage' => !Yii::$app->request->isAjax,
            'productListing' => json_decode($productListing, true),
            'settings' => $this->settings,
            'lastCategoryUrl' => $_SESSION['lastCategoryUrl'],
        ]]);
    }
}
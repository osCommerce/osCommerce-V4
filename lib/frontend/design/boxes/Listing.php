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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class Listing extends Widget
{

  public $file;
  public $params;
  public $settings;
  public $products;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $current_category_id;
    $languages_id = \Yii::$app->settings->get('languages_id');

    if (isset($this->params['listing_type_rows'])) {
        $this->settings[0]['listing_type_rows'] = $this->params['listing_type_rows'];
    }

    if ( !isset($this->params['listing_split']) || !is_object($this->params['listing_split']) || !is_a($this->params['listing_split'], 'frontend\design\splitPageResults' ) ) {
      return '';
    }
    $listing_split = $this->params['listing_split'];
    /**
     * @var $listing_split SplitPageResults
     */

    if ($listing_split->number_of_rows > 0){

      $products = Yii::$container->get('products');
      if (!isset($this->settings['listing_type']))
        $this->settings['listing_type'] = 'main';

      //$this->settings[0]['list_type'] = (isset($this->params['list_type']) ? $this->params['list_type'] : null);
      if (is_null($this->products)){
        if (is_string($listing_split->sql_query)) {
          Info::getProducts(tep_db_query($listing_split->sql_query), $this->settings);
          $this->products = $products->getAllProducts($this->settings['listing_type']);
        } elseif ($listing_split->sql_query instanceof \yii\db\Query) {
            $this->settings['settingsAdditional'] = $this->params['settingsAdditional'] ?? [];
          Info::getListProductsDetails($listing_split->sql_query->column(), $this->settings);
          $this->products = $products->getAllProducts($this->settings['listing_type']);
        }
      }
        $this->settings[0]['product_names_teg'] = '';

        $this->settings['mainListing'] = true;

        if (Info::get_gl() == 'b2b'){
            $this->settings['b2b'] = true;
        }
        
        if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
            return IncludeTpl::widget([
                'file' => 'boxes/catalog/listing.tpl',
                'params' => [
                    'products' => $this->products,
                    'products_carousel' => Info::themeSetting('products_carousel'),
                    'settings' => $this->settings,
                    'params' => [
                        'url' => tep_href_link(Yii::$app->controller->id . '/' . Yii::$app->controller->action->id, \common\helpers\Output::get_all_get_params(array('page'))),
                        'number_of_rows' => $listing_split->number_of_rows
                    ],
                    'fbl' => $_GET['fbl'],
                    'languages_id' => $languages_id
                ]
            ]);
        } else {
            return \frontend\design\boxes\ProductListing::widget([
                'products' => $this->products,
                'settings' => $this->settings,
                'id' => $this->id,
                'params' => $this->params
            ]);
        }

    } elseif (Yii::$app->controller->action->id == 'all-products') {
      return '<div class="no-found">' . ITEM_NOT_FOUND . '</div>';
    } elseif (\common\helpers\Categories::count_products_in_category($current_category_id)) {

        Info::addBoxToCss('products-listing');
        return '<div class="no-found">' . ALL_PRODUCTS_OF_STOCK . '</div><div class="show-ofstock-products">' .
            \frontend\design\boxes\HeaderStock::widget([
                'params' => [
                    'text' => SHOW_OUT_OF_STOCK_PRODUCTS
                ]
            ]) . '</div>';
    }

    return '';


  }
}

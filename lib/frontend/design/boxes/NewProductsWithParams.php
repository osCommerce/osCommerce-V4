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
use frontend\design\Info;
use common\classes\platform;
use common\helpers\Product;

class NewProductsWithParams extends Widget
{
    use \common\helpers\SqlTrait;

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
      global $current_category_id;
      $languages_id = \Yii::$app->settings->get('languages_id');

      if ($this->settings[0]['params']) {
          $max = $this->settings[0]['params'];
      } else {
          $max = MAX_DISPLAY_NEW_PRODUCTS;
      }

      $qParams = [
        'orderBy' => ['products_date_added' => SORT_DESC],
        'limit' => (int)$max,
        'get' => [],
      ];

/** 0x1 simple
 *  0x2 bundle
 *  0x4 PC Conf
 */
      if ($this->settings[0]['product_types']>0) {
        $type_where = ' ( 0 ';
        if ($this->settings[0]['product_types'] & 1) {
          $type_where .= ' or (p.is_bundle=0 and p.products_pctemplates_id=0)';
        }
        if ($this->settings[0]['product_types'] & 2) {
          $type_where .= ' or p.is_bundle>0';
        }
        if ($this->settings[0]['product_types'] & 4) {
          $type_where .= ' or p.products_pctemplates_id>0';
        }
        $type_where .= ')';
        $qParams['customAndWhere'] = $type_where;
      }
      
      if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS'))>0) {
        if (!empty($qParams['customAndWhere'])) {
          $qParams['customAndWhere'] .= ' and ';
        }
        $qParams['customAndWhere'] .= 'p.products_new_until>="' . date(\common\helpers\Date::DATABASE_DATE_FORMAT) . '"';
      }

      $gets = Yii::$app->request->get();

      if ($this->settings[0]['same_category']) {
        $qParams['currentCategory'] = true; //!$this->params['hasSubcategories']
        if ((int)$current_category_id <= 0 ) {
          
          if (!empty($gets['products_id'])) {
            $p = \common\helpers\Product::getCategories($gets['products_id']);
            $current_category_id = $p['categories_id'];
          }
        }
      } else {
        $qParams['currentCategory'] = false;
      }
///parse get settings
      $getFilters = [];
      if (!empty(trim($this->settings[0]['get']))) {
        $pairs = explode('&', trim($this->settings[0]['get']));
        if (!empty($pairs) && is_array($pairs)) {
          foreach ($pairs as $pair) {
            $kv = explode('=', $pair, 2);
            if (!empty($kv[0]) && !empty($kv[1])) {
              if (strpos($kv[0], '[]')) {
                $getFilters[str_replace('[]', '', $kv[0])][] = $kv[1];
              } elseif ($keyL = strpos($kv[0], '[')) {
                $key = substr($kv[0], 0, $keyL);
                $idx = substr($kv[0], $keyL+1, -1);
                $getFilters[$key][$idx] = $kv[1];
              } else {
                $getFilters[$kv[0]] = $kv[1];
              }
            }
          }
        }
      }
//add same prop to the get array.
      if (!empty($this->settings[0]['same_properties_value'])) {
        $propIds = preg_split('/[,; ]/', $this->settings[0]['same_properties_value'], -1, PREG_SPLIT_NO_EMPTY);
        if ($propIds && count($propIds)>0) {
          $p = \common\helpers\Product::getPropertiesShort($gets['products_id']);

          if ($p && is_array($p)) {
            foreach ($p as $v) {
              if (in_array($v['properties_id'], $propIds)) {
                $getFilters['pr' . $v['properties_id']][0] = $v['values_id'];
              }
            }
          }
        }
      }

      if (!empty($getFilters)) {
        $qParams['get'] =  $getFilters;
      }

      $q = new \common\components\ProductsQuery($qParams);


      $this->settings['listing_type'] .= '-' . $this->id;
      $products = Info::getListProductsDetails($q->buildQuery()->rebuildByGroup()->allIds(), $this->settings);

      if (count($products) > 0) {

          if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
              return IncludeTpl::widget([
                  'file' => 'boxes/new-products-params.tpl',
                  'params' => [
                      'products' => Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                      'settings' => $this->settings,
                      'languages_id' => $languages_id,
                      'id' => $this->id
                  ]
              ]);
          } else {
              return \frontend\design\boxes\ProductListing::widget([
                  'products' => Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                  'settings' => $this->settings,
                  'id' => $this->id
              ]);
          }
      }

      return '';
    }
    
}
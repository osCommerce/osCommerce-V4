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
use frontend\design\Info;
use common\classes\platform;
use common\helpers\Product;
use common\models\OrdersProducts;

class AlsoPurchased extends Widget
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
    $languages_id = \Yii::$app->settings->get('languages_id');
    $params = Yii::$app->request->get();

    if ($params['products_id']) {

        if ($this->settings[0]['params']) {
            $max = $this->settings[0]['params'];
        } else {
            $max = 4;
        }

        $cW = ['exists', OrdersProducts::find()->alias('opa')
                          ->innerJoin(OrdersProducts::tableName(),
                              " opa.orders_id = ". OrdersProducts::tableName() . ".orders_id and ". OrdersProducts::tableName() . ".products_id != '" . (int)$params['products_id'] . "'")
                                            ->andWhere("p.products_id = ". OrdersProducts::tableName() . ".products_id")
                                            ->andWhere([
                                                    'opa.products_id' => (int)$params['products_id'],
                                                  ])
          ];

        $q = new \common\components\ProductsQuery([
          'limit' => (int)$max,
          'customAndWhere' => $cW,
          //order by o.date_purchased desc
        ]);

        $this->settings['listing_type'] = 'also-purchased';
        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);

        if (count($products) > 0) {
            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/product/also-purchased.tpl',
                    'params' => [
                        'products' => Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                        'settings' => $this->settings
                    ]
                ]);
            } else {
                return \frontend\design\boxes\ProductListing::widget([
                    'products' => Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }


      } else {
        return Info::hideBox($this->id, $this->settings[0]['hide_parents']);
      }
    } else {
      return Info::hideBox($this->id, $this->settings[0]['hide_parents']);
    }
  }
}
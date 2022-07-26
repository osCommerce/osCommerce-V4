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
use frontend\design\Info;

class CrossSell extends Widget
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
        $params = Yii::$app->request->get();
        global $current_category_id;

        if (!$current_category_id) {
            return '';
        }

        $xsell_type_id = 0;
        if ( isset($this->settings[0]['xsell_type_id']) ){
            $xsell_type_id = (int)$this->settings[0]['xsell_type_id'];
        }

        $max = (isset($this->settings[0]['max_products']) ? $this->settings[0]['max_products'] : 4);

        $cW = ['exists', \common\models\CatsProductsXsell::find()->alias('xp')
                                          ->andWhere("p.products_id = xp.xsell_products_id")
                                          ->andWhere([
                                                  'xp.categories_id' => (int)$current_category_id,
                                                  'xp.xsell_type_id' => $xsell_type_id,
                                                ])
            ];

        $q = new \common\components\ProductsQuery([
          'limit' => (int)$max,
          'customAndWhere' => $cW,
        ]);
        
        $this->settings['listing_type'] = 'cross-sell-' . $xsell_type_id;
        $this->settings['options_prefix'] = 'list';
        
        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);

        if (count($products) > ($this->settings[0]['show_cart_button']?1:0)) {

            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/product/cross-sell.tpl',
                    'params' => [
                        'products' => $products,
                        'settings' => $this->settings,
                        'id' => $this->id
                    ]
                ]);
            } else {
                return \frontend\design\boxes\ProductListing::widget([
                    'products' => $products,
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }
        }

    }
}
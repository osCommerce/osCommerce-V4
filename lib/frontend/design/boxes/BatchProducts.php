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

use common\models\OrdersProducts;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\helpers\Product;
use common\classes\platform;
use yii\db\Query;

class BatchProducts extends Widget
{
    use \common\helpers\SqlTrait;

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();

        if ( !isset($this->params['products_id']) || empty($this->params['products_id']) ) {
            $this->params['products_id'] = intval(Yii::$app->request->get('products_id',0));
        }
    }

    public function run()
    {
        if ( $this->params['products_id'] ) {
            $products = $this->productList($this->params['products_id']);

            if (count($products) > 0) {
                if ( isset($this->settings[0]['product_auto_select']) && $this->settings[0]['product_auto_select'] ) {
                    $products[0]['batchSelected'] = true;
                }
                if ( $this->settings[0]['force_disable_attributes_quantity'] ) {
                    foreach ($products as $idx => $product) {
                        if (array_key_exists('show_attributes_quantity', $product)) {
                            $products[$idx]['show_attributes_quantity'] = 0;
                        }
                    }
                }


                return \frontend\design\boxes\ProductListing::widget([
                    'products' => $products,
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }
        }

        return Info::hideBox($this->id, $this->settings[0]['hide_parents']);
    }

    protected function productList( $products_id )
    {
        if ($this->settings[0]['sort_order']) {
            $orderBy = \common\helpers\Sorting::getOrderByArray($this->settings[0]['sort_order']);
        } else {
            $orderBy = ['rand()' => SORT_ASC];
        }

        Info::addJsData(['widgets' => [
            $this->id => [
                'batchSelectedWidget' => $this->settings[0]['batchSelectedWidget'],
            ]]]);

        $this->settings['listing_type'] = 'batchProducts' . $this->id;

        $cW = ['AND',
            ['not exists',
                (new Query())->from(['bs_parent' => TABLE_SETS_PRODUCTS])->where('p.products_id = bs_parent.sets_id')
            ],
            ['products_pctemplates_id'=>0],
        ];

        if ( isset($this->settings[0]['product_source'])
            && preg_match('/xsell_(\d+)/',$this->settings[0]['product_source'], $xsell_match) ) {
            $xsell_type_id = (int)$xsell_match[1];

            if ($xsellModel = \common\helpers\Extensions::getModel('UpSell', 'ProductsXsell')) {
                $cW[] = ['exists', $xsellModel::find()->alias('xp')
                    ->andWhere("p.products_id = xp.xsell_id")
                    ->andWhere([
                        'xp.products_id' => (int)$products_id,
                        'xp.xsell_type_id' => $xsell_type_id,
                    ])
                ];
            }

        }elseif ( isset($this->settings[0]['product_source']) && $this->settings[0]['product_source']=='main_product'){
            $cW[] = ['p.products_id'=>(int)$products_id];
        }elseif ( isset($this->settings[0]['product_source']) && $this->settings[0]['product_source']=='alsopurchased'){
            $cW[] = ['exists', OrdersProducts::find()->alias('opa')
                ->innerJoin(OrdersProducts::tableName(),
                    " opa.orders_id = " . OrdersProducts::tableName() . ".orders_id and " . OrdersProducts::tableName() . ".products_id != '" . (int)$products_id . "'")
                ->andWhere("p.products_id = " . OrdersProducts::tableName() . ".products_id")
                ->andWhere([
                    'opa.products_id' => (int)$products_id,
                ])
            ];
        }else{

        }

        $q = new \common\components\ProductsQuery([
            'orderBy' => $orderBy,
            'customAndWhere' => $cW,
            'limit' => $this->settings[0]['params'] ? (int)$this->settings[0]['params'] : 10,
        ]);

        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);

        return Yii::$container->get('products')->getAllProducts($this->settings['listing_type']);
    }
}
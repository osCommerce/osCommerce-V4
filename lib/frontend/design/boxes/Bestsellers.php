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
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\helpers\Product;
use common\classes\platform;

class Bestsellers extends Widget
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

        if ($this->settings[0]['params']) {
            $max = $this->settings[0]['params'];
        } else {
            $max = 4;
        }

        if ((!empty($this->settings[0]['days']) && (int)$this->settings[0]['days']>0) ||
            (defined('BESTSELLERS_SOLD_LAST_DAYS') && (int)BESTSELLERS_SOLD_LAST_DAYS>0) ) {
            $exclude_order_statuses_array = \common\helpers\Order::extractStatuses(DASHBOARD_EXCLUDE_ORDER_STATUSES);
            if (!empty($this->settings[0]['days']) && (int)$this->settings[0]['days']>0) {
                $days = (int)$this->settings[0]['days'];
            }
            if (empty($days)) {
                $days = (int)BESTSELLERS_SOLD_LAST_DAYS;
            }

            $andWhere = ['and',
                'p.products_ordered > 0',
                ['exists', \common\models\OrdersProducts::find()->alias('op')
                            ->joinWith('order o')->andWhere([
                                'and',
                                'op.products_id=p.products_id',
                                ['>=', 'o.date_purchased', date('Y-m-d', strtotime($days . ' days ago')) ],
                                ['not in', 'o.orders_status', $exclude_order_statuses_array]
                              ])
                ]
              ];
        } else {
            $andWhere = ['and', 'p.products_ordered > 0'];
        }

        if (isset($this->settings[0]['category'])) {
            $subCategories = \common\helpers\Categories::get_categories('', $this->settings[0]['category']);
            $categories = [];
            $categories[] = $this->settings[0]['category'];
            foreach ($subCategories as $subCategory) {
                $categories[] = $subCategory['id'];
            }

            $andWhere[] = ['exists', \common\models\Products2Categories::find()->alias('p2c')
                ->andWhere([
                    'and',
                    'p2c.products_id=p.products_id',
                    ['IN', 'p2c.categories_id', $categories],
                ])
            ];
        }

        $q = new \common\components\ProductsQuery([
          'orderBy' => ['bestsellers' => SORT_DESC],
          'limit' => (int)$max,
          'customAndWhere' => $andWhere,
        ]);

        $this->settings['listing_type'] = 'bestsellers';
        $this->settings[0]['view_as'] = ($this->settings[0]['view_as'] ?? false);
//echo __FILE__ .':' . __LINE__ . ' ' . $q->buildQuery()->getQuery()->createCommand()->rawSql . "<br>\n";
        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);

        if (count($products) <= 0) {
            return Info::hideBox($this->id, ArrayHelper::getValue($this->settings, [0,'hide_parents']));
        }

        if (
            in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2']) ||
            !$this->settings[0]['view_as']
        ) {
            return IncludeTpl::widget([
                'file' => 'boxes/bestsellers.tpl',
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
}
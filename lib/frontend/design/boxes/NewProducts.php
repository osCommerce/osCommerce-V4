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
use yii\helpers\ArrayHelper;

class NewProducts extends Widget
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

        if (isset($this->settings[0]['params']) && $this->settings[0]['params']) {
            $max = (int)$this->settings[0]['params'];
        } else {
            $max = (int)MAX_DISPLAY_NEW_PRODUCTS;
        }

        /** 0x1 simple
         *  0x2 bundle
         *  0x4 PC Conf
         */
        if (isset($this->settings[0]['product_types']) && $this->settings[0]['product_types']>0) {
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
        } else {
            $type_where = '';
        }

        if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS'))>0) {
          if (!empty($type_where)) {
            $type_where .= ' and ';
          }
          $type_where .= 'p.products_new_until>="' . date(\common\helpers\Date::DATABASE_DATE_FORMAT) . '"';
        }

        $q = new \common\components\ProductsQuery([
            'orderBy' => ['products_date_added' => SORT_DESC],
            'limit' => (int)$max,
            'onlyWithImages' => true,
            'customAndWhere' => $type_where,
        ]);

        $this->settings['listing_type'] = 'new-products';

        $products = Info::getListProductsDetails($q->buildQuery()->rebuildByGroup()->allIds(), $this->settings);

        if (count($products) > 0) {
            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/new-products.tpl',
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

        return Info::hideBox($this->id, ArrayHelper::getValue($this->settings, [0,'hide_parents']));
    }
}
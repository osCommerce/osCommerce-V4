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

class ViewedProducts extends Widget
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
            $max = (int)$this->settings[0]['params'];
        } else {
            $max = (int)MAX_DISPLAY_NEW_PRODUCTS;
        }


        if (isset($_SESSION['viewed_products']) && is_array($_SESSION['viewed_products']) && count($_SESSION['viewed_products'])>0) {

            $get = Yii::$app->request->get();

            $viewed_product_ids = $_SESSION['viewed_products'];
            if ( isset($viewed_product_ids[(int)$get['products_id']]) ) {
                unset($viewed_product_ids[(int)$get['products_id']]);
            }
            if (!is_array($viewed_product_ids)) {
              return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents']);
            }
            
            $viewed_product_ids = array_map('intval',array_reverse($viewed_product_ids));
            $viewed_product_ids = array_slice($viewed_product_ids, 0, $max);
            $viewed = "'".implode("','",$viewed_product_ids)."'";

            if ($viewed) {
              $q = new \common\components\ProductsQuery([
                'customAndWhere' =>  ['p.products_id' => $viewed],
                'currentCategory' => false,/// suppose viewed should not be restricted by current category.
                'orderBy' =>  ['p.products_id' => SORT_ASC],
                // order by instr(',".tep_db_input(implode(',',$viewed_product_ids)).",',concat(',',p.products_id,','))
              ]);

              $allowedIds = array_flip($q->buildQuery()->allIds());

              // sort by order in session
              foreach ($viewed_product_ids as $k => $v) {
                $allowedIds[$v] = $k;
              }
              $allowedIds = array_flip($allowedIds);
              ksort($allowedIds);

              $this->settings['listing_type'] = 'viewed';
              $products = Info::getListProductsDetails(array_values($allowedIds), $this->settings);

                if (count($products) > 0) {
                    if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                        return IncludeTpl::widget([
                            'file' => 'boxes/viewed-products.tpl',
                            'params' => [
                                'products' => $products,
                                'settings' => $this->settings,
                                'languages_id' => $languages_id,
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

        return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents']);
    }
}
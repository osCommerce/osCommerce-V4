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
use common\helpers\Product;
use common\classes\platform;

class FeaturedProducts extends Widget
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
            $max = MAX_DISPLAY_FEATURED_PRODUCTS;
        }

        if (isset($this->settings[0]['sort_order']) && $this->settings[0]['sort_order']) {
            $orderBy = \common\helpers\Sorting::getOrderByArray($this->settings[0]['sort_order']);
        } else {
            $orderBy = ['rand()' => SORT_ASC];
        }

        $q = new \common\components\ProductsQuery([
            'orderBy' => $orderBy,
            'limit' => (int)$max,
            'page' => FILENAME_FEATURED_PRODUCTS,
            'featuredTypeId' => $this->settings[0]['featured_type_id'],
        ]);

        $this->settings['listing_type'] = 'featured-'.$this->id;
        $products = Info::getListProductsDetails($q->buildQuery()->rebuildByGroup()->allIds(), $this->settings);

        if (count($products) > 0) {
            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/featured-products.tpl',
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

        return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents'] ?? null);
    }
}
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

class SpecialsProducts extends Widget
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

        if (!isset($this->settings[0]['specials_type_id'])) {
            $this->settings[0]['specials_type_id'] = '';
        }

        $q = new \common\components\ProductsQuery([
          'limit' => (int)$max,
          'page' => FILENAME_SPECIALS,
          'specialsTypeId' => $this->settings[0]['specials_type_id'],
          'orderBy' => ['rand()' => SORT_ASC],
        ]);

        $this->settings['listing_type'] = 'special-products-' . $this->settings[0]['specials_type_id'];
        $products = Info::getListProductsDetails($q->buildQuery()->rebuildByGroup()->allIds(), $this->settings);

        if (count($products) > 0) {
            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/specials-products.tpl',
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
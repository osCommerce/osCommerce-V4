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

class Dimensions extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if ($this->params['products_id']) {
            $productsId = $this->params['products_id'];
        } else {
            $productsId = Yii::$app->request->get('products_id');
        }
        if (!$productsId ) {
            return '';
        }

        $products = Yii::$container->get('products');
        $data = $products->getProduct($productsId);

        $data['length_cm'] = (float)$data['length_cm'];
        $data['width_cm'] = (float)$data['width_cm'];
        $data['height_cm'] = (float)$data['height_cm'];
        $data['dimensions_cm'] = (float)$data['dimensions_cm'];

        return IncludeTpl::widget(['file' => 'boxes/product/dimensions.tpl', 'params' => [
            'data' => $data,
            'settings' => $this->settings[0]
        ]]);
    }
}
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
use backend\design\Style;

class ImageMapProduct extends Widget
{

    public $file;
    public $params;
    public $content;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $productsId = (int)Yii::$app->request->get('products_id');

        Info::addBlockToWidgetsList('image-maps');

        $mapsId = \common\models\Products::findOne($productsId)->maps_id ?? null;

        return \frontend\design\boxes\ImageMap::widget(['params' => ['mapsId' => $mapsId]]);

    }
}
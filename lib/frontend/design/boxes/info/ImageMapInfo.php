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

namespace frontend\design\boxes\info;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use backend\design\Style;

class ImageMapInfo extends Widget
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
        $infoId = (int)Yii::$app->request->get('info_id');

        Info::addBlockToWidgetsList('image-maps');

        $map = \common\models\Information::find()
            ->select(['maps_id'])
            ->distinct()
            ->where(['information_id' => $infoId])->asArray()->all();

        return \frontend\design\boxes\ImageMap::widget(['params' => ['mapsId' => $map[0]['maps_id']]]);

    }
}
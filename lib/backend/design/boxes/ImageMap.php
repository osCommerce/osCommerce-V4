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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class ImageMap extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (false === \common\helpers\Acl::checkExtensionAllowed('ImageMaps', 'allowed')) {
            return '';
        }
        global $languages_id;

        $mapImage = '';
        $mapTitle = '';

        $map = \common\extensions\ImageMaps\models\ImageMaps::findOne($this->settings[0]['maps_id']);
        if ($map) {
            $mapImage = $map->image;
            $mapTitle = $map->getTitle($languages_id);
        }

        return $this->render('image-map.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'mapsTitle' => $mapTitle,
            'mapsImage' => $mapImage,
        ]);
    }
}
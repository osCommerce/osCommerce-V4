<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\models;

class CategoriesPlatformSettings extends \yii\db\ActiveRecord
{
  
  public static function tableName() {
    return 'categories_platform_settings';
  }
  
  public function rules()
  {
      return [
          [['categories_id', 'platform_id', 'show_on_home', 'maps_id'], 'integer'],
          [['categories_image', 'categories_image_2', 'categories_image_3'], 'string', 'max' => 128],
      ];
  }

  /*public function getImageMap()
  {
      if (!class_exists('\common\extensions\ImageMaps\models\ImageMaps')) {
          return null;
      }
    return $this->hasOne(\common\extensions\ImageMaps\models\ImageMaps::className(), ['maps_id' => 'maps_id']);
  }

  public function getImageMapTitle()
  {
      if (!class_exists('\common\extensions\ImageMaps\models\ImageMapsProperties')) {
          return null;
      }
    $languages_id = \Yii::$app->settings->get('languages_id');
    return
      $this->hasOne(\common\extensions\ImageMaps\models\ImageMapsProperties::className(), ['maps_id' => 'maps_id']) //->via('imageMaps')
           ->andWhere(['maps_properties_name' => 'title', 'languages_id' => $languages_id])
           ->select(['title' => 'maps_properties_value',
                    'maps_id'
                    ])
        ;
  }*/

}

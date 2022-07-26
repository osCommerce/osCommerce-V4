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
namespace common\models;

use Yii;

/**
 * This is the model class for table "properties".
 */
class Properties extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'properties';
    }
    
    public function getValues(){
        return $this->hasMany(PropertiesValues::className(), ['properties_id' => 'properties_id']);
    }
    
    public function getDescriptions(){
        return $this->hasMany(PropertiesDescription::className(), ['properties_id' => 'properties_id']);
    }

    public function getProperties2Products(){
        return $this->hasMany(Properties2Propducts::className(), ['properties_id' => 'properties_id']);
    }

    public function getProducts(){
        return $this->hasMany(Products::className(), ['products_id' => 'products_id'])->via('properties2Products');
    }

    public function getBackendname(){
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->hasMany(PropertiesDescription::className(), ['properties_id' => 'properties_id'])->andOnCondition(['language_id'=> $languages_id]);
    }

    public function getFrontendname(){
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->hasMany(PropertiesDescription::className(), ['properties_id' => 'properties_id'])->andOnCondition(['language_id'=> $languages_id]);
    }

}

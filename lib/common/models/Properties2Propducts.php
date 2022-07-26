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
 * This is the model class for table "properties_to_products".
 */
class Properties2Propducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'properties_to_products';
    }

    public static function find()
    {
        return new \common\models\queries\Properties2ProductsQuery(get_called_class());
    }
    public function getProperties() {
      return $this->hasOne(Properties::className(), ['properties_id' => 'properties_id']);
    }


    public function getPropertiesDescription() {
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->hasOne(PropertiesDescription::className(), ['properties_id' => 'properties_id'])->andOnCondition(['{{%properties_description}}.language_id' => $languages_id])
          ->joinWith('propertiesUnit')
          ;
    }

 public function getPropertiesDescriptions() {
        return $this->hasOne(PropertiesDescription::className(), ['properties_id' => 'properties_id']);
    }

    public function getPropertiesDescription2() {
        return $this->hasOne(PropertiesDescription::className(), ['properties_id' => 'properties_id'])->andOnCondition(['{{%properties_description}}.language_id' => $languages_id]);
    }

    public function getValues() {
      return $this->hasMany(PropertiesValues::className(), ['properties_id' => 'properties_id', 'values_id' => 'values_id']);
    }

    public function getPropertiesValue() {
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->hasMany(PropertiesValues::className(), ['properties_id' => 'properties_id', 'values_id' => 'values_id'])->andOnCondition(['{{%properties_values}}.language_id' => $languages_id])->indexBy('values_id');
    }

    public function getSearchValues(){
        return $this->hasMany(PropertiesValues::className(), ['properties_id' => 'properties_id', 'values_id' => 'values_id'])->indexBy('language_id');
    }

    public function getSearchDescriptions(){
        return $this->hasMany(PropertiesDescription::className(), ['properties_id' => 'properties_id'])
            ->indexBy('language_id')
            //->select('properties_id, language_id, properties_name, properties_seo_page_name')
            ;
    }

}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ship_options".
 *
 * @property int $ship_orders_id
 * @property int $language_id
 * @property int $platform_id
 * @property string $ship_options_name
 * @property int $restrict_access
 * @property int $sort_order
 */
class ShippingOptions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ship_options';
    }
    
    public static function primaryKey() {
        return ['ship_options_id', 'language_id', 'platform_id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ship_options_id', 'language_id', 'platform_id'], 'required'],
            [['ship_options_id', 'language_id', 'platform_id'], 'integer'],
            [['ship_options_name'], 'string', 'max' => 255],
            [['restrict_access', 'sort_order'], 'default', 'value' => 0],
        ];
    }
    
    public static function create($platform_id, $language_id){
        $option = new static([
            'ship_options_id' => static::getMax($platform_id) + 1 ,
            'platform_id' => (int)$platform_id,
        ]);
        return $option;
    }
    
    public function setName($name){
        $this->ship_options_name = (string)$name;
    }
    
    public function setAccess($restrict_access){
        $this->restrict_access = (int)$restrict_access;
    }
    
    public function setOrder($sort_order){
        $this->sort_order = (int)$sort_order;
    }
    
    public static function getMax($platform_id){
        return static::find()->where(['platform_id' => (int)$platform_id,])->max('ship_options_id');
    }

}

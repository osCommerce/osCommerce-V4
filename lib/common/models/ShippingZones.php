<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ship_zones".
 *
 * @property int $ship_zone_id
 * @property string $ship_zone_name
 * @property string $ship_zone_description
 * @property datetime $last_modified
 * @property datetime $date_added
 * @property int $platform_id
 */
class ShippingZones extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ship_zones';
    }
    
    public static function primaryKey() {
        return ['ship_zone_id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ship_zone_id', 'platform_id'], 'integer'],
            [['ship_zone_name'], 'string', 'max' => 32],
            [['ship_zone_description'], 'string', 'max' => 255],
            [['platform_id'], 'default', 'value' => 0],
        ];
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added', 'last_modified'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['last_modified'],
                ],              
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public static function create(string $name, int $platform_id = 0, string $desc = '') {
        $zone = new static();
        $zone->ship_zone_name = $name;
        $zone->ship_zone_description = $desc;
        $zone->platform_id = $platform_id;
        return $zone;
    }
    
    public static function getMax($platform_id){
        return static::find()->where(['platform_id' => (int)$platform_id,])->max('ship_zone_id');
    }

}

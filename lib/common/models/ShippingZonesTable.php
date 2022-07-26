<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "zone_table".
 *
 * @property int $zone_table_id
 * @property int $ship_zone_id
 * @property int $ship_options_id
 * @property int $country_id
 * @property string $rate
 * @property float $handling_price
 * @property float $per_kg_price
 * @property int $enabled
 * @property int $platform_id
 * @property string $type
 */
class ShippingZonesTable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zone_table';
    }
    
    public static function primaryKey() {
        return ['ship_zone_id', 'ship_options_id', 'enabled', 'platform_id', 'type'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ship_zone_id', 'ship_options_id', 'platform_id', 'type', ], 'required'],
            [['ship_zone_id', 'ship_options_id', 'country_id'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['rate'], 'string', 'max' => 1024],
            [['type'], 'default', 'value' => 'order'],
            [['platform_id', 'mode', 'handling_price', 'per_kg_price', 'enabled'], 'default', 'value' => 0],
        ];
    }
    
    public static function getMax($platform_id){
        return static::find()->where(['platform_id' => (int)$platform_id,])->max('zone_table_id');
    }
    
    public static function create(int $shipZoneId, int $shipOptionId, int $platformId, int $enabled = 0, $type = 'order'){
        $zTable = new static([
            'ship_zone_id' => $shipZoneId,
            'ship_options_id' => $shipOptionId,
            'platform_id' => $platformId,
            'enabled' => $enabled,
            'type' => static::getType($type),
        ]);
        return $zTable;
    }
    
    public static function getType($type){
        return (in_array($type, ['order', 'quote', 'sample']) ? $type : 'order');
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        \common\models\ShippingZoneTableCheckoutNote::deleteAll(['zone_table_id'=>$this->zone_table_id]);

        return true;

    }

}

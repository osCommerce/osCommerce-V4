<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "locations".
 *
 * @property integer $location_id
 * @property integer $block_id
 * @property string $location_name
 * @property integer $parrent_id
 * @property integer $is_final
 * @property integer $warehouse_id
 * @property integer $sort_order
 */
class Locations extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'locations';
    }

    public function getLocationBlock()
    {
        return $this->hasOne(LocationBlocks::className(),['block_id'=>'block_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['block_id', 'parrent_id', 'is_final', 'warehouse_id', 'sort_order'], 'integer'],
            [['location_name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'location_id' => 'Location ID',
            'block_id' => 'Block ID',
            'location_name' => 'Location Name',
            'parrent_id' => 'Parrent ID',
            'is_final' => 'Is Final',
            'warehouse_id' => 'Warehouse ID',
            'sort_order' => 'Sort Order',
        ];
    }
}

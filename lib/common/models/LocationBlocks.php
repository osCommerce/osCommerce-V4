<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "location_blocks".
 *
 * @property integer $block_id
 * @property string $block_name
 */
class LocationBlocks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'location_blocks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['block_name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'block_id' => 'Block ID',
            'block_name' => 'Block Name',
        ];
    }
}

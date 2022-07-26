<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menu_source".
 *
 * @property int $id
 * @property int $platform_id
 * @property int $source_platform_id
 */
class MenuSource extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'platform_id', 'source_platform_id'], 'required'],
            [['id', 'platform_id', 'source_platform_id'], 'integer'],
            [['id', 'platform_id', 'source_platform_id'], 'unique', 'targetAttribute' => ['id', 'platform_id', 'source_platform_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_id' => 'Platform ID',
            'source_platform_id' => 'Source Platform ID',
        ];
    }
}

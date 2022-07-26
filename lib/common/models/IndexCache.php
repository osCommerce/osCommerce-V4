<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "index_cache".
 *
 * @property string $icTable
 * @property string $icKey
 * @property string $icColumn
 * @property int $icLength
 * @property int $icOrder
 * @property int $icUnique
 * @property string $icDateInsert
 */
class IndexCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'index_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['icTable', 'icKey', 'icColumn', 'icLength', 'icOrder', 'icUnique'], 'required'],
            [['icLength', 'icOrder', 'icUnique'], 'integer'],
            [['icDateInsert'], 'safe'],
            [['icTable', 'icKey', 'icColumn'], 'string', 'max' => 255],
            [['icTable', 'icKey', 'icColumn'], 'unique', 'targetAttribute' => ['icTable', 'icKey', 'icColumn']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'icTable' => 'Ic Table',
            'icKey' => 'Ic Key',
            'icColumn' => 'Ic Column',
            'icLength' => 'Ic Length',
            'icOrder' => 'Ic Order',
            'icUnique' => 'Ic Unique',
            'icDateInsert' => 'Ic Date Insert',
        ];
    }
}
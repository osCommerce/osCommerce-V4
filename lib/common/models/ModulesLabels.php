<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "modules_labels".
 *
 * @property int $id
 * @property int $platform_id
 * @property string $code
 * @property string $labels_list
 */
class ModulesLabels extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules_labels';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'labels_list'], 'required'],
            [['platform_id'], 'integer'],
            [['labels_list'], 'string'],
            [['code'], 'string', 'max' => 64],
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
            'code' => 'Code',
            'labels_list' => 'Labels List',
        ];
    }
}

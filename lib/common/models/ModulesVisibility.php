<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "modules_visibility".
 *
 * @property integer $id
 * @property string $code
 * @property string $area
 * @property integer $platform_id
 */
class ModulesVisibility extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modules_visibility';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id'], 'required'],
            [['platform_id'], 'integer'],
            [['area'], 'string'],
            [['code'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'area' => 'Area',
            'platform_id' => 'Platform ID',
        ];
    }
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "module_tokens".
 *
 * @property int $id
 * @property string $class
 * @property string|null $token
 * @property string $valid_until
 * @property int $platform_id
 * @property int $admin_id
 */
class ModuleTokens extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'module_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token'], 'string'],
            [['valid_until'], 'required'],
            [['valid_until'], 'safe'],
            [['platform_id', 'admin_id'], 'integer'],
            [['class'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class' => 'Class',
            'token' => 'Token',
            'valid_until' => 'Valid Until',
            'platform_id' => 'Platform ID',
            'admin_id' => 'Admin ID',
        ];
    }


}

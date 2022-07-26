<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_platforms".
 *
 * @property integer $admin_id
 * @property integer $platform_id
 */
class AdminPlatforms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_platforms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'platform_id'], 'required'],
            [['admin_id', 'platform_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'platform_id' => 'Platform ID',
        ];
    }
}

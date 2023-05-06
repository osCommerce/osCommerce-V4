<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_levels".
 *
 * @property int $access_levels_id
 * @property string $access_levels_name
 * @property string $access_levels_persmissions
 */
class AccessLevels extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_levels';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['access_levels_name', 'access_levels_persmissions'], 'required'],
            [['access_levels_persmissions'], 'string'],
            [['access_levels_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'access_levels_id' => 'Access Levels ID',
            'access_levels_name' => 'Access Levels Name',
            'access_levels_persmissions' => 'Access Levels Persmissions',
        ];
    }

    public function getAdmins()
    {
        return $this->hasMany(Admin::className(), ['access_levels_id' => 'access_levels_id']);
    }
}

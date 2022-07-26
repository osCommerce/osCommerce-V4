<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "modules_groups_settings".
 *
 * @property integer $id
 * @property integer $platform_id
 * @property string $code
 * @property string $group_list
 */
class ModulesGroupsSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modules_groups_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'group_list'], 'required'],
            [['platform_id'], 'integer'],
            [['group_list'], 'string'],
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
            'platform_id' => 'Platform ID',
            'code' => 'Code',
            'group_list' => 'Group List',
        ];
    }
}

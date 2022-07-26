<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_control_list".
 *
 * @property integer $access_control_list_id
 * @property integer $parent_id
 * @property string $access_control_list_key
 * @property integer $sort_order 
 */
class AccessControlList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_control_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order'], 'integer'],
            [['access_control_list_key'], 'required'],
            [['access_control_list_key'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'access_control_list_id' => 'Access Control List ID',
            'parent_id' => 'Parent ID',
            'access_control_list_key' => 'Access Control List Key',
            'sort_order' => 'Sort Order', 
        ];
    }
}

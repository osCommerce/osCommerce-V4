<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "io_project".
 *
 * @property int $project_id
 * @property string $project_code
 * @property int $is_local
 * @property int $department_id
 * @property int $platform_id
 * @property string $description
 */
class IoProject extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'io_project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_code'], 'required'],
            [['is_local', 'department_id', 'platform_id'], 'integer'],
            [['project_code'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['project_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'project_code' => 'Project Code',
            'is_local' => 'Is Local',
            'department_id' => 'Department ID',
            'platform_id' => 'Platform ID',
            'description' => 'Description',
        ];
    }
}

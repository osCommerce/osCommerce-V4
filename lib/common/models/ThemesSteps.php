<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_steps".
 *
 * @property int $steps_id
 * @property int $parent_id
 * @property string $event
 * @property string $data
 * @property string|null $theme_name
 * @property string|null $date_added
 * @property int $active
 * @property int $admin_id
 */
class ThemesSteps extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'themes_steps';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'active', 'admin_id'], 'integer'],
            [['data'], 'required'],
            [['data'], 'string'],
            [['date_added'], 'safe'],
            [['event'], 'string', 'max' => 64],
            [['theme_name'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'steps_id' => 'Steps ID',
            'parent_id' => 'Parent ID',
            'event' => 'Event',
            'data' => 'Data',
            'theme_name' => 'Theme Name',
            'date_added' => 'Date Added',
            'active' => 'Active',
            'admin_id' => 'Admin ID',
        ];
    }
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_templates".
 *
 * @property int $admin_template_id
 * @property int $access_levels_id
 * @property string|null $page
 * @property string|null $template
 */
class AdminTemplates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['access_levels_id'], 'integer'],
            [['page', 'template'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_template_id' => 'Admin Template ID',
            'access_levels_id' => 'Access Levels ID',
            'page' => 'Page',
            'template' => 'Template',
        ];
    }
}

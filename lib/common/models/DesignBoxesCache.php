<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_cache".
 *
 * @property string $block_name
 * @property string $theme_name
 * @property string $json
 * @property string $serialize
 * @property string|null $date_modified
 */
class DesignBoxesCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['block_name', 'theme_name'], 'required'],
            [['date_modified'], 'safe'],
            [['block_name', 'theme_name'], 'string', 'max' => 256],
            [['block_name', 'theme_name'], 'unique', 'targetAttribute' => ['block_name', 'theme_name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'block_name' => 'Block Name',
            'theme_name' => 'Theme Name',
            'json' => 'json',
            'serialize' => 'serialize',
            'date_modified' => 'Date Modified',
        ];
    }
}

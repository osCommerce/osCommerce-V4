<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes".
 *
 * @property integer $id
 * @property string $microtime
 * @property string $theme_name
 * @property string $block_name
 * @property string $widget_name
 * @property string $widget_params
 * @property integer $sort_order
 */
class DesignBoxes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'design_boxes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['theme_name', 'microtime'], 'required'],
            [['block_name', 'widget_name', 'widget_params'], 'string'],
            [['theme_name'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'microtime' => 'microtime',
            'theme_name' => 'Theme Name',
            'block_name' => 'Block Name',
            'widget_name' => 'Widget Name',
            'widget_params' => 'Widget Params',
            'sort_order' => 'Sort Order',
        ];
    }
}

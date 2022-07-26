<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "page_styles".
 *
 * @property int $id
 * @property string $style
 * @property string $type
 * @property int $page_id
 * @property int $platform_id
 */
class PageStyles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page_styles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_id', 'platform_id'], 'integer'],
            [['style', 'type'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'style' => 'Style',
            'type' => 'Type',
            'page_id' => 'Page ID',
            'platform_id' => 'Platform ID',
        ];
    }
}

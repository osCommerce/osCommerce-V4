<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_videos".
 *
 * @property int $video_id
 * @property int $products_id
 * @property string|null $video
 * @property int $language_id
 */
class ProductsVideos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_videos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'language_id'], 'required'],
            [['products_id', 'language_id'], 'integer'],
            [['video'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'video_id' => 'Video ID',
            'products_id' => 'Products ID',
            'video' => 'Video',
            'language_id' => 'Language ID',
        ];
    }
}

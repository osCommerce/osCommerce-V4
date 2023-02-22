<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_languages".
 *
 * @property integer $blang_id
 * @property integer $banners_id
 * @property integer $platform_id
 * @property string $banners_title
 * @property string $banners_url
 * @property string $banners_image
 * @property string $banners_html_text
 * @property integer $language_id
 * @property integer $target
 * @property integer $banner_display
 * @property integer $text_position
 * @property string $svg
 */
class BannersLanguages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banners_languages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['banners_id', 'banners_title', 'banners_url', 'banners_image', 'text_position', 'svg'], 'required'],
            [['banners_id', 'platform_id', 'language_id', 'target', 'banner_display', 'text_position'], 'integer'],
            [['banners_html_text', 'svg'], 'string'],
            [['banners_title', 'banners_image'], 'string', 'max' => 255],
            [['banners_url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'blang_id' => 'Blang ID',
            'banners_id' => 'Banners ID',
            'platform_id' => 'Platform ID',
            'banners_title' => 'Banners Title',
            'banners_url' => 'Banners Url',
            'banners_image' => 'Banners Image',
            'banners_html_text' => 'Banners Html Text',
            'language_id' => 'Language ID',
            'target' => 'Target',
            'banner_display' => 'Banner Display',
            'text_position' => 'Text Position',
            'svg' => 'Svg',
        ];
    }
}

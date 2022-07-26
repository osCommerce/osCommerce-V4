<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "seo_redirects_named".
 *
 * @property int $seo_redirects_named_id
 * @property string $redirects_type
 * @property int $owner_id
 * @property string $old_seo_page_name
 * @property int $language_id
 * @property int $platform_id
 */
class SeoRedirectsNamed extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seo_redirects_named';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'language_id', 'platform_id'], 'integer'],
            [['redirects_type'], 'string', 'max' => 64],
            [['old_seo_page_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'seo_redirects_named_id' => 'Seo Redirects Named ID',
            'redirects_type' => 'Redirects Type',
            'owner_id' => 'Owner ID',
            'old_seo_page_name' => 'Old Seo Page Name',
            'language_id' => 'Language ID',
            'platform_id' => 'Platform ID',
        ];
    }
}

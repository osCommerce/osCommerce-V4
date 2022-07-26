<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "meta_tags".
 *
 * @property string $meta_tags_key
 * @property string $meta_tags_value
 * @property int $language_id
 * @property int $affiliate_id
 * @property int $platform_id
 */
class MetaTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta_tags';
    }

    public static function primaryKey() {
        return ['meta_tags_key', 'language_id', 'platform_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta_tags_key'], 'required'],
            [['meta_tags_value'], 'string'],
            [['language_id', 'affiliate_id', 'platform_id'], 'integer'],
            [['meta_tags_key'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'meta_tags_key' => 'Meta Tags Key',
            'meta_tags_value' => 'Meta Tags Value',
            'language_id' => 'Language ID',
            'affiliate_id' => 'Affiliate ID',
            'platform_id' => 'Platform ID',
        ];
    }
}

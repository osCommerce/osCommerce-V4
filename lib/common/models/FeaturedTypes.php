<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "featured_types".
 *
 * @property int $featured_type_id
 * @property int $language_id
 * @property string $featured_type_name
 */
class FeaturedTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'featured_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['featured_type_id', 'language_id'], 'required'],
            [['featured_type_id', 'language_id'], 'integer'],
            [['featured_type_name'], 'string', 'max' => 64],
            [['featured_type_id', 'language_id'], 'unique', 'targetAttribute' => ['featured_type_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'featured_type_id' => 'Featured Type ID',
            'language_id' => 'Language ID',
            'featured_type_name' => 'Featured Type Name',
        ];
    }
}

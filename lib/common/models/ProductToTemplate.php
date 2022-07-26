<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "product_to_template".
 *
 * @property int $id
 * @property int $products_id
 * @property int $platform_id
 * @property string $theme_name
 * @property string $template_name
 */
class ProductToTemplate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_to_template';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'platform_id', 'theme_name', 'template_name'], 'required'],
            [['products_id', 'platform_id'], 'integer'],
            [['theme_name', 'template_name'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'products_id' => 'Products ID',
            'platform_id' => 'Platform ID',
            'theme_name' => 'Theme Name',
            'template_name' => 'Template Name',
        ];
    }
}

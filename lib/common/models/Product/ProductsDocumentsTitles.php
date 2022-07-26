<?php

namespace common\models\Product;

use Yii;

/**
 * This is the model class for table "products_documents_titles".
 *
 * @property int $products_documents_id
 * @property int $language_id
 * @property string $title
 */
class ProductsDocumentsTitles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_documents_titles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_documents_id', 'language_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['products_documents_id', 'language_id'], 'unique', 'targetAttribute' => ['products_documents_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_documents_id' => 'Products Documents ID',
            'language_id' => 'Language ID',
            'title' => 'Title',
        ];
    }
}

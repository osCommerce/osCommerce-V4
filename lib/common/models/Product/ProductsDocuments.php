<?php

namespace common\models\Product;

use common\models\queries\ProductsDocumentsQuery;

/**
 * This is the model class for table "products_documents".
 *
 * @property int $products_documents_id
 * @property int $products_id
 * @property int $document_types_id
 * @property int $sort_order
 * @property string $filename
 * @property int $is_link
 * @property ProductsDocumentsTitles $title
 * @property ProductsDocumentsTitles[] $titles
 */
class ProductsDocuments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'document_types_id', 'sort_order', 'is_link'], 'integer'],
            [['filename'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_documents_id' => 'Products Documents ID',
            'products_id' => 'Products ID',
            'document_types_id' => 'Document Types ID',
            'sort_order' => 'Sort Order',
            'filename' => 'Filename',
            'is_link' => 'Is Link',
        ];
    }

    /**
     * @return ProductsDocumentsQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new ProductsDocumentsQuery(static::class);
    }

    /**
     * one-to-many all languages
     * @return \yii\db\ActiveQuery
     */
    public function getTitles()
    {
        return $this->hasMany(ProductsDocumentsTitles::class, ['products_documents_id' => 'products_documents_id']);
    }

    /**
     * one-to-one 1 language
     * @param int|null $languageId
     * @return \yii\db\ActiveQuery
     */
    public function getTitle(?int $languageId = null)
    {
        $query =  $this->hasOne(ProductsDocumentsTitles::class, ['products_documents_id' => 'products_documents_id']);
        if ($languageId !== null) {
            $query->andOnCondition([ProductsDocumentsTitles::tableName() . 'language_id' => $languageId]);
        }
        return $query;
    }
}

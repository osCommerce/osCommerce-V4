<?php

namespace common\models\Product;

use common\models\Products;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "products_notes".
 *
 * @property int $products_notes_id
 * @property int $products_id
 * @property string $note
 * @property int $updated_at
 * @property int $created_at
 *
 * @property Products $products
 */
final class ProductsNotes extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_notes';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'note'], 'required'],
            [['products_id', 'updated_at', 'created_at'], 'integer'],
            [['note'], 'string'],
            [['products_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['products_id' => 'products_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_notes_id' => 'Products Notes ID',
            'products_id' => 'Products ID',
            'note' => 'Note',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasOne(Products::class, ['products_id' => 'products_id']);
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return json_decode(json_encode($this), true);
    }
}

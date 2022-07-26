<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "groups_products".
 *
 * @property int $groups_id
 * @property int $products_id
 *
 * @property Groups $groups
 * @property Products $products
 */
class GroupsProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groups_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groups_id', 'products_id'], 'required'],
            [['groups_id', 'products_id'], 'integer'],
            [['groups_id', 'products_id'], 'unique', 'targetAttribute' => ['groups_id', 'products_id']],
            [['groups_id'], 'exist', 'skipOnError' => true, 'targetClass' => Groups::class, 'targetAttribute' => ['groups_id' => 'groups_id']],
            [['products_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['products_id' => 'products_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'groups_id' => 'Groups ID',
            'products_id' => 'Products ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasOne(Groups::class, ['groups_id' => 'groups_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasOne(Products::class, ['products_id' => 'products_id']);
    }
}

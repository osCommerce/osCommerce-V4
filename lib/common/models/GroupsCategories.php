<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "groups_categories".
 *
 * @property int $groups_id
 * @property int $categories_id
 *
 * @property Categories $categories
 * @property Groups $groups
 */
class GroupsCategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groups_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groups_id', 'categories_id'], 'required'],
            [['groups_id', 'categories_id'], 'integer'],
            [['groups_id', 'categories_id'], 'unique', 'targetAttribute' => ['groups_id', 'categories_id']],
            [['categories_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::className(), 'targetAttribute' => ['categories_id' => 'categories_id']],
            [['groups_id'], 'exist', 'skipOnError' => true, 'targetClass' => Groups::className(), 'targetAttribute' => ['groups_id' => 'groups_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'groups_id' => 'Groups ID',
            'categories_id' => 'Categories ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasOne(Categories::className(), ['categories_id' => 'categories_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasOne(Groups::className(), ['groups_id' => 'groups_id']);
    }
}

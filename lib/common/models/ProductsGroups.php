<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_groups".
 *
 * @property int $products_groups_id
 * @property int $language_id
 * @property string|null $products_groups_name
 * @property string|null $date_added
 * @property string|null $date_last_modified
 */
class ProductsGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_id'], 'required'],
            [['language_id'], 'integer'],
            [['date_added', 'date_last_modified'], 'safe'],
            [['products_groups_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_groups_id' => 'Products Groups ID',
            'language_id' => 'Language ID',
            'products_groups_name' => 'Products Groups Name',
            'date_added' => 'Date Added',
            'date_last_modified' => 'Date Last Modified',
        ];
    }
}

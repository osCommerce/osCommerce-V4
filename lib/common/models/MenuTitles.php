<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menu_titles".
 *
 * @property int $id
 * @property int $language_id
 * @property int $item_id
 * @property string $title
 * @property string $link
 */
class MenuTitles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_titles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_id', 'item_id'], 'integer'],
            [['title', 'link'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_id' => 'Language ID',
            'item_id' => 'Item ID',
            'title' => 'Title',
            'link' => 'Link',
        ];
    }

    public function getItems()
    {
        return $this->hasOne(MenuItems::className(), ['id' => 'item_id']);
    }
}

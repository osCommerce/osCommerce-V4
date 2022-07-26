<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menu_items".
 *
 * @property int $id
 * @property int $platform_id
 * @property int $menu_id
 * @property int $parent_id
 * @property string $link
 * @property int $link_id
 * @property string $link_type
 * @property int $target_blank
 * @property int $sub_categories
 * @property string $custom_categories
 * @property string $class
 * @property int $sort_order
 * @property int $no_logged
 * @property int $theme_page_id
 */
class MenuItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'menu_id', 'parent_id', 'link_id', 'target_blank', 'sub_categories', 'sort_order', 'no_logged', 'theme_page_id'], 'integer'],
            [['link', 'custom_categories'], 'string'],
            [['link_type'], 'string', 'max' => 255],
            [['class'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_id' => 'Platform ID',
            'menu_id' => 'Menu ID',
            'parent_id' => 'Parent ID',
            'link' => 'Link',
            'link_id' => 'Link ID',
            'link_type' => 'Link Type',
            'target_blank' => 'Target Blank',
            'sub_categories' => 'Sub Categories',
            'custom_categories' => 'Custom Categories',
            'class' => 'Class',
            'sort_order' => 'Sort Order',
            'no_logged' => 'No Logged',
            'theme_page_id' => 'Theme Page ID',
        ];
    }

    public function getTitles()
    {
        return $this->hasMany(MenuTitles::className(), ['item_id' => 'id']);
    }
}

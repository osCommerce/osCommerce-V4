<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menus".
 *
 * @property int $id
 * @property string|null $menu_name
 * @property string|null $last_modified
 */
class Menus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_name'], 'string'],
            [['last_modified'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menu_name' => 'Menu Name',
            'last_modified' => 'Last Modified',
        ];
    }
}

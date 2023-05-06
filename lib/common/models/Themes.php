<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "themes".
 *
 * @property integer $id
 * @property string $theme_name
 * @property string $title
 * @property string $description
 * @property integer $install
 * @property integer $is_default
 * @property integer $sort_order
 * @property string $parent_theme
 * @property integer $themes_group_id
 */
class Themes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'themes';
    }

    public function getAssignedToPlatforms()
    {
        return $this->hasMany(PlatformThemes::className(), ['theme_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['install', 'is_default', 'sort_order'], 'integer'],
            [['theme_name'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 128],
            [['parent_theme'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'theme_name' => 'Theme Name',
            'title' => 'Title',
            'description' => 'Description',
            'install' => 'Install',
            'is_default' => 'Is Default',
            'sort_order' => 'Sort Order',
            'parent_theme' => 'Parent Theme',
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        \backend\design\Theme::themeRemove($this->theme_name);
        \backend\design\Theme::themeRemove($this->theme_name . '-mobile');

        PlatformThemes::deleteAll(['theme_id'=>$this->id]);

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ( isset($changedAttributes['is_default']) && $this->getAttribute('is_default') ) {
            static::updateAll(['is_default'=>'0'],'theme_name!=:this_theme',['this_theme'=>$this->theme_name]);
        }
        parent::afterSave($insert, $changedAttributes);
    }


}

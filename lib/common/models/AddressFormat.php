<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "reviews".
 *
 * @property integer $address_format_id
 * @property string $address_format
 * @property string $address_summary 
 * @property integer $address_format_title
 */
class AddressFormat extends ActiveRecord
{
    public static function tableName()
    {
        return '{{address_format}}';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'address_format');
    }

    public function afterDelete()
    {
        parent::afterDelete();

        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'address_format');
    }


}

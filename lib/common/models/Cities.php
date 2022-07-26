<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "cities".
 *
 * @property integer $city_id
 * @property integer $city_country_id
 * @property string $city_code
 * @property string $city_name
 */
class Cities extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cities}}';
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        PostalCodes::updateAll([
            'city_id' => 0,
        ], ['city_id'=>$this->city_id]);

        return true;
    }

}

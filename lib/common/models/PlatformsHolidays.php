<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "platforms_holidays".
 *
 * @property integer $platforms_holidays_id
 * @property integer $platform_id
 * @property string $holidate
 */
class PlatformsHolidays extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platforms_holidays';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id'], 'required'],
            [['platform_id'], 'integer'],
            [['holidate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platforms_holidays_id' => 'Platforms Holidays ID',
            'platform_id' => 'Platform ID',
            'holidate' => 'Holidate',
        ];
    }
}

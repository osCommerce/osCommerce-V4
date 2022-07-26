<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "exclusion_devices".
 *
 * @property string $device_id
 * @property string|null $date_add
 */
class ExclusionDevices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'exclusion_devices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id'], 'required'],
            [['date_add'], 'safe'],
            [['device_id'], 'string', 'max' => 32],
            [['device_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'device_id' => 'Device ID',
            'date_add' => 'Date Add',
        ];
    }
    
    public static function cleanupDevices() {
        //self::deleteAll('date_add <= :date_add', [':date_add' => date("Y-m-d") . " 00:00:01"]);// until the end of the day
        self::deleteAll('date_add <= :date_add', [':date_add' => date("Y-m-d H:i:s", strtotime('- 1 hour'))]);
    }
}

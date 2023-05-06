<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "zones".
 *
 * @property integer $zone_id
 * @property integer $zone_country_id
 * @property string $zone_code
 * @property string $zone_name
 */
class Zones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName():string
    {
        return 'zones';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['zone_country_id'], 'integer'],
            [['zone_code', 'zone_name'], 'required'],
            [['zone_code'], 'string', 'max' => 32],
            [['zone_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'zone_id' => 'Zone ID',
            'zone_country_id' => 'Zone Country ID',
            'zone_code' => 'Zone Code',
            'zone_name' => 'Zone Name',
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        Cities::updateAll([
            'city_zone_id' => 0,
        ], ['city_zone_id'=>$this->zone_id]);

        PostalCodes::updateAll([
            'zone_id' => 0,
        ], ['zone_id'=>$this->zone_id]);

        return true;
    }

}

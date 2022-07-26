<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "zones_to_tax_zones".
 *
 * @property integer $association_id
 * @property integer $zone_country_id
 * @property integer $zone_id
 * @property integer $geo_zone_id
 * @property string $last_modified
 * @property string $date_added
 */
class TaxZonesZones extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zones_to_tax_zones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zone_country_id', 'zone_id', 'geo_zone_id'], 'integer'],
            [['last_modified', 'date_added'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'association_id' => 'Association ID',
            'zone_country_id' => 'Zone Country ID',
            'zone_id' => 'Zone ID',
            'geo_zone_id' => 'Geo Zone ID',
            'last_modified' => 'Last Modified',
            'date_added' => 'Date Added',
        ];
    }
}

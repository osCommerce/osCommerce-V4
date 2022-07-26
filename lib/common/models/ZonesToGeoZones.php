<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "zones_to_geo_zones".
 *
 * @property int $association_id
 * @property int $zone_country_id
 * @property int $zone_id
 * @property int $geo_zone_id
 * @property string $last_modified
 * @property string $date_added
 * @property string $postcode_start
 * @property string $postcode_end
 */
class ZonesToGeoZones extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'zones_to_geo_zones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['zone_country_id', 'zone_id', 'geo_zone_id'], 'integer'],
            [['last_modified', 'date_added'], 'safe'],
            [['postcode_start', 'postcode_end'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'association_id' => 'Association ID',
            'zone_country_id' => 'Zone Country ID',
            'zone_id' => 'Zone ID',
            'geo_zone_id' => 'Geo Zone ID',
            'last_modified' => 'Last Modified',
            'date_added' => 'Date Added',
            'postcode_start' => 'Postcode Start',
            'postcode_end' => 'Postcode End',
        ];
    }
}

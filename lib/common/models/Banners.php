<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_new".
 *
 * @property int $banners_id
 * @property string $banners_group
 * @property int $expires_impressions
 * @property string $expires_date
 * @property string $date_scheduled
 * @property string $date_added
 * @property string $date_status_change
 * @property int $status
 * @property int $affiliate_id
 * @property int $sort_order
 * @property string $banner_type
 */
class Banners extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_new';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banners_group', 'sort_order', 'banner_type'], 'required'],
            [['expires_impressions', 'status', 'affiliate_id', 'sort_order'], 'integer'],
            [['expires_date', 'date_scheduled', 'date_added', 'date_status_change'], 'safe'],
            [['banners_group'], 'string', 'max' => 10],
            [['banner_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'banners_id' => 'Banners ID',
            'banners_group' => 'Banners Group',
            'expires_impressions' => 'Expires Impressions',
            'expires_date' => 'Expires Date',
            'date_scheduled' => 'Date Scheduled',
            'date_added' => 'Date Added',
            'date_status_change' => 'Date Status Change',
            'status' => 'Status',
            'affiliate_id' => 'Affiliate ID',
            'sort_order' => 'Sort Order',
            'banner_type' => 'Banner Type',
        ];
    }
}

<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "manufacturers_info".
 *
 * @property integer $manufacturers_id
 * @property integer $languages_id
 * @property string $manufacturers_url
 * @property integer $url_clicked
 * @property string $date_last_click
 * @property string $manufacturers_meta_description
 * @property string $manufacturers_meta_key
 * @property string $manufacturers_meta_title
 * @property string $manufacturers_seo_name
 * @property string $manufacturers_description
 * @property string $manufacturers_h1_tag
 * @property string $manufacturers_h2_tag
 * @property string $manufacturers_h3_tag
 */
class ManufacturersInfo extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manufacturers_info}}';
    }

}

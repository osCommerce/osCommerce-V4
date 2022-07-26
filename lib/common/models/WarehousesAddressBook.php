<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouses_address_book".
 *
 * @property integer $warehouses_address_book_id
 * @property integer $warehouse_id
 * @property integer $is_default
 * @property string $entry_company
 * @property string $entry_company_vat
 * @property string $entry_company_reg_number
 * @property string $entry_postcode
 * @property string $entry_street_address
 * @property string $entry_suburb
 * @property string $entry_city
 * @property string $entry_state
 * @property integer $entry_country_id
 * @property integer $entry_zone_id
 * @property double $lat
 * @property double $lng
 */
class WarehousesAddressBook extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_address_book';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'is_default', 'entry_country_id', 'entry_zone_id'], 'integer'],
            [['is_default', 'lat', 'lng'], 'required'],
            [['lat', 'lng'], 'number'],
            [['entry_company', 'entry_suburb', 'entry_city', 'entry_state'], 'string', 'max' => 32],
            [['entry_company_vat', 'entry_company_reg_number'], 'string', 'max' => 128],
            [['entry_postcode'], 'string', 'max' => 10],
            [['entry_street_address'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'warehouses_address_book_id' => 'Warehouses Address Book ID',
            'warehouse_id' => 'Warehouse ID',
            'is_default' => 'Is Default',
            'entry_company' => 'Entry Company',
            'entry_company_vat' => 'Entry Company Vat',
            'entry_company_reg_number' => 'Entry Company Reg Number',
            'entry_postcode' => 'Entry Postcode',
            'entry_street_address' => 'Entry Street Address',
            'entry_suburb' => 'Entry Suburb',
            'entry_city' => 'Entry City',
            'entry_state' => 'Entry State',
            'entry_country_id' => 'Entry Country ID',
            'entry_zone_id' => 'Entry Zone ID',
            'lat' => 'Lat',
            'lng' => 'Lng',
        ];
    }

    public function getCountry(){
        $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasOne(Countries::className(), ['countries_id' => 'entry_country_id'])
                ->where([Countries::tableName().'.language_id' => (int)$languages_id]);
    }

}

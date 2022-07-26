<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupons_description".
 *
 * @property int $coupon_id
 * @property int $language_id
 * @property string $coupon_name
 * @property string $coupon_description
 */
class CouponsCustomerCodesList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coupons_customercodes_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customercode_id', 'coupon_id'], 'integer'],
            [['coupon_code', 'only_for_customer'], 'string'],   
            ['date_added', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'coupon_id' => 'Coupon ID',
            'only_for_customer' => 'Only For Customer',
            'coupon_code' => 'Coupon Code',           
        ];
    }
}

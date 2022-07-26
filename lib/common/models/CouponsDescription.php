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
class CouponsDescription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coupons_description';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['coupon_id', 'language_id'], 'integer'],
            [['coupon_name'], 'required'],
            [['coupon_description'], 'string'],
            [['coupon_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'coupon_id' => 'Coupon ID',
            'language_id' => 'Language ID',
            'coupon_name' => 'Coupon Name',
            'coupon_description' => 'Coupon Description',
        ];
    }
}

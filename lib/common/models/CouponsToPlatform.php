<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupons_to_platform".
 *
 * @property int $platform_id
 * @property int $coupon_id
 *
 * @property Coupons $coupon
 * @property Platforms $platform
 */
class CouponsToPlatform extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupons_to_platform';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'coupon_id'], 'required'],
            [['platform_id', 'coupon_id'], 'integer'],
            [['platform_id', 'coupon_id'], 'unique', 'targetAttribute' => ['platform_id', 'coupon_id']],
            [['platform_id'], 'exist', 'skipOnError' => true, 'targetClass' => Platforms::className(), 'targetAttribute' => ['platform_id' => 'platform_id']],
            [['coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coupons::className(), 'targetAttribute' => ['coupon_id' => 'coupon_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'coupon_id' => 'Coupon ID',
        ];
    }

    /**
     * Gets query for [[Coupon]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupons::className(), ['coupon_id' => 'coupon_id']);
    }

    /**
     * Gets query for [[Platform]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::className(), ['platform_id' => 'platform_id']);
    }
}

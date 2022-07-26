<?php

namespace frontend\models\repositories;

use common\models\Coupons;

class CouponsRepository 
{
    /**
     * 
     * @param int $gvId
     * @return array
     */
    public function getCouponAmount(int $gvId)
    {
        $coupon = Coupons::find()->select(['coupon_amount', 'coupon_currency'])->where(['coupon_id' => $gvId])->asArray()->one();
        if (!$coupon) {
            throw new NotFoundException('Coupon is not found.');
        }
        return $coupon;
    }
}

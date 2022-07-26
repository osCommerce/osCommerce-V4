<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */


namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class CouponRedeemTrack extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coupon_redeem_track';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['redeem_date'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public function getOrder(){
        return $this->hasOne(Orders::class, ['orders_id' => 'order_id'])->select(['orders_id']);
    }
    
    public function getCoupon(){
        return $this->hasOne(Coupons::class, ['coupon_id' => 'coupon_id']);
    }
    
}

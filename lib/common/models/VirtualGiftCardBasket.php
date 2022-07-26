<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "virtual_gift_card_basket".
 *
 * @property integer $virtual_gift_card_basket_id
 * @property integer $customers_id
 * @property string $session_id
 * @property integer $currencies_id
 * @property integer $products_id
 * @property float $products_price
 * @property string $virtual_gift_card_recipients_name
 * @property string $virtual_gift_card_recipients_email
 * @property string $virtual_gift_card_message
 * @property string $virtual_gift_card_senders_name
 * @property string $virtual_gift_card_code
 * @property datetime send_card_date
 * @property datetime activated
 */
class VirtualGiftCardBasket extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%virtual_gift_card_basket}}';
    }
    
    public function activate(){
        $this->activated = 1;
        if ($this->save(false)){
            if ($this->coupon){
                $this->coupon->coupon_active = Coupons::STATUS_ACTIVE;
                $this->coupon->save();
            }
            return true;
        }
        return false;
    }
    
    public function getProduct(){
        return $this->hasOne(Products::class, ['products_id' => 'products_id']);
    }
    
    public function getCoupon(){
        return $this->hasOne(Coupons::class, ['coupon_code' => 'virtual_gift_card_code']);
    }
}

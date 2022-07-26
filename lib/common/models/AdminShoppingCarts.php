<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "admin_shopping_carts".
 *
 * @property integer $admin_id
 * @property integer $basket_id
 * @property integer $customers_id 
 * @property integer $platform_id
 * @property integer $order_id
 * @property integer $status
 * @property void $customer_basket
 * @property date $updated_at
 * @property string $cart_type
 * @property void $checkout_details
 */
class AdminShoppingCarts extends ActiveRecord
{
    public static function tableName()
    {
        return '{{admin_shopping_carts}}';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
}

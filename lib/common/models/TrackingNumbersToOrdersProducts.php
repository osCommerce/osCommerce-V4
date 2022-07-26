<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "tracking_numbers_to_orders_products".
 *
 * @property integer $tracking_numbers_id
 * @property integer $orders_id
 * @property integer $orders_products_id
 * @property integer $products_quantity
 */
class TrackingNumbersToOrdersProducts extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tracking_numbers_to_orders_products}}';
    }
}

<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "virtual_gift_card_basket".
 *
 * @property integer $products_id
 * @property integer $currencies_id
 * @property float $products_price
 * @property float $products_discount_price
 */
class VirtualGiftCardPrices extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%virtual_gift_card_prices}}';
    }
}

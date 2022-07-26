<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "gift_wrap_products".
 *
 * @property int $gw_id
 * @property int $products_id
 * @property int $groups_id
 * @property int $currencies_id
 * @property float $gift_wrap_price
 */
class GiftWrapProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gift_wrap_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'groups_id', 'currencies_id'], 'integer'],
            [['gift_wrap_price'], 'number'],
            [['products_id', 'groups_id', 'currencies_id'], 'unique', 'targetAttribute' => ['products_id', 'groups_id', 'currencies_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'gw_id' => 'Gw ID',
            'products_id' => 'Products ID',
            'groups_id' => 'Groups ID',
            'currencies_id' => 'Currencies ID',
            'gift_wrap_price' => 'Gift Wrap Price',
        ];
    }
}

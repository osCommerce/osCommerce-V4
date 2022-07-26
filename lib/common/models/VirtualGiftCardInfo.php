<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "virtual_gift_card_info".
 *
 * @property int $virtual_gift_card_info_id
 * @property int|null $customers_id
 * @property int|null $currencies_id
 * @property int|null $products_id
 * @property float $products_price
 * @property float $products_discount_price
 * @property string $virtual_gift_card_recipients_name
 * @property string $virtual_gift_card_recipients_email
 * @property string $virtual_gift_card_message
 * @property string $virtual_gift_card_senders_name
 * @property string $virtual_gift_card_code
 * @property string $send_card_date
 * @property string|null $gift_card_design
 */
class VirtualGiftCardInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'virtual_gift_card_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customers_id', 'currencies_id', 'products_id'], 'integer'],
            [['products_price', 'products_discount_price', 'virtual_gift_card_recipients_name', 'virtual_gift_card_recipients_email', 'virtual_gift_card_message', 'virtual_gift_card_senders_name', 'virtual_gift_card_code', 'send_card_date'], 'required'],
            [['products_price', 'products_discount_price'], 'number'],
            [['send_card_date'], 'safe'],
            [['virtual_gift_card_recipients_name', 'virtual_gift_card_senders_name'], 'string', 'max' => 64],
            [['virtual_gift_card_recipients_email'], 'string', 'max' => 96],
            [['virtual_gift_card_message'], 'string', 'max' => 255],
            [['virtual_gift_card_code'], 'string', 'max' => 32],
            [['gift_card_design'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'virtual_gift_card_info_id' => 'Virtual Gift Card Info ID',
            'customers_id' => 'Customers ID',
            'currencies_id' => 'Currencies ID',
            'products_id' => 'Products ID',
            'products_price' => 'Products Price',
            'products_discount_price' => 'Products Discount Price',
            'virtual_gift_card_recipients_name' => 'Virtual Gift Card Recipients Name',
            'virtual_gift_card_recipients_email' => 'Virtual Gift Card Recipients Email',
            'virtual_gift_card_message' => 'Virtual Gift Card Message',
            'virtual_gift_card_senders_name' => 'Virtual Gift Card Senders Name',
            'virtual_gift_card_code' => 'Virtual Gift Card Code',
            'send_card_date' => 'Send Card Date',
            'gift_card_design' => 'Gift Card Design',
        ];
    }
}

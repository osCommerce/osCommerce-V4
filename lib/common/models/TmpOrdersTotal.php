<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tmp_orders_total".
 *
 * @property int $orders_total_id
 * @property int $orders_id
 * @property string $title
 * @property string $text
 * @property float $value
 * @property string $class
 * @property int $sort_order
 * @property string|null $text_inc_tax
 * @property string|null $text_exc_tax
 * @property int $tax_class_id
 * @property float $value_inc_tax
 * @property float $value_exc_vat
 * @property int|null $is_removed
 * @property string $currency
 * @property float $currency_value
 */
class TmpOrdersTotal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmp_orders_total';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_id', 'sort_order', 'tax_class_id', 'is_removed'], 'integer'],
            [['value', 'value_inc_tax', 'value_exc_vat', 'currency_value'], 'number'],
            [['title', 'text', 'text_inc_tax', 'text_exc_tax'], 'string', 'max' => 255],
            [['class'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_total_id' => 'Orders Total ID',
            'orders_id' => 'Orders ID',
            'title' => 'Title',
            'text' => 'Text',
            'value' => 'Value',
            'class' => 'Class',
            'sort_order' => 'Sort Order',
            'text_inc_tax' => 'Text Inc Tax',
            'text_exc_tax' => 'Text Exc Tax',
            'tax_class_id' => 'Tax Class ID',
            'value_inc_tax' => 'Value Inc Tax',
            'value_exc_vat' => 'Value Exc Vat',
            'is_removed' => 'Is Removed',
            'currency' => 'Currency',
            'currency_value' => 'Currency Value',
        ];
    }
}

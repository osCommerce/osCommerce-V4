<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shipping_np_order_params".
 *
 * @property int $orders_id
 * @property string $name
 * @property string $value
 * @property array $valueData
 * @property string $type
 */
class ShippingNpOrderParams extends \yii\db\ActiveRecord
{
    public $valueData = [];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipping_np_order_params';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_id', 'type'], 'required'],
            [['orders_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['value'], 'string'],
            [['type'], 'string', 'max' => 96],
            [['orders_id', 'type'], 'unique', 'targetAttribute' => ['orders_id', 'type']],
        ];
    }
    public function afterFind()
    {
        parent::afterFind();
        $this->valueData = json_decode($this->value, true);
    }
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "currencies".
 *
 * @property int $currencies_id
 * @property string $title
 * @property string $code
 * @property string $symbol_left
 * @property string $symbol_right
 * @property string $decimal_point
 * @property string $thousands_point
 * @property string $decimal_places
 * @property double $value
 * @property string $last_updated
 * @property int $sort_order
 * @property int $status
 * @property string $nominals
 */
class Currencies extends \yii\db\ActiveRecord
{
    /**
     * @var array $nominalsVal
     */
    public $nominalsVal;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currencies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'code'], 'required'],
            [['value'], 'number'],
            [['last_updated'], 'safe'],
            [['sort_order', 'status'], 'integer'],
            [['title'], 'string', 'max' => 32],
            [['code'], 'string', 'max' => 3],
            [['symbol_left', 'symbol_right'], 'string', 'max' => 12],
            [['decimal_point', 'thousands_point', 'decimal_places'], 'string', 'max' => 1],
            [['nominals'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currencies_id' => 'Currencies ID',
            'title' => 'Title',
            'code' => 'Code',
            'symbol_left' => 'Symbol Left',
            'symbol_right' => 'Symbol Right',
            'decimal_point' => 'Decimal Point',
            'thousands_point' => 'Thousands Point',
            'decimal_places' => 'Decimal Places',
            'value' => 'Value',
            'last_updated' => 'Last Updated',
            'sort_order' => 'Sort Order',
            'status' => 'Status',
            'nominals' => 'Nominals',
            'nominalsVal' => 'Nominals',
        ];
    }
    public function afterFind()
    {
        parent::afterFind();
        $this->nominalsVal = explode(',',$this->nominals);
    }
}

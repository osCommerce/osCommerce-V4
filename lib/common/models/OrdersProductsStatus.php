<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_products_status".
 *
 * @property integer $orders_products_status_id
 * @property integer $language_id
 * @property string $orders_products_status_name
 * @property string $orders_products_status_colour
 */
class OrdersProductsStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_products_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_products_status_id', 'language_id', 'orders_products_status_name'], 'required'],
            [['orders_products_status_id', 'language_id'], 'integer'],
            [['orders_products_status_name'], 'string', 'max' => 32],
            [['orders_products_status_name_long'], 'string', 'max' => 64],
            [['orders_products_status_colour'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orders_products_status_id' => 'Orders Products Status ID',
            'language_id' => 'Language ID',
            'orders_products_status_name' => 'Orders Products Status Name',
            'orders_products_status_name_long' => 'Orders Products Status Name Long',
            'orders_products_status_colour' => 'Orders Products Status Colour',
        ];
    }

    /**
     * Returning language independent colour of Order product status
     * @return string colour HEX
     */
    public function getColour()
    {
        $return = '#000000';
        if ($this->orders_products_status_id > 0) {
            $opsRecord = self::find()
                ->where(['orders_products_status_id' => $this->orders_products_status_id])
                ->andWhere(['NOT IN', 'orders_products_status_colour', ['', '#000000']])
                ->one();
            if ($opsRecord) {
                $return = $opsRecord->orders_products_status_colour;
            }
            unset($opsRecord);
        }
        return $return;
    }

    /**
     * Returning array of OrdersProductsStatusManual objects
     * @global integer $languages_id
     * @return array of instances of OrdersProductsStatusManual
     */
    public function getMatrixArray($asArray = false)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $return = [];
        foreach ($this->hasMany(OrdersProductsStatusManualMatrix::className(), ['orders_products_status_id' => 'orders_products_status_id'])->all() as $opsmmRecord) {
            $opsmRecord = OrdersProductsStatusManual::findOne(['orders_products_status_manual_id' => $opsmmRecord->orders_products_status_manual_id, 'language_id' => $languages_id]);
            if (!$opsmRecord) {
                $opsmRecord = OrdersProductsStatusManual::findOne(['orders_products_status_manual_id' => $opsmmRecord->orders_products_status_manual_id]);
            }
            if (!$opsmRecord) {
                continue;
            }
            $return[$opsmRecord->orders_products_status_manual_id] = ($asArray != false ? $opsmRecord->toArray() : $opsmRecord);
        }
        return $return;
    }

    /**
     * Totally replacing relation matrix based on relation of Order product status to Manual Order product status
     * @param array $matrixArray - array of Manual Order product status id's
     * @return true | string with error message
     */
    public function setMatrixArray(array $matrixArray = array())
    {
        $return = true;
        OrdersProductsStatusManualMatrix::deleteAll(['orders_products_status_id' => $this->orders_products_status_id]);
        foreach ($matrixArray as $opsmId) {
            if ($opsmId > 0) {
                $opsmmRecord = new OrdersProductsStatusManualMatrix();
                $opsmmRecord->orders_products_status_manual_id = $opsmId;
                $opsmmRecord->orders_products_status_id = $this->orders_products_status_id;
                try {
                    $opsmmRecord->save();
                } catch (\Exception $exc) {
                    $return = $exc->getMessage();
                }
            }
        }
        return $return;
    }
}

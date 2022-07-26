<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_products_status_manual".
 *
 * @property integer $orders_products_status_manual_id
 * @property integer $language_id
 * @property string $orders_products_status_manual_name
 * @property string $orders_products_status_manual_name_long
 * @property string $orders_products_status_manual_colour
 */
class OrdersProductsStatusManual extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_products_status_manual';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_products_status_manual_id', 'language_id', 'orders_products_status_manual_name', 'orders_products_status_manual_name_long'], 'required'],
            [['orders_products_status_manual_id', 'language_id'], 'integer'],
            [['orders_products_status_manual_name'], 'string', 'max' => 32],
            [['orders_products_status_manual_name_long'], 'string', 'max' => 64],
            [['orders_products_status_manual_colour'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orders_products_status_manual_id' => 'Orders Products Status Manual ID',
            'language_id' => 'Language ID',
            'orders_products_status_manual_name' => 'Orders Products Status Manual Name',
            'orders_products_status_manual_name_long' => 'Orders Products Status Manual Name Long',
            'orders_products_status_manual_colour' => 'Orders Products Status Manual Colour',
        ];
    }

    /**
     * Returning language independent colour of Manual Order product status
     * @return string colour HEX
     */
    public function getColour()
    {
        $return = '#000000';
        if ($this->orders_products_status_manual_id > 0) {
            $opsmRecord = self::find()
                ->where(['orders_products_status_manual_id' => $this->orders_products_status_manual_id])
                ->andWhere(['NOT IN', 'orders_products_status_manual_colour', ['', '#000000']])
                ->one();
            if ($opsmRecord) {
                $return = $opsmRecord->orders_products_status_manual_colour;
            }
            unset($opsmRecord);
        }
        return $return;
    }

    /**
     * Returning array of OrdersProductsStatus objects
     * @global integer $languages_id
     * @return array of instances of OrdersProductsStatus
     */
    public function getMatrixArray($asArray = false)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $return = [];
        foreach ($this->hasMany(OrdersProductsStatusManualMatrix::className(), ['orders_products_status_manual_id' => 'orders_products_status_manual_id'])->all() as $opsmmRecord) {
            $opsRecord = OrdersProductsStatus::findOne(['orders_products_status_id' => $opsmmRecord->orders_products_status_id, 'language_id' => $languages_id]);
            if (!$opsRecord) {
                $opsRecord = OrdersProductsStatus::findOne(['orders_products_status_id' => $opsmmRecord->orders_products_status_id]);
            }
            if (!$opsRecord) {
                continue;
            }
            $return[$opsRecord->orders_products_status_id] = ($asArray != false ? $opsRecord->toArray() : $opsRecord);
        }
        return $return;
    }

    /**
     * Totally replacing relation matrix based on relation of Manual Order product status to Order product status
     * @param array $matrixArray - array of Order product status id's
     * @return true | string with error message
     */
    public function setMatrixArray(array $matrixArray = array())
    {
        $return = true;
        OrdersProductsStatusManualMatrix::deleteAll(['orders_products_status_manual_id' => $this->orders_products_status_manual_id]);
        foreach ($matrixArray as $opsId) {
            if ($opsId > 0) {
                $opsmmRecord = new OrdersProductsStatusManualMatrix();
                $opsmmRecord->orders_products_status_manual_id = $this->orders_products_status_manual_id;
                $opsmmRecord->orders_products_status_id = $opsId;
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

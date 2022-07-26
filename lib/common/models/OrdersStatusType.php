<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_status_type".
 *
 * @property int $orders_status_type_id
 * @property int $language_id
 * @property string $orders_status_type_name
 * @property string $orders_status_type_color
 * @property array |  OrdersStatusGroups $groups
 */
class OrdersStatusType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_status_type';
    }


    public function getGroups(){
    	$languages_id = \Yii::$app->settings->get('languages_id');
	    return $this->hasMany(OrdersStatusGroups::className(), ['orders_status_type_id' => 'orders_status_type_id'])
	                ->where([OrdersStatusGroups::tableName() . '.language_id' => $languages_id]);
    }



}

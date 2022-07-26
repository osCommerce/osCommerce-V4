<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_status_groups".
 *
 * @property int $orders_status_groups_id
 * @property int $language_id
 * @property string $orders_status_groups_name
 * @property string $orders_status_groups_color
 * @property int $orders_status_type_id
 */
class OrdersStatusGroups extends \yii\db\ActiveRecord
{
    const NEW_GROUP = 1;
    const PROCESSING_GROUP = 2;
    const INCOMPLETE_GROUP = 3;
    const COMPLETE_GROUP = 4;
    const CANCELLED_GROUP = 5;
    const SUBSCRIPTION_GROUP = 6;
    const QUOTATION_GROUP = 7;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_status_groups';
    }


    public function getStatuses(){
	    $languages_id = \Yii::$app->settings->get('languages_id');
	    return $this->hasMany(OrdersStatus::className(), ['orders_status_groups_id' => 'orders_status_groups_id'])->where([OrdersStatus::tableName() . '.language_id' => $languages_id]);
    }



}

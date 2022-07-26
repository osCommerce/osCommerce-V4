<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "tracking_numbers".
 *
 * @property integer $tracking_numbers_id
 * @property integer $orders_id
 * @property integer $tracking_carriers_id
 * @property string $tracking_number
 */
class TrackingNumbers extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tracking_numbers}}';
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        TrackingNumbersToOrdersProducts::deleteAll(['tracking_numbers_id'=>$this->tracking_numbers_id]);

        return true;
    }


}

<?php

namespace common\models;

use yii\db\ActiveRecord;

class OrdersParent extends ActiveRecord
{
    public static function tableName()
    {
        return 'orders_parent';
    }
    
    public static function primaryKey(){
        return ['orders_id'];
    }
}

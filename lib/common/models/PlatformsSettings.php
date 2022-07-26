<?php

namespace common\models;

use yii\db\ActiveRecord;

class PlatformsSettings extends ActiveRecord
{
    public static function tableName()
    {
        return '{{platforms_settings}}';
    }
    
    public function beforeDelete() {
        //2do: delete prices for this platform 
        return parent::beforeDelete();
    }
    
    public function getPlatform(){
        return $this->hasOne(Platforms::className(), ['platform_id' => 'platform_id']);
    }
   
}

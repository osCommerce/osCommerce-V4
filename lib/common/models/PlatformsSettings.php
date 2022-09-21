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

    public function afterSave($insert, $changedAttributes) {
        if (!empty($changedAttributes)) {
            $newAttributes = $this->getAttributes();
            foreach (\common\helpers\Hooks::getList('platforms-settings/after-save') as $filename) {
                include($filename);
            }
        }
        return parent::afterSave($insert, $changedAttributes);

    }
   
}

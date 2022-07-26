<?php

namespace common\models;

class Restriction extends \yii\db\ActiveRecord{
    
    public static function tableName() {
        return 'forbidden';
    }
    
    public static function primaryKey() {
        return ['forbidden_id'];
    }
    
    public function rules() {
        return [
          ['forbidden_address', 'required'],  
          ['forbidden_address', 'vaidateIP'],
        ];
    }
    
    public function vaidateIP($attribute, $params){
        
        if (!filter_var($this->getAttribute($attribute), FILTER_VALIDATE_IP) || empty($this->$attribute)){
            $this->addError($attribute, 'Invalid IP address');
        }
    }
    
    public static function verifyAddress(){
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['forbidden_address' => $remote_ip])->one();
        if ($oops){
            header("HTTP/1.0 403 Forbidden");
            echo '<h1>403 Forbidden</h1>';
            exit;
        }
    }
}
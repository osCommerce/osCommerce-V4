<?php

namespace common\models;

class Fraud extends \yii\db\ActiveRecord{
    
    public static function tableName() {
        return 'fraud';
    }
    
    public static function primaryKey() {
        return ['fraud_address'];
    }
    
    public function rules() {
        return [
          ['fraud_address', 'required'],
          ['fraud_address', 'vaidateIP'],
        ];
    }
    
    public function vaidateIP($attribute, $params){
        
        if (!filter_var($this->getAttribute($attribute), FILTER_VALIDATE_IP) || empty($this->$attribute)){
            $this->addError($attribute, 'Invalid IP address');
        }
    }
    
    public static function verifyAddress() {
        self::cleanupAddress();
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['fraud_address' => $remote_ip])->one();
        if ($oops){
            if ($oops->fraud_counter > 2) {
                return true;
            }
        }
        return false;
    }
    
    public static function underSurveillanceAddress() {
        self::cleanupAddress();
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['fraud_address' => $remote_ip])->one();
        if ($oops){
            if ($oops->fraud_counter > 0) {
                return true;
            }
        }
        return false;
    }
    
    public static function blockAddress() {
        self::cleanupAddress();
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['fraud_address' => $remote_ip])->one();
        if ($oops){
            if ($oops->fraud_counter > 9) {
                return true;
            }
        }
        return false;
    }
    
    public static function registerAddress() {
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['fraud_address' => $remote_ip])->one();
        if ($oops){
            $oops->fraud_counter += 1;
        } else {
            $oops = new Fraud();
            $oops->fraud_address = $remote_ip;
            $oops->fraud_counter = 1;
        }
        $oops->fraud_date = date("Y-m-d H:i:s");
        $oops->save();
    }
    
    public static function cleanupAddress() {
        self::deleteAll('fraud_date <= :fraud_date', [':fraud_date' => date("Y-m-d H:i:s", strtotime('- 1 hour'))]);
    }
    
    public static function cleanAddress() {
        $remote_ip = \common\helpers\System::get_ip_address();
        $oops = self::find()->where(['fraud_address' => $remote_ip])->one();
        if ($oops){
            $oops->delete();
        }
    }
        
}
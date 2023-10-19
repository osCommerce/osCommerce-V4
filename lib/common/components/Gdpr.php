<?php

/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\components;

use Yii;
use common\models\GdprCheck;
use common\models\YoungCustomers;
use common\helpers\Date as DateHelper;

class Gdpr {
    
    private $entity = null;
    private $gdprCheck = null;
    private $todayDate;
    private $dobDate;
    private $todayDateTime = null;
    private $dobDateTime = null;
    private $error = false;
    private $mistake = false;
    private $message = '';    
    private $email = null;
    
    public function __construct($userIdentity = null) {
        if ($userIdentity instanceof \common\models\Customers ){
            $this->entity = $userIdentity;
            $this->setDobDate($this->entity->customers_dob);
        }        
        $this->setTodayDate();
    }
    
    public function validToken($token){
        $this->gdprCheck = GdprCheck::findOne(['token' => $token]);        
        return $this->gdprCheck;
    }
    
    public function getTokenEntity(){
        return $this->gdprCheck;
    }
    
    public function getEntity(){
        return $this->entity;
    }

    public function isValidGdpr(){        
        return ;
    }
    
    public function getError(){
        return $this->error;
    }
    
    public function getMessage(){
        return $this->message;
    }
    
    public function hasMistake(){
        return $this->mistake;
    }
    
    public function setEmail($email){
        $this->email = $email;
    }

    public function setDobDate($date){
        if (!empty($date) && $date != '0000-00-00 00:00:00') {
            if (checkdate(date('m', strtotime($date)), date('d', strtotime($date)), date('Y', strtotime($date)))) {
                $this->dobDate = date('Y-m-d', strtotime($date));
                return true;
            }
        }
        $this->error = true;
        $this->message = ENTRY_DATE_OF_BIRTH_ERROR;
        return false;
    }
    
    public function setTodayDate(){
        $this->todayDate = date('Y-m-d');
    }

    public function getTDifference(){
        if (is_null($this->dobDateTime))
            $this->dobDateTime = new \DateTime($this->dobDate);
        if (is_null($this->todayDateTime))
            $this->todayDateTime = new \DateTime($this->todayDate);
        return $this->dobDateTime->diff($this->todayDateTime);
    }

    public function isFraud(){
        $fraud = false;        
        if (!empty($this->dobDate) && $this->dobDate != '0000-00-00 00:00:00') {
            $difference = $this->getTDifference();
            $fraud = $difference->invert == 1 || $difference->y < 13 ? true : false;                
        }
        return $fraud;
    }
    
    public function generateToken(){
        do {
            $new_token = \common\helpers\Password::create_random_value(32);
            $checkToken = GdprCheck::find()->where(['token' => $new_token])->one();
        } while ($checkToken);
        return $new_token;
    }
    
    public function saveGdprCheck(){
        if ($this->entity){
            $uGdpr = new GdprCheck();
            $token = $this->generateToken();
            $uGdpr->setAttributes([
                'customers_id' => $this->entity->customers_id,
                'email' => $this->entity->customers_email_address,
                'token' => $token,
            ], false);
            $uGdpr->save(false);
            return $token;
        }
        return false;
    }
    
    public function getGdprToken(){
        if ($this->entity){
            $gdprCheck = GdprCheck::findOne($this->entity->customers_id);
            if (!$gdprCheck){                            
                $token = $this->saveGdprCheck();
            } else {
                $token = $gdprCheck->token;
            }
            return $token;
        }
        return false;
    }

    //account login
    function processGdprChecking(){
        if (ACCOUNT_GDPR == 'true' && in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && !$this->entity->dob_flag) {
            if (empty($this->entity->customers_dob) || $this->entity->customers_dob == '0000-00-00 00:00:00' || $this->isFraud()) {
                $new_token = $this->getGdprToken();
                if (Yii::$app->request->isAjax){
                    global $messageStack;
                    if (is_object($messageStack)) {
                        $messageStack->add_session('login', ENTRY_DATE_OF_BIRTH_ERROR);
                    }
                    tep_redirect(tep_href_link('account/update', 'token=' . $new_token, 'SSL'));
                } else {
                    tep_redirect(tep_href_link('account/update', 'token=' . $new_token, 'SSL'));
                }
                exit();
            }
        }
    }
    
    private function addInterval(\DateTime $date, $period){
        $date->add($period);
    }


    public function validateGdpr(){  
        if (ACCOUNT_GDPR == 'true' && $this->dobDate){
            $difference = $this->getTDifference();
            if ($difference->invert == 1) {
                $this->message = ENTRY_DATE_OF_BIRTH_ERROR;
                $this->mistake = true;
            } elseif ($difference->y < 13) {
                $this->addInterval($this->dobDateTime, new \DateInterval( "P13Y" ));
                $this->addInterval($this->todayDateTime, new \DateInterval( "P1Y" ));
                $difference = $this->getTDifference();
                if ($difference->invert == 1) {
                    $ban_period = $this->todayDateTime->format('Y-m-d');
                } else {
                    $ban_period = $this->dobDateTime->format('Y-m-d');
                }
                $this->error = true;
                $this->message = ENTRY_DATE_OF_BIRTH_RESTRICTION;
                if ($this->gdprCheck){
                    \common\models\YoungCustomers::deleteAll(['email' => md5($this->gdprCheck->email)]);
                    $this->banUser($this->gdprCheck->email, $ban_period);
                    \common\helpers\Customer::deleteCustomer($this->gdprCheck->customers_id);
                    $this->gdprCheck->delete();
                } else {
                    if (!is_null($this->email)){
                        \common\models\YoungCustomers::deleteAll(['email' => md5($this->email)]);
                        $this->banUser($this->email, $ban_period);
                    }
                }
            }
        }
        $this->afterValidation();
    }
    
    public function afterValidation(){
        if (!$this->error && !$this->mistake){            
            if ($this->gdprCheck){
                \common\models\YoungCustomers::deleteAll(['email' => md5($this->gdprCheck->email)]);
                $this->gdprCheck->delete();            
            }
        }
    }

    public function banUser($email, $period){
        $ban = new \common\models\YoungCustomers();
        $ban->email = md5($email);
        $ban->expiration_date = $period;        
        $ban->save();        
    }
    
    public function isBanned(){
        if ($this->gdprCheck){
            return is_object(\common\models\YoungCustomers::find()
                    ->where(['and', ['email' =>  md5($this->gdprCheck->email)] , ['>', 'expiration_date', date('Y-m-d')]])->one());
        }
        return false;
    }
    
}
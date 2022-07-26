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

namespace frontend\forms\registration;
 
use Yii;
use yii\base\Model;

class AuthContainer {
    
    private $_forms = [];
    private $_errors = [];

    public function getForms($loadedScenarious) {
        switch ($loadedScenarious ){
            case 'account/create':
                $cLogin = CustomerRegistration::SCENARIO_LOGIN;
                $cRegisrt = CustomerRegistration::SCENARIO_REGISTER;
                $this->_forms = [
                    'login' => new CustomerRegistration(['scenario' => $cLogin, 'shortName' => $cLogin]),
                    'registration' => new CustomerRegistration(['scenario' => $cRegisrt, 'shortName' => $cRegisrt]),
                ];
                break;
            case 'account/login-box':
                $cLogin = CustomerRegistration::SCENARIO_LOGIN_TOP;
                $this->_forms = [
                    'login' => new CustomerRegistration(['scenario' => $cLogin, 'shortName' => $cLogin]),                    
                ];
                break;
            case 'index/auth':
                $cLogin = CustomerRegistration::SCENARIO_LOGIN;
                $cRegisrt = CustomerRegistration::SCENARIO_REGISTER;
                $cEnquire = CustomerRegistration::SCENARIO_ENQUIRE;
                $this->_forms = [
                    'login' => new CustomerRegistration(['scenario' => $cLogin, 'shortName' => $cLogin]),
                    'registration' => new CustomerRegistration(['scenario' => $cRegisrt, 'shortName' => $cRegisrt]),
                    'enquire' => new CustomerRegistration(['scenario' => $cEnquire, 'shortName' => $cEnquire]),
                ];
                break;
            case 'checkout':
                $cLogin = CustomerRegistration::SCENARIO_LOGIN;
                $cRegisrt = CustomerRegistration::SCENARIO_REGISTER;
                $cGuest = CustomerRegistration::SCENARIO_GUEST;
                $this->_forms = [
                    'login' => new CustomerRegistration(['scenario' => $cLogin, 'shortName' => $cLogin]),
                    'registration' => new CustomerRegistration(['scenario' => $cRegisrt, 'shortName' => $cRegisrt]),
                    'guest' => new CustomerRegistration(['scenario' => $cGuest, 'shortName' => $cGuest]),
                ];
                break;
            case 'quote/sample':
                $cFast = CustomerRegistration::SCENARIO_FAST_ORDER;
                $cLogin = CustomerRegistration::SCENARIO_LOGIN;
                $cRegisrt = CustomerRegistration::SCENARIO_REGISTER;                
                $this->_forms = [
                    'fast' => new CustomerRegistration(['scenario' => $cFast, 'shortName' => $cFast]),
                    'login' => new CustomerRegistration(['scenario' => $cLogin, 'shortName' => $cLogin]),
                    'registration' => new CustomerRegistration(['scenario' => $cRegisrt, 'shortName' => $cRegisrt]),                    
                ];
                break;
        }
        return $this->_forms;
    }
    
    public function loadScenario($scenario){
        $response = null;
        if (CustomerRegistration::hasScenario($scenario)){
            if(isset($this->_forms[$scenario])){
                $model = $this->_forms[$scenario];
            } else {
                $model = new CustomerRegistration(['scenario' => $scenario, 'shortName' => $scenario ]);
            }
            $this->_errors = [];
            if ($model->load(Yii::$app->request->post()) && $model->validate()){
                $response = $model->processCustomerAuth();
                if ($model->hasErrors()){
                    $this->_errors[$model->scenario] = $model->getErrors();
                }
            } else {
                if ($model->hasErrors()){
                    $this->_errors[$model->scenario] = $model->getErrors();
                }
            }
            if ($model->hasErrors()){
                $model->cleanupSafeFields();
            }
        }
        return $response;        
    }
    
    public function isShowAddress(){
        $show = false;
        if ($this->_forms){
            foreach($this->_forms as $form){
                $show = $show || $form->isShowAddress();
            }
        }
        return $show;
    }


    public function hasErrors($scenario = null){
        if (!empty($scenario)){
            return count($this->_errors[$scenario]);
        }
        return count($this->_errors);
    }
    
    public function getErrors($scenario = null){
        if (!empty($scenario)){
            return $this->_errors[$scenario];
        }
        return $this->_errors;
    }
    
}
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
namespace suppliersarea\forms;

class LoginForm extends \yii\base\Model{
    
    public $email_address;
    public $password;
    
    
    public function rules() {
        return[
            [['email_address', 'password'], 'required'],
            //['email_address', 'email'],
        ];
    }
    
    public function loginSupplier(){
        $_supplier = \suppliersarea\components\SupplierIdentity::findIdentityByData($this->email_address, $this->password);              
        if (!$_supplier){
            $this->addError('email_address', 'Invalid email/password');
            return false;
        } else {            
            \suppliersarea\SupplierModule::getInstance()->user->login($_supplier);            
            return true;
        }
    }
    
}
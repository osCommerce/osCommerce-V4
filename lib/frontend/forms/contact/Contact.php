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

namespace frontend\forms\contact;
 
use Yii;
use yii\base\Model;
use common\components\Customer;
use common\classes\ReCaptcha;
use frontend\design\Info;

class Contact extends \frontend\forms\registration\CustomerRegistration {
    
    public $customer_id;
    public $captcha = null;
    public $captha_enabled = false;    
    public $captcha_response;    
    public $captcha_widget;
    
    private $shortName = 'Contact';
    
    public function __construct($config = array()) {
        $this->customer_id = Yii::$app->user->getId();
        if (isset($config['captcha'])){
            $this->captha_enabled = (bool)$config['captcha'] && !$this->customer_id;
            if ($this->captha_enabled ){
                $this->captcha = new ReCaptcha();            
                $this->captha_enabled = $this->captcha->isEnabled();
            }
        }
        
        if ($this->captha_enabled && !$this->customer_id){
            $this->captcha_widget = \frontend\design\boxes\ReCaptchaWidget::widget();
        }        
    }
    
    public function formName(){
        return $this->shortName;
    }

    public function beforeValidate() {        
        $this->captcha_response = Yii::$app->request->post('g-recaptcha-response', null);
        $this->name = filter_var(htmlentities($this->name), FILTER_SANITIZE_STRING);
        $this->email_address = filter_var($this->email_address, FILTER_SANITIZE_STRING);
        $this->content = filter_var(htmlentities($this->content), FILTER_SANITIZE_STRING);
        return parent::beforeValidate();
    }

    public function rules() {        
        $_rules = [
            ['email_address', 'email', 'message' => ENTRY_EMAIL_ADDRESS_CHECK_ERROR],
            ['email_address', 'emailUnique'],
            [['email_address', 'content', 'name'], 'required', 'skipOnEmpty' => false],
            [['email_address', 'content', 'name'], 'string', 'skipOnEmpty' => false],
            [['dob', 'gdrp'], 'requiredOnRegister', 'skipOnEmpty' => false],            
            ['terms', 'requiredTrems', 'skipOnEmpty' => false],
            ['captcha_response', 'validateCaptcha', 'skipOnEmpty' => false]
        ];        
        return $_rules;
    }
    
    public function requiredTrems($attribute, $params){        
        if (!$this->$attribute){
            $this->addError($attribute, 'Please Read terms & conditions');
        }
    }
    
    public function validateCaptcha($attribute, $params){        
        if ($this->captha_enabled){
            if (!$this->captcha->checkVerification($this->captcha_response)){
                $this->addError($attribute, UNSUCCESSFULL_ROBOT_VERIFICATION);
            }
        }
    }

    public function scenarios() {        
        return [
            'default' => $this->collectFields(null)
        ];
    }
    
    public function collectFields($type) {
        
        $fields = ['email_address'];

        if (!$this->customer_id){
            if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])){
                $fields[] = 'dobTmp';
                $fields[] = 'dob';
                $fields[] = 'gdpr';
            }
            $fields[] = 'terms';
        }

        $fields[] = 'name';
        $fields[] = 'content';
        if ($this->captha_enabled){
            $fields[] = 'captcha_response';
        }

        return $fields;
            
    }
    
    public function preloadCustomersData($customer = null){
        if ($customer instanceof Customer){
            $this->email_address = $customer->customers_email_address;
            $this->name = $customer->customers_firstname;
        }
    }


    public function sendMessage(){
        $platform_config = Yii::$app->get('platform')->config();
        $to_name = $platform_config->const_value('STORE_OWNER');
        $to_email = $platform_config->contactUsEmail();


        $email_params = array();

        /** @var \common\extensions\CustomerAdditionalFields\CustomerAdditionalFields $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('CustomerAdditionalFields')) {
            $email_params = $ext::prepareEmailParams(Yii::$app->request->post());
        }

        $email_params['STORE_NAME'] = STORE_NAME;
        $email_params['CUSTOMER_NAME'] = $this->name;
        $email_params['CUSTOMER_EMAIL'] = $this->email_address;
        $email_params['ENQUIRY_CONTENT'] = $this->content;
        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Contact Us Enquiry', $email_params);

        if ( strpos($to_email,',')!==false ) {
            $to_name = '';
            \common\helpers\Mail::send(
                $to_name, $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'),
                ($email_subject ? $email_subject : EMAIL_SUBJECT), ($email_text ? $email_text : $this->content),
                $to_name, $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'),
                array(), 'CC: ' . $to_email . "\n" . 'Reply-To: "' . $this->name . '" <' . $this->email_address . '>'
            );
        } else {

            \common\helpers\Mail::send(
                $to_name, $to_email,
                ($email_subject ? $email_subject : EMAIL_SUBJECT), ($email_text ? $email_text : $this->content),
                $to_name, $to_email,
                array(), 'Reply-To: "' . $this->name . '" <' . $this->email_address . '>'
            );
        }
        return true;
    }
    
}
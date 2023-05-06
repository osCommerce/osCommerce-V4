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

namespace backend\forms;

use Yii;
use yii\base\Model;
use common\classes\ReCaptcha;

class Login extends Model {

    public $captha_enabled = false;

    public $captcha = null;

    public $captcha_response;
    public $captcha_widget;

    private $shortName = 'Login';

    public function __construct($config = array()) {
        if (isset($config['captha_enabled']) && $config['captha_enabled'] == true) {
            $this->captha_enabled = 'captha';
            unset($config['captha_enabled']);
        } elseif (\common\models\Fraud::underSurveillanceAddress()) {
            $this->captha_enabled = 'captha';
        }
        if (defined('ADMIN_LOGIN_OTP_ENABLE') AND (ADMIN_LOGIN_OTP_ENABLE == 'True')) {
            $this->captha_enabled = 'captha';
        }
        if ($this->captha_enabled == 'captha') {
            if (defined('PREFERRED_USE_RECAPTCHA') && PREFERRED_USE_RECAPTCHA == 'True') {
                $captcha = new ReCaptcha();
                if ($captcha->isEnabled()) {
                    $this->captha_enabled = 'recaptha';
                    $this->captcha_widget = \frontend\design\boxes\ReCaptchaWidget::widget();
                    $this->captcha = $captcha;
                }
            }
        }
        parent::__construct($config);
    }

    public function formName(){
        return $this->shortName;
    }

    public function load($data, $formName = null)
    {
        if ($this->captha_enabled == 'recaptha'){
            $formName = '';
        }
        return parent::load($data, $formName);
    }

    public function beforeValidate() {
        if ($this->captha_enabled == 'recaptha'){
            $this->captcha_response = Yii::$app->request->post('g-recaptcha-response', null);
        }
        return parent::beforeValidate();
    }

    public function rules() {
        $_rules = [];
        if ($this->captha_enabled == 'captha') {
            $_rules[] = ['captcha', 'required'];
            $_rules[] = ['captcha', 'captcha'];
        }
        if ($this->captha_enabled == 'recaptha') {
            $_rules[] = ['captcha_response', 'validateCaptcha', 'skipOnEmpty' => false];
        }
        return $_rules;
    }

    public function validateCaptcha($attribute, $params) {
        if ($this->captha_enabled == 'recaptha'){
            if (!$this->captcha->checkVerification($this->captcha_response)){
                $this->addError($attribute, 'Wrong captcha verification');
            }
        }
    }

    public function scenarios() {
        return [
            'default' => $this->collectFields(null)
        ];
    }

    public function collectFields($type) {
        $fields = [];
        if ($this->captha_enabled == 'captha') {
            $fields[] = 'captcha';
        }
        if ($this->captha_enabled == 'recaptha') {
            $fields[] = 'captcha_response';
        }
        return $fields;
    }

}
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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\ReCaptcha;

class SendForm extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $post = Yii::$app->request->post();

        if (is_array($post) && $post['send_form']){

            $request = self::validateFormData($post, $this->settings);

            if ($request == 'success') {
                self::sendMessage($this->settings);
            }

            return $request;

        }

        $text = self::fieldingFormsFields(self::getField($this->settings, 'text'));

        $success = \frontend\design\Info::translateKeys(self::getField($this->settings, 'success'));

        return IncludeTpl::widget(['file' => 'boxes/send-form.tpl','params' => [
            'form' => $text,
            'success' => $success,
            'formAction' => Yii::$app->urlManager->createUrl(['get-widget/one-post']),
            'id' => $this->id,
        ]]);
    }

    public static function validateFormData($formData, $settings)
    {
        $captcha = new ReCaptcha();

        //TODO: add validate form by 'required' field from $formData

        if (strpos(self::getField($settings, 'text'), '<captcha></captcha>') !== false) {
            if (!$captcha->checkVerification($_POST['g-recaptcha-response'])){
                return UNSUCCESSFULL_ROBOT_VERIFICATION;
            }
        }

        return 'success';
    }

    public static function fieldingFormsFields($text)
    {
        \common\helpers\Translation::init('js');
        \common\helpers\Translation::init('admin/js');

        $formData = self::getFormData($text);

        //TODO: add fielding customer data if customer loggedin

        $text = \frontend\design\Info::translateKeys($text);

        if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && Yii::$app->user->isGuest) {
            $text = str_replace('<gdpr></gdpr>', IncludeTpl::widget(['file' => 'boxes/gdpr.tpl']), $text);
        }

        $text = str_replace('<captcha></captcha>', \frontend\design\boxes\ReCaptchaWidget::widget(), $text);

        return $text;
    }

    public static function sendMessage($settings)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $platform_id = \common\classes\platform::currentId();
        $post = Yii::$app->request->post();
        $platformData = \frontend\design\Info::platformData();


        $platform_config = Yii::$app->get('platform')->config($platform_id);
        /**
         * @var $platform_config \common\classes\platform_config
         */

        if ($settings[0]['to_name']) {
            $to_name = $settings[0]['to_name'];
        } else {
            $to_name = $platform_config->const_value('STORE_OWNER');
        }
        if ($settings[0]['to_email_address']) {
            $to_email_address = $settings[0]['to_email_address'];
        } else {
            $to_email_address = $platform_config->landingContactEmail();
        }
        if ( strpos($to_email_address,',')!==false ) {
            $to_name = '';
        }

        /*if ($post['name']) {
            $from_email_name = $post['name'];
        } else {
            $from_email_name = 'Site visitor';
        }

        if ($post['email']) {
            $from_email_address = $post['email'];
        } else {
            $from_email_address = '';
        }*/
        $from_email_name = $platformData['platform_name'];
        $from_email_address = $platformData['platform_email_from'];


        $email_params = [];
        $email_params['STORE_NAME'] = '';
        $formData = self::getFormData(self::getField($settings, 'text'));
        foreach ($formData as $field) {
            if ($field['name']) {
                $email_params[strtoupper($field['name'])] = $_POST[$field['name']];
            }
        }

        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template(
            $settings[0]['email_template'],
            $email_params,
            $languages_id,
            $platform_id
        );
        if ( !empty($email_subject) && !empty($email_text) ) {
            \common\helpers\Mail::send(
                $to_name,
                $to_email_address,
                $email_subject,
                $email_text,
                $from_email_name,
                $from_email_address,
                [], '', '', ['add_br' => 'no']
            );
        }
    }

    public static function getField($settings, $field)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        if ( $settings[0][$field]) {
            return $settings[0][$field];
        } elseif ($settings[$languages_id][$field]) {
            return $settings[$languages_id][$field];
        } elseif ($settings[\common\classes\language::defaultId()][$field]) {
            return $settings[\common\classes\language::defaultId()][$field];
        } else {
            return $settings[0][$field];
        }

    }

    public static function getFormData($text)
    {
        $inputs = [];
        $formData = [];
        if (preg_match_all('/\<input[^>]+\>/', $text, $inputs)) {
            foreach ($inputs[0] as $input){
                $name = '';
                if (preg_match_all('/name\=\'([^\']+)\'/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                if (preg_match_all('/name\=\"([^\"]+)\"/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                $type = '';
                if (preg_match_all('/type\=\'([^\']+)\'/', $input, $tmpName)) {
                    $type = $tmpName[1][0];
                }
                if (preg_match_all('/type\=\"([^\"]+)\"/', $input, $tmpName)) {
                    $type = $tmpName[1][0];
                }
                $required = false;
                if (strpos($input, 'required')) {
                    $required = true;
                }
                $checked = false;
                if (strpos($input, 'required')) {
                    $checked = true;
                }
                $formData[] = [
                    'teg' => 'input',
                    'name' => $name,
                    'type' => $type,
                    'required' => $required,
                    'checked' => $checked
                ];
            }
        }
        if (preg_match_all('/\<textarea[^>]+\>/', $text, $textarea)) {
            foreach ($textarea[0] as $input){
                $name = '';
                if (preg_match_all('/name\=\'([^\']+)\'/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                if (preg_match_all('/name\=\"([^\"]+)\"/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                $checked = false;
                if (strpos($input, 'required')) {
                    $checked = true;
                }
                $formData[] = [
                    'teg' => 'textarea',
                    'name' => $name,
                    'required' => $required,
                    'checked' => $checked
                ];
            }
        }
        if (preg_match_all('/\<select[^>]+\>/', $text, $select)) {
            foreach ($select[0] as $input){
                $name = '';
                if (preg_match_all('/name\=\'([^\']+)\'/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                if (preg_match_all('/name\=\"([^\"]+)\"/', $input, $tmpName)) {
                    $name = $tmpName[1][0];
                }
                $checked = false;
                if (strpos($input, 'required')) {
                    $checked = true;
                }
                $formData[] = [
                    'teg' => 'textarea',
                    'name' => $name,
                    'required' => $required,
                    'checked' => $checked
                ];
            }
        }
        if (stripos($text, '<captcha></captcha>')) {
            $formData['captcha'] = true;
        }
        return $formData;
    }
}
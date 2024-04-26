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

namespace common\classes;

class Captcha
{
    private $identityBy;
    private $field;
    private $captcha;
    private $widget;

    public function __construct($field, $identityBy = null)
    {
        $this->identityBy = $identityBy;
        $captcha = new \common\classes\ReCaptcha();
        if (defined('PREFERRED_USE_RECAPTCHA') && PREFERRED_USE_RECAPTCHA == 'True' && $captcha->isEnabled()) {
            $this->widget = \frontend\design\boxes\ReCaptchaWidget::widget();
            $this->captcha = 'recaptcha';
            $this->field = 'g-recaptcha-response';
        } else {
            $this->field = $field;
            $params = ['attribute' => 'captcha'];
            $type = is_object($this->identityBy) ? 'model' : 'name';
            $params[$type] = $this->field;
            $this->captcha = 'captcha';
            $this->widget = \yii\captcha\Captcha::widget($params);
        }
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function isValid($post)
    {
        switch ($this->captcha) {
            case 'recaptcha':
                $captcha = new \common\classes\ReCaptcha();
                return $captcha->checkVerification($post[$this->field] ?? null);
                break;
            case 'captcha':
                if (is_object($this->identityBy && property_exists($this->identityBy, $this->field))) {
                    $userValue = $this->identityBy->{$this->field};
                } else {
                    $userValue = ($post[$this->field] ?? null);
                }
                return (new \yii\captcha\CaptchaValidator())->validate($userValue);
                break;
        }
        return false;
    }
}
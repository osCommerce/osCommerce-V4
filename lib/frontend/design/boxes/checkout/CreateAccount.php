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

namespace frontend\design\boxes\checkout;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class CreateAccount extends Widget
{

    public $file;
    public $params;
    public $settings;
    public $manager;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!Yii::$app->user->isGuest) {
            return '';
        }

        \common\helpers\Translation::init('js');

        if ($this->manager && !$this->params['manager']){
            $this->params['manager'] = $this->manager;
        }

        $this->params['manager']->createCaptchaWidget();
        return IncludeTpl::widget(['file' => 'boxes/checkout/create-account.tpl', 'params' => array_merge($this->params, [
            'settings' => $this->settings,
            'id' => $this->id,
            'model' => $this->params['manager']->getCustomerContactForm(),
            //'createAccount' => Yii::$app->request->get('guest') ? false : true
        ])]);
    }
}
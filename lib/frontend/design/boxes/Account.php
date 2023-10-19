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

use common\models\Customers;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class Account extends Widget
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
        //global $customer_id;

        $customer = null;
        if (!Yii::$app->user->isGuest) {
            $customer = Yii::$app->user->getIdentity();
        }
        $authContainer = new \frontend\forms\registration\AuthContainer();
        
        $isReseller = false;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_is_reseller')) {
            $isReseller = true;
        }
        $show_socials = false;
        if (property_exists(\Yii::$app->controller, 'show_socials')) {
            $show_socials = \Yii::$app->controller->show_socials;
        }
        $this->settings[0]['show_customers_name'] = (isset($this->settings[0]['show_customers_name']) ? $this->settings[0]['show_customers_name'] : 0);
        
        return IncludeTpl::widget(['file' => 'boxes/account.tpl', 'params' => [
            'id' => $this->id,
            'customerData' => $customer,
            'customerLogged' => !Yii::$app->user->isGuest,
            'isReseller' => $isReseller,
            'settings' => $this->settings,
            'params' =>  ['enterModels' => $authContainer->getForms('account/create'), 'action' => tep_href_link('account/login', 'action=process', 'SSL'), 'show_socials' => $show_socials]
        ]]);
    }
}

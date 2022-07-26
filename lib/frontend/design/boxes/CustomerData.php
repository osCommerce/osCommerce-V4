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

class CustomerData extends Widget
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
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $currencies = Yii::$container->get('currencies');
        $customer = Yii::$app->user->getIdentity();

        $data = '';
        switch ($this->settings[0]['customers_data']) {
            case 'points':
                $data = $customer->customers_bonus_points;
                break;
            case 'credit_amount':
                $data = $currencies->format($customer->credit_amount);
                break;
            case 'customer_name':
                $data = $customer->customers_firstname . ' ' . $customer->customers_lastname;
                break;
            case 'group':
                $groups = \common\models\Groups::findOne($customer->groups_id);
                $data = $groups->groups_name;
                break;
        }
        
        return $data;
    }
}

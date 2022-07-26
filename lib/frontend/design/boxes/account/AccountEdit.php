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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class AccountEdit extends Widget
{

    public $file;
    public $params;
    public $settings;
    public $editModel;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        
        \common\helpers\Translation::init('js');
        \common\helpers\Translation::init('account/edit');

        $customer = Yii::$app->user->getIdentity();
        
        if (is_null($this->editModel)){
            $this->editModel = new CustomerRegistration(['scenario' => CustomerRegistration::SCENARIO_EDIT, 'shortName' => CustomerRegistration::SCENARIO_EDIT]);
        }
        
        if (!\Yii::$app->request->isPost){
            $this->editModel->preloadCustomersData($customer);
        }

        return IncludeTpl::widget(['file' => 'boxes/account/account-edit.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'editModel' => $this->editModel,
            'action' => ['account/edit', 'action'=>'process'],
        ]]);
    }
}
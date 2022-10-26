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

namespace backend\design\editor;


use Yii;
use yii\base\Widget;

class Account extends Widget {
    
    public $manager;
    public $contactForm;
    public $shippingForm;
    public $billingForm;

    public function init(){
        parent::init();
    }
    
    public function run(){
        
        
        return $this->render('account', [
            'manager' => $this->manager,
            'contact' => $this->contactForm,
            'shipping' => $this->shippingForm,
            'billing' => $this->billingForm,
            'showGroup' => \common\helpers\Extensions::isCustomerGroupsAllowed(),
            'platforms' => \yii\helpers\ArrayHelper::map(\common\classes\platform::getList(false), 'id', 'text'),
            'groups' => \yii\helpers\ArrayHelper::map(\common\models\Groups::find()->all(), 'groups_id', 'groups_name'),
            'url' => \yii\helpers\Url::to(array_merge(['editor/create-account'], Yii::$app->request->getQueryParams())),
        ]);
    }
}

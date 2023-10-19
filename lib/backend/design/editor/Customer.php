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

class Customer extends Widget {
    
    public $manager;
    public $admin;
        
    public function init(){
        parent::init();
    }
    
    public function run(){
        $model = null;
        //if ($this->manager->isCustomerAssigned() || $this->manager->has('customer')){
            $model = $this->manager->getCustomerContactForm();
        //}
        
        return $this->render('customer',[
            'manager' => $this->manager,
            'admin_name' => $this->admin->getInfo('admin_firstname') . ' ' .$this->admin->getInfo('admin_lastname'),
            'model' => $model,
        ]);
    }
}

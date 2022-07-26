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

class Address extends Widget {
    
    public $manager;
    public $model;
    public $holder;
        
    public function init(){
        parent::init();
    }
    
    public function run(){

        return $this->render('address-area',[
            'manager' => $this->manager,
            'model' => $this->model,
            'holder' => $this->holder,
            'postcoder' => ($ext = \common\helpers\Acl::checkExtensionAllowed('AddressLookup')) ? $ext::getTool() : null,
        ]);
    }
}

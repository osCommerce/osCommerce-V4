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
use common\helpers\Acl;

class Bundle extends Widget {
    
    public $products;
    public $manager;
    
    public function init(){
        parent::init();
    }    
    
    public function run(){
        return $this->render('bundle', [
            'products' => $this->products,
            'manager' => $this->manager,
            'tax_address' => $this->manager->getOrderInstance()->tax_address,
            'tax_class_array' => \common\helpers\Tax::get_complex_classes_list(),
        ]);
    }
    
}

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

class Tax extends Widget {
    
    public $manager;
    public $tax_address;
    public $tax_class_array;
    public $product;
    public $onchange;
    public $wrap = false;
    public $uprid = ''; //use for products in bundle
    
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        if (!$this->uprid){
            $this->uprid = $this->product['current_uprid'] ?? $this->product['products_id'];
        }
        
        return $this->render('tax', [
            'product' => $this->product,
            'tax_address' => $this->tax_address,
            'tax_class_array' => $this->tax_class_array,
            'onchange' => $this->onchange,
            'wrap' => $this->wrap,
            'uprid' => $this->uprid,
        ]);
    }
    
}

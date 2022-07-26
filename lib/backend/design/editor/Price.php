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

class Price extends Widget {
    
    public $manager;
    public $price;
    public $price_variant;
    public $qty;
    public $tax;
    public $currency;
    public $field;
    public $classname;
    public $isEditInGrid = false;
    
    
    public function init(){
        parent::init();
        if (!is_null($this->price_variant)){
            if (is_null($this->price)){
                $this->price = $this->price_variant;
            }
        }
    }
    
    public function run(){
        
        $currencies = Yii::$container->get('currencies');
        
        return $this->render('price', [
            'isEditInGrid' => $this->isEditInGrid,
            'currencies' => $currencies,
            'price' => $this->price,
            'tax' => $this->tax,
            'qty' => $this->qty,
            'currency' => $this->currency,
            'field' => $this->field,
            'class' => $this->classname,
            'currency_value' => $currencies->currencies[$this->currency]['value'],
        ]);
    }
    
}

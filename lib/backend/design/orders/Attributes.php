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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;

class Attributes extends Widget {
    
    public $product;
    public $currency;
    public $currency_value;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        $attributes = [];
        if ( isset($this->product['attributes']) && is_array($this->product['attributes']) ){
            foreach ($this->product['attributes'] as $attribute){
                $attribute['display_price'] = '';
                if ($attribute['price']){
                    if ( strpos($attribute['prefix'], '%')!==false ) {
                        $attribute['display_price'] = substr($attribute['prefix'],0,1).\common\helpers\Output::percent($attribute['price']);
                    }else{
                        $attribute['display_price'] = \backend\design\editor\Formatter::price($attribute['price'], $this->product['tax'], $this->product['qty'], $this->currency, $this->currency_value);
                    }
                }
                $attributes[] = $attribute;
            }
        }
        return $this->render('attributes',[
            //'product' => $this->product,
            'attributes' => $attributes,
            //'currency' => $this->currency,
            //'currency_value' => $this->currency_value,
        ]);
    }
}

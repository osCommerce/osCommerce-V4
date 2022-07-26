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

class ShippingExtraInfo extends Widget {
    
    public $manager;
    public $order;
            
    public function init(){
        parent::init();
    }
    
    public function run(){
        $extra = '';
        if (!empty($this->order->info['shipping_class'])){
            if (strpos($this->order->info['shipping_class'], 'collect') !== false){
                $shipping = $this->manager->getShippingCollection()->get('collect');
                if ($shipping){
                    $extra .= $shipping->getCollectAddress($this->order->info['shipping_class']);
                }
            } else {
                $moduleName = explode('_' , $this->order->info['shipping_class']);
                $shipping = $this->manager->getShippingCollection()->get($moduleName[0]);
                if(is_object($shipping) && method_exists($shipping, 'getAdditionalOrderParams')){
                    $extra .= $shipping->getAdditionalOrderParams([], $this->order->order_id, $this->order->table_prefix);
                }
            }
            return $this->render('shipping-extra-info', [
                'extra' => $extra
            ]);
        }
    }
}

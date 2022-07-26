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

class PaymentExtraInfo extends Widget {
    
    public $manager;
    public $order;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        $extra = '';
        $info = $this->order->info;
        
        if(isset($info['payment_class']) && $info['payment_class'] == 'cardpos' && !empty($info['card_reference_id'])){
            $extra .= '<br /><span>' . TEXT_ID_REFERENCE . '</span><pre>' . $info['card_reference_id'] . '</pre>';
        }
        if(isset($info['payment_class']) && $info['payment_class']=='cash' && ($info['cash_data_summ'] > 0 || $info['cash_data_change'] > 0 )){
            $extra .= '<br /><span>' . TEXT_CHANGE . ': ' . $info['cash_data_change'] . '</span>';
            $extra .= '<span>' . TEXT_OUT_OF . ': ' . $info['cash_data_summ'] . '</span>';
        }
                        
        return $extra;
    }
}

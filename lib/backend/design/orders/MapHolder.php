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

class MapHolder extends Widget {
    
    public $manager;
    public $order;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        if (!defined('SHOW_MAP_ORDER_PROCESS')) return '';
        if (SHOW_MAP_ORDER_PROCESS != 'True') return '';
        
        return $this->render('map-holder', [
            'order' => $this->order,
            'sameAddress' => ($this->order->delivery == $this->order->billing && $this->order->delivery['format_id'] == $this->order->billing['format_id']),
            'manager' => $this->manager,
            ]);
    }
}

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

class Collection extends Widget {
    
    public $manager;
    public $collection;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        
        return $this->render('collection', [
            'products' => $this->collection['all_products'],
            'product' => $this->collection['curr_product'],
            'chosenProducts' => $this->collection['collection_products'],
            'old' => $this->collection['collection_full_price'],
            'price' => $this->collection['collection_full_price'],
            'special' => $this->collection['collection_discount_price'],
            'save' => $this->collection['collection_discount_percent'],
            'savePrice' => $this->collection['collection_save_price'],
            'manager' => $this->manager
        ]);
    }
    
}

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

class Toolbar extends Widget {
    
    public $expanded = true;
    public $visible = true;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        return $this->render('toolbar',[ 'expanded' => $this->expanded, 'visible' => $this->visible]);
    }
}

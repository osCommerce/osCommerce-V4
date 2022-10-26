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

class Attributes extends Widget {
    
    public $attributes;
    public $attrText;
    public $settings;
    public $complex = false;
    
    public function init(){
        parent::init();
        if (!$this->settings){
            $this->settings['onchange'] = 'getDetails(this)';
        }
    }    
    
    public function run(){
        return $this->render('attributes', [
            'attributes' => $this->attributes,
            'attrText' => $this->attrText,
            'settings' => $this->settings,
            'complex' => $this->complex,
        ]);
    }
    
}

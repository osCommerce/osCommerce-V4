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

namespace common\components\google\widgets;

use Yii;

class MapWidget extends \yii\base\Widget
{
    public $value;
    public $owner;
    public $description;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        return $this->render('map-config', [
            'value' => $this->value,
            'owner' => $this->owner,
            'description' => $this->description,
        ]);
    }
}

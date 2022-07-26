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

namespace common\classes;

use common\models\Platforms;
use common\models\PlatformsSettings;

class platform_settings {
    
    protected $platform_settings;
    
    public function __construct($id){
        $this->platform_settings = PlatformsSettings::findOne([$id]);
        if (!$this->platform_settings){
            $this->platform_settings = PlatformsSettings::findOne([platform::defaultId()]);
        }
    }
    
    public function getPlatformToDescription(){
        if ($this->platform_settings->use_own_descriptions){
            return $this->platform_settings->platform_id;
        } else {
            if ($this->platform_settings->use_owner_descriptions){
                return $this->platform_settings->use_owner_descriptions;
            } else {
                return platform::defaultId();
            }
        }
    }
}

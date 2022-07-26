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

$vendorDir = dirname(__FILE__);
spl_autoload_register(function($class) use ($vendorDir) {    
    if (strpos($class, 'subdee') !== false){
        $ex = explode("\\", $class);
        unset($ex[0]);
        unset($ex[1]);
        if (count($ex)){
            $file = implode(DIRECTORY_SEPARATOR , $ex);
            if (file_exists($vendorDir . DIRECTORY_SEPARATOR . $file . '.php')){
                require_once($vendorDir . DIRECTORY_SEPARATOR .$file . '.php');
            }
        }
    }
});
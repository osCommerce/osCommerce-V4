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
namespace common\services\storages;

interface StorageInterface {
    
    public function getPointer();

    public function setPointer(string $pointer);
    
    public function pointerShifted();

    public function get($name);
    
    public function getAll();

    public function set($name, $value);
    
    public function has($name);
    
    public function remove($name);
    
    public function removeAll();
    
}
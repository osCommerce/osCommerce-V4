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
use Yii;

class ObjectStorage implements StorageInterface {
    
    protected $_pointer = null;
    
    private $_storageName = '_tlStorageId';
    private $_storageID;
    
    private $data = [];
    
    public function __construct() {
        $this->_storageID = Yii::$app->security->generateRandomString();
    }
    
    public function setPointer(string $pointer){
        $this->_storageID = $pointer;
    }
    
    public function getPointer(){
        return $this->_storageID;
    }
    
    public function pointerShifted(){
        return false;
    }

    public function get($name){
        $var = $this->_get();
        return isset($var[$name]) ? $var[$name] : null;
    }
    
    public function getAll(){
        return $this->_get();
    }

    public function set($name, $value){
        $this->data[$name] = $value;
    }
    
    private function _get(){
        return $this->data;
    }
    
    public function has($name){
        return isset($this->data[$name]);
    }
    
    public function remove($name){
        if ($this->has($name)){
            $var = $this->_get();
            unset($var[$name]);
            $this->data = $var;
        }
    }
    
    public function removeAll(){
        $this->data = [];
    }
}
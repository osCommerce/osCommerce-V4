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

class SessionStorage implements StorageInterface {
    
    protected $_pointer = null;
    
    private $_storageName = '_tlStorageId';
    private $_storageID;
    
    public $session;
    
    public function __construct() {
        $this->session = \common\helpers\Session::getSession(); //Yii::$app->getSession();
        if ($this->session->has($this->_storageName)){
            $this->_storageID = $this->session->get($this->_storageName);
        } else {
            $this->_storageID = Yii::$app->security->generateRandomString();
            $this->session->set($this->_storageName, $this->_storageID);
        }
    }
    
    public function setPointer(string $pointer){
        $this->_storageID = $pointer;
        $this->session->set($this->_storageName, $this->_storageID);
    }
    
    public function getPointer(){
        return $this->_storageID;
    }
    
    public function pointerShifted(){
        return false;
    }

    public function get($name){
        $var = $this->_get();        
        return $var[$name] ?? null;
    }
    
    public function getAll(){
        return $this->_get();
    }

    public function set($name, $value){
        $var = $this->_get();
        $var[$name] = $value;        
        $this->session->set($this->_storageID, $var);
    }
    
    private function _get(){
        return $this->session->get($this->_storageID) ?? [];
    }
    
    public function has($name){
        return !is_null($this->get($name));
    }
    
    public function remove($name){
        if ($this->has($name)){
            $var = $this->_get();
            unset($var[$name]);
            $this->session->set($this->_storageID, $var);
        }        
    }
    
    public function removeAll(){
        $this->session->set($this->_storageID, []);
    }
}
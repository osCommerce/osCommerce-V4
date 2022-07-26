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
use common\models\DataStorage;

class DbStorage implements StorageInterface {
        
    protected $_storageID = null;
    
    protected $storedData = [];
    
    private $pointerShifted = false;
    
    public function __construct($pointer = null) {
        if (is_null($pointer)){
            if (is_null($this->_storageID)){
                $this->_storageID = Yii::$app->security->generateRandomString();
            }
        } else {
            $this->_storageID = $pointer;
        }
        $this->cleanOldData();
    }
    
    private function cleanOldData(){
        DataStorage::deleteAll(['<', 'date_modified', new \yii\db\Expression("now() - interval 2 month")]);
    }
        
    public function getPointer() {
        return $this->_storageID;
    }

    // storageId should be unique (modified quotes_id, orders_id, simples_id, basket_id)
    public function setPointer(string $pointer){
        if ($pointer){
            if ($pointer != $this->_storageID){
                $this->_storageID = $pointer;
                $this->storedData = [];
                $this->pointerShifted = true;
            }
        }
    }
    
    public function pointerShifted(){
        return $this->pointerShifted;
    }
    
    public function get($name){        
        if (!$this->storedData){
           $this->storedData = $this->_get();
        }
        return $this->storedData[$name] ?? null;
    }
    
    public function getAll(){
        if (!$this->storedData){
           $this->storedData = $this->_get();
        }
        return $this->storedData;
    }

    public function set($name, $value){
        $this->storedData[$name] = $value;
        $this->_save();
    }
    
    private function _get(){
        $_stored = [];
        if ($this->_storageID){
            $_stored = DataStorage::findOne(['pointer' => $this->_storageID]);
            if ($_stored){
                $_stored = unserialize(base64_decode($_stored['data']));
            } else {
                $_stored = ['emptyValue' => null];
            }
        }
        return $_stored;
    }
    
    private function _save(){
        if ($this->_storageID){
            $_stored = DataStorage::findOne(['pointer' => $this->_storageID]);
            if (!$_stored){
                $_stored = new DataStorage();
                $_stored->pointer = $this->_storageID;
            }
            unset($this->storedData['emptyValue']);
            $_stored->data = base64_encode(serialize($this->storedData));
            $_stored->save();
        }
    }
    
    public function has($name){
        if (!$this->storedData){
           $this->storedData = $this->_get();
        }
        return isset($this->storedData[$name]);
    }

    public function remove($name){        
        if (isset($this->storedData[$name])){
            unset($this->storedData[$name]);
            $this->_save();
        }
    }
    
    public function removeAll(){
        $this->storedData = [];
        if ($this->_storageID){
            $_stored = DataStorage::findOne(['pointer' => $this->_storageID]);
            if ($_stored) $_stored->delete();
        }
    }
}
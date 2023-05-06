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

namespace common\components;

use Yii;
use yii\base\Component;

class Settings extends Component {
    
    public $sessionKey = 'settings';
    private $data = [];
    
    public function has($variable) {
        $this->load();
        if (isset($this->data[$variable])) {
            return true;
        }
        return false;
    }
    
    public function get($variable) {
        $this->load();
        if (isset($this->data[$variable])) {
            return $this->data[$variable];
        } else {
            $def = $this->getDef($variable);
            // too much warnings about currency in the log
            // \Yii::warning("Settings variable '$variable' is not defined, default value returned.", 'main');
            return $def;
        }
    }

    public function getDef($variable) {
        switch ($variable) {
            case 'currency': return DEFAULT_CURRENCY;
            case 'affiliate_id': return 0;
            case 'customer_groups_id': return (defined('DEFAULT_USER_GROUP')?(int)DEFAULT_USER_GROUP:0);
        }
        return false;
    }
    
    public function set($variable, $value) {
        $this->load();
        if (!is_array($this->data)) { $this->data = []; }
        $this->data[$variable] = $value;
        $this->save();
        return true;
    }
    
    public function getAll()
    {
        $this->load();
        return $this->data;
    }
    
    public function setAll(array $data) {
        $this->data = $data;
        $this->save();
        return true;
    }
    
    public function remove($variable) {
        $this->load();
        if (isset($this->data[$variable])) {
            unset($this->data[$variable]);
            $this->save();
            return true;
        }
        return false;
    }

    public function clear($except = [])
    {
        if (is_array($except) && count($except) > 0) {
            foreach (array_keys($this->data) as $key) {
                if (in_array($key, $except)) continue;
                unset($this->data[$key]);
            }
        } else {
            $this->data = [];
        }
        $this->save();
        return true;
    }
                
    private function load()
    {
        if (Yii::$app instanceof \yii\console\Application) {
            $this->data = $_SESSION[$this->sessionKey] ?? [];
        } else {
            if (Yii::$app->storage->pointerShifted()){
                $this->data = Yii::$app->storage->getAll();
            } else {
                $this->data = Yii::$app->session->get($this->sessionKey, []);
            }
        }
    }

    private function save()
    {
        if (Yii::$app instanceof \yii\console\Application) {
            $_SESSION[$this->sessionKey] = $this->data;
        } else {
            if (!Yii::$app->storage->pointerShifted()){
                Yii::$app->session->set($this->sessionKey, $this->data);
            }
        }
    }
}
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

namespace backend\models;

use Yii;
use yii\base\Model;

class Admin {

    protected $info;

    public function __construct($id = 0) {

        if ($id) {
            $this->info = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . (int) $id . "'"));
        } else {
            $this->info = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . (int) Yii::$app->session->get('login_id') . "'"));
        }
    }

    public function getInfo($field) {
        if (is_array($this->info)) {
            return $this->info[$field];
        }
        return;
    }

    public function saveAdditionalInfo($data) {
        $this->_save('additional_info', serialize($data));
    }

    public function getAdditionalInfo() {
        return (isset($this->info['additional_info'])) ? unserialize($this->info['additional_info']) : [];
    }

    public function saveAdditionalData($data) {
        if (!isset($data) || !is_array($data)) {
            return null;
        }
        $_info = unserialize($this->info['additional_info'] ?? null);
        if (isset($_info) && is_array($_info)) {
            $_data = array_merge($_info, $data);
        } else {
            $_data = $data;
        }
        $this->_save('additional_info', serialize($_data));
    }

    public function getAdditionalData($key) {
        if (!$this->info['additional_info']) {
            return '';
        }
        $_info = unserialize($this->info['additional_info'] ?? null);

        if (!$_info) {
            return '';
        }

        if (isset($_info[$key])) {
            return $_info[$key];
        }

        return '';
    }

    private function _save($field, $data) {

        if ($this->info['admin_id']) {
            tep_db_query("update " . TABLE_ADMIN . " set {$field} = '" . tep_db_input($data) . "' where admin_id = '" . (int) $this->info['admin_id'] . "'");
        }

        return;
    }

}

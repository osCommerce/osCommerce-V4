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

/**
 * Class Api
 * realize DPD Local API
 */
class DpdApi {

    private $_server = 'https://api.interlinkexpress.com';
    private $_username = 'wealden2';
    private $_password = 'muscles123';
    private $_account_number = '2203894';
    private $_geo_session = '';

    public function __construct() {
        
    }
    
    public function setConfig($config) {
        if (isset($config['server'])) {
            $this->_server = $config['server'];
        }
        if (isset($config['username'])) {
            $this->_username = $config['username'];
        }
        if (isset($config['password'])) {
            $this->_password = $config['password'];
        }
        if (isset($config['account_number'])) {
            $this->_account_number = $config['account_number'];
        }
    }

    private function _get_geo_session() {
        $headers = array(
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->_username . ':' . $this->_password),
            'Content-Type: application/json',
        );
        $result = $this->_call_http_url($this->_server . '/user/?action=login', json_encode(['auditUser' => 'null']), $headers);
        $result = json_decode($result, true);
        return $result['data']['geoSession'];
    }

    public function _send_request($command, $getfields = [], $postfields = [], $headers = []) {
        if ($this->_geo_session == '') {
            $this->_geo_session = $this->_get_geo_session();
        }

        $headers[] = 'GeoClient: account/' . $this->_account_number;
        $headers[] = 'GeoSession: ' . $this->_geo_session;

        $request = '';
        if (is_array($getfields) && count($getfields) > 0) {
            $request = '?';
            foreach ($getfields as $key => $value) {
                $request .= $key . '=' . urlencode($value) . '&';
            }
        } elseif (is_string($getfields) && strlen($getfields) > 0) {
            $request = '?' . $getfields;
        }

        return $result = $this->_call_http_url($this->_server . $command . $request, $postfields, $headers);
    }

    private function _call_http_url($url, $postfields = array(), $headers = array()) {
        if ($ch = curl_init()) {
            $url = str_replace('&amp;', '&', $url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (is_array($headers) && count($headers) > 0) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            if ((is_array($postfields) && count($postfields) > 0) ||
                    (is_string($postfields) && strlen($postfields) > 0)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            }
            if (!($response = curl_exec($ch))) {
                $response = curl_error($ch);
            }
            curl_close($ch);
            return $response;
        }
    }

}

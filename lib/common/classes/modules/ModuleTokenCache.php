<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * 
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright 2000-2023 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\classes\modules;
use \yii\httpclient\Client;

/**
 * cache access token (encrypted in DB)
 * implement in your module
 * protected function getToken():['token' => 'xxx', 'until' => 'db_date_time|time_seconds|epoch_until'];
 *
 * if use prepareSendRequest
 *
 * protected function getRestAPIUser() {}
 * protected function getRestApiPassword() {$ret = $this->decryptConst('MODULE_xxxx_CLIENT_SECRET'); return $ret; }
 * protected function getTokenUrl() {}
 * protected function getApiUrl($action) {}
 *
 *
 * optionally
 * protected function getEncryptionKey():string {}
 *
 */

trait ModuleTokenCache {

    public $auth_platform_id = 0;
    public $auth_login_id = 0;
    public $auth_location = 'db'; //2do file

/**
 * get token from Cache, if not found/expired - call getToken and save new token in cache.
 * @return string
 * @throws type
 */
    protected function getCacheToken() {

        $platform_id = $admin_id = 0;
        if (!empty($this->auth_platform_id)) {
            $platform_id = intval($this->auth_platform_id);
        }
        if (!empty($this->auth_login_id)) {
            $admin_id = intval($this->auth_login_id);
        }
        
        $q = \common\models\ModuleTokens::find()
            ->andWhere(['>', 'valid_until', date(\common\helpers\Date::DATABASE_DATETIME_FORMAT)])
            ->andWhere([
              'class' => (!empty($this->code)?$this->code:$this->getModuleCode()),
              'admin_id' => $admin_id,
              'platform_id' => $platform_id,
            ]);
        $cached = $q->one();
        if (!empty($cached->token)) {
            $ret = $cached->token;

            if (method_exists($this, 'getEncryptionKey')) {
                $key = $this->getEncryptionKey();
            }
            if (empty($key)) {
                $key = \Yii::$app->params['secKey.backend'];
            }

            $ret = \Yii::$app->security->decryptByKey( utf8_decode($ret), $key);
        }

        if (empty($ret)) {
            if (!method_exists($this, 'getToken')) {
                throw new \Exception('Method getToken does not exists');
            }
            $token_info = $this->getToken();
            if (empty($token_info['token'])) {
                throw new \Exception('New Token not found');
            }
            $this->saveTokenToCache($token_info);
            $ret = $token_info['token'];
        }

        return $ret;
    }

    protected function saveTokenToCache($token_info) {
        $token = $token_info['token'];
        $until = $token_info['until'];
        if (is_numeric($until)) {
            //time or linux epoch
            // generally 10sec delay is too huge (token is taken from cache right before request - all request details already prepared).
            if ($until<1689000000) {
                $until = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT, time()+$until-10);
            } else {
                $until = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT, $until);
            }
        }
        $platform_id = $admin_id = 0;
        if (!empty($this->auth_platform_id)) {
            $platform_id = intval($this->auth_platform_id);
        }
        if (!empty($this->auth_login_id)) {
            $admin_id = intval($this->auth_login_id);
        }
        if (method_exists($this, 'getEncryptionKey')) {
            $key = $this->getEncryptionKey();
        }
        if (empty($key)) {
            $key = \Yii::$app->params['secKey.backend'];
        }

        if ($this->auth_location == 'db') {
            \common\models\ModuleTokens::DeleteAll([
              'class' => (!empty($this->code)?$this->code:$this->getModuleCode()),
              'admin_id' => $admin_id,
              'platform_id' => $platform_id,
            ]);
            $model = new \common\models\ModuleTokens();
            $model->loadDefaultValues();
            $model->setAttributes([
              'class' => (!empty($this->code)?$this->code:$this->getModuleCode()),
              'admin_id' => $admin_id,
              'platform_id' => $platform_id,
              'valid_until' => $until,
              'token' => utf8_encode(\Yii::$app->security->encryptByKey( $token, $key)),
            ]);
            $model->save();

        }
    }

/**
 *
 * @param string $type
 * @param array|false $params POST data or False to send GET request
 * @param array $url_params
 * @return array ['error' => , 'description' => , 'http_code' => , 'data' => ];
 */
    protected function prepareSendRequest($type, $params, $url_params = []) {
        $url = $this->getApiUrl($type);

        if (!empty($url_params)) {
            if (!is_array($url_params)) {
                $url_params = [$url_params];
            }
            $url = vsprintf($url, $url_params);
        }

        $client = new Client([
            'requestConfig' => [
                'format' => ($type != 'get_token'? Client::FORMAT_JSON : Client::FORMAT_RAW_URLENCODED)
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'parsers' => [
                'json' => '\yii\httpclient\JsonParser',
            ]
        ]);
        $request = $client->createRequest();
        $request->setMethod('post');

        try {
            if ($type != 'get_token') {
                $request->headers->set('Authorization', 'bearer ' . $this->getCacheToken());
            } else {
                $url = $this->getTokenUrl();
                $username = $this->getRestApiUser();
                $password = $this->getRestApiPassword();
                $request->headers->set('Authorization', 'Basic ' . base64_encode("$username:$password"));
            }


            if ($params === false) {
                $request->setMethod('get');
            }
            $request->setUrl($url)->setData($params);


            if (!empty($this->debug)) {
                if ($this->debug > 1) {
                    \Yii::warning(print_r($request, true), $this->code . 'REQUEST');
                } else {
                    \Yii::warning($url . ' post  => ' . print_r($params, true), $this->code . 'REQUEST');
                }
            }

            $transaction_response = $request->send();

            if (!empty($this->debug) && $this->debug > 1) {
                \Yii::warning(print_r($transaction_response, true), $this->code . 'RESPONCE');
            }
            if ($transaction_response->isOk) {
                $return = [
                        'http_code' => $transaction_response->getStatusCode(),
                        'data' => $transaction_response->getData()
                    ];

            } else {

                $return = [
                  'error' => 1,
                  'description' => ''
                ];
                $data = $transaction_response->getData();
                if (!empty($data['description'])) {
                    $return['description'] = $data['description'];
                }
                $data = json_decode($transaction_response->getContent(), true);

                if (!empty($data['errors']) && is_array($data['errors'])) {
                    foreach ($data['errors'] as $error) {
                        $return['description'] .=  ' ' . $error['description'] . ' ' . ($error['property']??'');
                    }
                } elseif (!empty($data['statusDetail'])) {
                    $return['description'] = $data['statusDetail'];
                }
            }

        } catch (\Exception $ex) {
            $return = $ex->getMessage();
        }

        if (!empty($this->debug)) {
            \Yii::warning(print_r($return, true), $this->code . 'RESPONCE');
        }
        return $return;
    }

}

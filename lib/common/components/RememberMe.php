<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\components;

/**
 * seems YII2 doesn't provide a way to change user's auth_key for autologin
 * (saved only once during customer registration .... https://yii2-framework.readthedocs.io/en/latest/guide/security-authentication/ )
 */
class RememberMe extends \yii\web\User {
    
    public $autoLoginDuration = 0;

    protected function removeIdentityCookie()
    {
        parent::removeIdentityCookie();
        try {
            $user = $this->getIdentity();
            if ($user) {
                $user->auth_key = \Yii::$app->security->generateRandomString();
                $user->save(false);
            }
        } catch (\Exception $ex) {
            \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG');
        }

    }

    public function logout($destroySession = true) {
        //disable autologin (everywhere after log out
        try {
            $user = $this->getIdentity();
        } catch (\Exception $ex) {
            \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG');
        }

        if (parent::logout($destroySession)) {
            //parent::removeIdentityCookie();
            if ($user) {
                try {
                    $user->auth_key = \Yii::$app->security->generateRandomString();
                    $user->save(false);
                } catch (\Exception $ex) {
                    \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG');
                }
            }
        }
    }


}

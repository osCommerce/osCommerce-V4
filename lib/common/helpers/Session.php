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

namespace common\helpers;
use Yii;

/**
 * Description of Session
 *
 * @author vlad
 */
class Session {

    public static function get($key) {
        return static::getSession()->get($key);
    }

    public static function getSession() {
        if (method_exists(Yii::$app, 'getSession')) {
            return Yii::$app->getSession();
        } else {
            //console workaround
            $storage = Yii::$app->get('storage');
            if (is_object($storage)) {
                return $storage;
            } else {
                return Yii::$app->session;
            }
        }
    }
    
    public static function deleteCustomerSessions($customer_id)
    {
        $whosRows = \common\models\WhosOnline::find()
                ->where(['customer_id' => $customer_id])
                ->asArray()
                ->all();
        foreach ($whosRows as $whos) {
            if (STORE_SESSIONS == 'mysql') {
                $sessionRow = \common\models\Sessions::find()->where(['sesskey' => $whos['session_id']])->one();
                if ($sessionRow instanceof \common\models\Sessions) {
                    $sessionRow->delete();
                }
            } else {
                $file = tep_session_save_path() . '/' . $whos['session_id'];
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        \common\models\WhosOnline::deleteAll(['customer_id' => $customer_id]);
    }

}

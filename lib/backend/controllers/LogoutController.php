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

namespace backend\controllers;

use common\helpers\Translation;
use Yii;
use yii\web\Controller;

/**
 * default controller to handle user requests.
 */
class LogoutController extends Controller
{
    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex()
    {
        global $login_id, $device_hash;

        $alslHash = trim(Yii::$app->request->get('hash'));
        if ( !empty($alslHash) ) {
            Translation::init('admin/logout');
            $this->layout = false;
            return $this->render('sessions', [
                'formAction' => \yii\helpers\Url::to(['logout/', 'hash'=>null]),
                'alslHash' => $alslHash,
            ]);
        }
        $alslHash = trim(Yii::$app->request->post('hash'));
        if ($alslHash != '') {
            \common\models\AdminLoginSessionLogoff::deleteAll(['<', 'alsl_date_expire', date('Y-m-d H:i:s')]);
            $alslRecord = \common\models\AdminLoginSessionLogoff::findOne(['alsl_hash' => $alslHash]);
            if ($alslRecord instanceof \common\models\AdminLoginSessionLogoff) {
                $login_id = (int)$alslRecord->alsl_admin_id;
                $device_hash = $alslRecord->alsl_device_id;
                \common\models\AdminLogin::deleteAll(['al_admin_id' => $login_id]);
                \common\models\AdminLoginSession::deleteAll(['als_admin_id' => $login_id]);
                \common\models\AdminDevice::deleteAll(['ad_admin_id' => $login_id, 'ad_device_id' => $device_hash]);
                $alslRecord->delete();
            }
            unset($alslRecord);
        }
        unset($alslHash);

        if (!tep_session_is_registered('admin_multi_session_error')) {
            $adminLoginLogRecord = new \common\models\AdminLoginLog();
            $adminLoginLogRecord->all_event = 20;
            $adminLoginLogRecord->all_device_id = $device_hash;
            $adminLoginLogRecord->all_ip = '';
            $adminLoginLogRecord->all_agent = '';
            $adminLoginLogRecord->all_user_id = $login_id;
            $adminLoginLogRecord->all_user = \common\models\AdminLoginLog::getAdminEmail($login_id);
            $adminLoginLogRecord->all_date = date('Y-m-d H:i:s');
            try {
                $adminLoginLogRecord->save();
            } catch (\Exception $exc) {}
        }

        \common\models\AdminLoginSession::deleteAll(['als_admin_id' => (int)$login_id, 'als_device_id' => trim($device_hash)]);

        //tep_session_destroy();
        tep_session_unregister('login_id');
        tep_session_unregister('login_firstname');
        tep_session_unregister('login_groups_id');
        tep_session_unregister('login_affiliate');
        tep_session_unregister('login_vendor');
        tep_session_unregister('device_hash');

        session_regenerate_id();

        $session = Yii::$app->session;
        $session->destroy();

        return $this->redirect(['login/']);
    }
}
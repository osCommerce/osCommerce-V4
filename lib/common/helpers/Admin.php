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

namespace common\helpers;


class Admin
{

    public static function getAdminsWithWalkinOrders(){
        $orderAdminIds = \common\models\Orders::find()
            ->distinct()
            ->select(['admin_id'])
            ->asArray()
            ->all();
        $orderAdminIds = \yii\helpers\ArrayHelper::map($orderAdminIds, 'admin_id','admin_id');
        unset($orderAdminIds[0]);

        $admins = [];
        if (count($orderAdminIds)>0) {
            foreach (\common\models\Admin::find()->where(['IN','admin_id',$orderAdminIds])->all() as $admin) {
                $admins[] = $admin;
            }
        }
        return $admins;
    }

/**
 * returns list of all [active] admins <login_failture<5>
 * @param bool $allDetails include all admin details
 * @return array [admin_id => '', 'listTitle' => concat(admin_lastname, admin_firstname, admin_email_address) [, *] ]
 */
    public static function getList($allDetails = false) {

      $q = \common\models\Admin::find()
          ->andWhere(' login_failture < 5 ') // add status field??
          ->addSelect(['listTitle' => new \yii\db\Expression('concat(admin_lastname, " ", admin_firstname, " ", admin_email_address)')])
          ->addSelect('admin_id')
          ->orderBy('admin_lastname, admin_firstname, admin_email_address')
          ;
      if ($allDetails) {
        $q->addSelect("*");
      }
      return $q->asArray()->all();

    }
    
    public static function appShopConnectedMessage() {
        if (defined('PROJECT_RELEASE_TYPE') && PROJECT_RELEASE_TYPE == 'VERSIONING') {
            return;
        }
        global $login_id;
        $admin = \common\models\Admin::findOne($login_id);
        $storageKey = $admin->storage_key ?? '';
        if (empty($storageKey)) {
            $message = (defined('MESSAGE_KEY_EMPTY')
                ? constant('MESSAGE_KEY_EMPTY')
                : 'Your shop is not connected with <a href="%1$s">App Shop</a>. Why and how do I need to connect to App Shop? Please see the article <a target="_blank" href="%2$s">Connecting to App shop</a> for more details.'
            );
            $appUrl = \Yii::$app->urlManager->createUrl('install');
            $wikiUrl = 'https://www.oscommerce.com/wiki/index.php?title=Connecting_to_App_Shop';
            \Yii::$container->get('message_stack')->add(sprintf($message, $appUrl, $wikiUrl), 'alert', 'info');
        } else {
            $storageUrl = \Yii::$app->params['appStorage.url'];
            $contents = @file_get_contents($storageUrl . 'patch/last-known.json');
            $current = (defined('MIGRATIONS_DB_REVISION') ? MIGRATIONS_DB_REVISION : '');
            if (!empty($contents) && !empty($current)) {
                $json = json_decode($contents);
                if (isset($json->version)) {
                    $version = (string)$json->version;
                    if ($current != $version) {
                        $message = (defined('MESSAGE_SYSTEM_UPDATES')
                            ? constant('MESSAGE_SYSTEM_UPDATES')
                            : 'The updates are available in <a href="%1$s">App Shop</a>. Please check it for more details.'
                        );
                        $appUrl = \Yii::$app->urlManager->createUrl(['install', 'set' => 'updates']);
                        \Yii::$container->get('message_stack')->add(sprintf($message, $appUrl), 'alert', 'info');
                    }
                }
            }
        }
    }

    /**
     * @return array|false
     */
    public static function limitedPlatformList()
    {
        $limited_platforms = false;
        $admin_id = (int)$_SESSION['login_id'];
        if (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            $limited_platforms = [];
            $platforms = \common\models\AdminPlatforms::find()->where(['admin_id' => $admin_id])->asArray()->all();
            foreach ($platforms as $platform) {
                $limited_platforms[(int)$platform['platform_id']] = (int)$platform['platform_id'];
            }
        }
        return $limited_platforms;
    }

}
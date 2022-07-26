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

}
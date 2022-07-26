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
use common\models\AdminMessages;

/*
 * Admin Notifier
 */

class AdminNotifier {
    
    /* save  retrieved class message or simple message */
    public function addNotification($class, $message, $type = 'info') {
        $adminMessage = new AdminMessages();
        if (is_object($class) && $class instanceof \backend\models\NotificationInterface) {
            $adminMessage->setAttributes([
                'class' => $class::className(),
                'message' => $class->prepareAdminMessage($message),
                    ], false);
        } else {
            $adminMessage->message = $message;
        }
        $adminMessage->status = 'unread';
        $adminMessage->type = $type;
        $adminMessage->save();
    }

    /* return array retrieved messages */
    public function getUnreadNotifications() {
        $_list = AdminMessages::getUnread()->orderBy('date_added desc')->all();
        if ($_list) {
            foreach ($_list as $key => $notification) {
                $_list[$key]->status = 'read';
                $_list[$key]->save();
                if (!empty($notification->class) && class_exists($notification->class)) {
                    $object = new $notification->class;
                    $_list[$key]->message = $object->getAdminMessage($notification->message);
                }
            }
        }
        return $_list;
    }

    public function getUnreadCount() {
        return AdminMessages::getUnread()->count();
    }

    public function getLastNotification() {
        //to do
    }

    public function getAllNotifications() {
        //to do
    }

}

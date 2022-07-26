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

namespace frontend\controllers;

use Yii;

/**
 * Messages extension controller
 */
class MessagesController extends Sceleton {

public function actionIndex() {
        if (Yii::$app->user->isGuest) {
            global $navigation;
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionIndex();
        }
    }

    public function actionList() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionList();
        }
    }

    public function actionView() {
        if (Yii::$app->user->isGuest) {
            global $navigation;
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionView();
        }
    }

    public function actionNew() {
        if (Yii::$app->user->isGuest) {
            global $navigation;
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionNew();
        }
    }

    public function actionAttachment() {
        if (Yii::$app->user->isGuest) {
            global $navigation;
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionAttachment();
        }
    }

    public function actionSave() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionSave();
        }
    }

    public function actionBulkUnread() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionBulkUnread();
        }
    }

    public function actionBulkStarred() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionBulkStarred();
        }
    }

    public function actionBulkDelete() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionBulkDelete();
        }
    }

    public function actionUpdateUnread() {
        if (Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('messages', '', 'SSL'));
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')) {
            return $ext::actionUpdateUnread();
        }
    }

    public function actionCommunication()
    {
        if ($ext = \common\helpers\Acl::checkExtension('Communication', 'actionIndex')) {
            return $ext::actionIndex();
        }
    }
}

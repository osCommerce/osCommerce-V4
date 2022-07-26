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

use Yii;
use common\extensions\SMS\SMS;
use common\extensions\SMS\Messages;
use common\classes\platform;

class Sms_messagesController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_SMS', 'BOX_SMS_MESSAGES'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/sms');
        parent::__construct($id, $module);
    }
    
    public function beforeAction($action)
    {
        if (false === \common\helpers\Acl::checkExtensionAllowed('SMS', 'allowed')) {
            $this->redirect(array('/'));
            return false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex() {

        $this->selectedMenu = array('settings', 'sms_messages', 'sms_messages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('sms_messages/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="javascript:void(0)" onClick="newMessage();" class="btn btn-primary"><i class="icon-file-text"></i>' . IMAGE_INSERT . '</a>';
        $this->view->headingTitle = HEADING_TITLE;

        $platforms = platform::getList(false);

        $this->view->MessagesTable = array(
            array(
                'title' => TABLE_HEADING_DEFAULT_MESSGAES,
                'not_important' => 0,
            ),
        );
        return $this->render('index', [
                    'platforms' => $platforms,
                    'first_platform_id' => platform::firstId(),
                    'default_platform_id' => platform::defaultId(),
                    'isMultiPlatforms' => platform::isMulti(),
        ]);
    }

    public function actionList() {

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_page_number = ($start / $length) + 1;
        $platform_id = Yii::$app->request->get('platform_id');

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_prepare_input($_GET['search']['value']);
            $search = " and (sms_default_message_name like '%" . tep_db_input($keywords) . "%' or sms_default_message_text like '%" . tep_db_input($keywords) . "%')";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                default:
                    $orderBy = "sms_default_message_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
            }
        } else {
            $orderBy = "sms_default_message_name asc";
        }
        $params = ['orderBy' => $orderBy, 'search' => $search, 'limit' => $start . ', '. $length, 'total' => true];
        $responseList = Messages::getAllMessages($platform_id, $params, $total);

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionItempreedit() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $item_id = (int) Yii::$app->request->post('item_id', 0);

        $mInfo = Messages::getMessage($item_id);

        return $this->renderAjax('view', ['mInfo' => $mInfo, 'languages_id' => $languages_id]);
    }

    public function actionEdit() {

        $this->selectedMenu = array('sms_messages', 'sms_messages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('sitemap/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#message_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $item_id = (int) Yii::$app->request->get('item_id', 0);
        $platform_id = (int) Yii::$app->request->get('platform_id', 0);

        if (Yii::$app->request->isPost) {
            $platform_id = (int) Yii::$app->request->post('platform_id', 0);
            if ($item_id = Messages::saveMessage($_POST)) {
                Yii::$app->session->setFlash('success', TEXT_MESSEAGE_SUCCESS);
                return $this->redirect(['sms_messages/edit', 'platform_id' => $platform_id, 'item_id' => $item_id]);
            } else {
                Yii::$app->session->setFlash('error', TEXT_MESSAGE_ERROR);
                return $this->redirect(['sms_messages/edit', 'platform_id' => $platform_id]);
            }
        } else {
            $mInfo = Messages::getMessage($item_id, $platform_id);
        }
        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();
        return $this->render('edit', ['mInfo' => $mInfo, 'messages' => $messages]);
    }

    public function actionDelete() {
        $this->layout = false;

        $item_id = Yii::$app->request->post('item_id', 0);
        if ($item_id) {
            Messages::deleteMessage($item_id);
        }
        echo '1';
        exit();
    }

}

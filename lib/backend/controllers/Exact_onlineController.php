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

class Exact_onlineController extends Sceleton {

  public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_EXACT_ONLINE'];

  public function actionIndex() {
    $this->selectedMenu = array('settings', 'exact_online');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('exact_online/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    /*if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      return $ext::adminIndex();
    }*/

    return $this->render('index');
  }

  public function actionUpdate() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $message = $ext::adminUpdate();
    } else {
      $message = 'Error: Please Install Exact Online Extension.';
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionRun() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $message = $ext::runFeedNow($_GET['feed']);
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionProducts() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $message = $ext::runFeedNow('exact_run_products');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionStock() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $message = $ext::runFeedNow('exact_run_products_qty');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOrders() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $message = $ext::runFeedNow('exact_run_orders');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOauth() {
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ExactOnline', 'allowed')) {
      $ext::adminOauth();
    }

    tep_redirect(tep_href_link('exact_online'));
  }
}

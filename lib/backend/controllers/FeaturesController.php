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
use \common\helpers\Translation;

class FeaturesController extends Sceleton {

  public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_FEATURES'];
  
  public function __construct($id, $module=null){
    Translation::init('admin/features');
    parent::__construct($id, $module);
  }  
    
  public function actionIndex() {
    $currencies = Yii::$container->get('currencies');

    $this->selectedMenu = array('settings', 'features');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('features/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    $search = '';
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
      $keywords = tep_db_prepare_input($_GET['search']);
      $search .= " and (f.features_title like '%" . tep_db_input($keywords) . "%')";
    }
    if ($_GET['ftID'] > 0) {
      $search .= " and f.features_types_id = '" . (int) $_GET['ftID'] . "'";
    }

    $params = array();
    $this->view->featuresArray = array();
    $this->view->featuresTypesArray = array();
    $features_types_pulldown_array = array(array('id' => '', 'text' => TEXT_ALL_FEATURES_TYPES));
    $features_types_query = tep_db_query("select features_types_id, features_types_title from " . TABLE_FEATURES_TYPES . " where 1");
    while ($features_types = tep_db_fetch_array($features_types_query)) {
      $this->view->featuresTypesArray[$features_types['features_types_id']] = $features_types;
      $features_types_pulldown_array[] = array('id' => $features_types['features_types_id'], 'text' => $features_types['features_types_title']);
      $features_query = tep_db_query("select f.features_id, f.features_title, f.features_image, f.features_description, f.features_monthly_price, f.features_setup_price, count(d2f.features_id) as feature_enabled from " . TABLE_FEATURES . " f left join " . TABLE_DEPARTMENTS_TO_FEATURES . " d2f on f.features_id = d2f.features_id and d2f.departments_id = '" . (int)DEPARTMENTS_ID . "' where f.features_types_id = '" . (int)$features_types['features_types_id'] . "' and f.always_included = 0 " . $search . " group by f.features_id order by f.sort_order, f.features_title");
      while ($features = tep_db_fetch_array($features_query)) {
        if (tep_not_null($features['features_image'])) {
          $features['features_image'] = SUPERADMIN_HTTP_IMAGES . $features['features_image'];
        }
        if ($features['features_setup_price'] > 0) {
          $features['features_setup_price'] = $currencies->format($features['features_setup_price']);
        } else {
          $features['features_setup_price'] = '';
        }
        if ($features['features_monthly_price'] > 0) {
          $features['features_monthly_price'] = $currencies->format($features['features_monthly_price']);
        } else {
          $features['features_monthly_price'] = '';
        }
        $this->view->featuresArray[$features_types['features_types_id']][$features['features_id']] = $features;
      }
    }
    $this->view->filterFeaturesTypes = tep_draw_pull_down_menu('ftID', $features_types_pulldown_array, $_GET['ftID'], 'class="form-control" onchange="this.form.submit();"');
    $this->view->filterSearch = tep_draw_input_field('search', $_GET['search'], 'class="form-control"');

    return $this->render('index');
  }

  public function actionView() {
    $messageStack = \Yii::$container->get('message_stack');
    $currencies = Yii::$container->get('currencies');

    $this->selectedMenu = array('settings', 'features');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('features/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    $features_id = Yii::$app->request->get('fID', 0);
    $features = tep_db_fetch_array(tep_db_query("select f.features_id, f.features_title, f.features_image, f.features_description, f.features_monthly_price, f.features_setup_price, count(d2f.features_id) as feature_enabled from " . TABLE_FEATURES . " f left join " . TABLE_DEPARTMENTS_TO_FEATURES . " d2f on f.features_id = d2f.features_id and d2f.departments_id = '" . (int)DEPARTMENTS_ID . "' where f.features_id = '" . (int) $features_id . "' group by f.features_id"));
    if (tep_not_null($features['features_image'])) {
      $features['features_image'] = SUPERADMIN_HTTP_IMAGES . $features['features_image'];
    }
    if ($features['features_setup_price'] > 0) {
      $features['features_setup_price'] = $currencies->format($features['features_setup_price']);
    } else {
      $features['features_setup_price'] = '';
    }
    if ($features['features_monthly_price'] > 0) {
      $features['features_monthly_price'] = $currencies->format($features['features_monthly_price']);
    } else {
      $features['features_monthly_price'] = '';
    }
    $fInfo = new \objectInfo($features, false);
    if ($fInfo->features_id > 0) {
      if ($messageStack->size() > 0) {
        $this->view->errorMessage = $messageStack->output(true);
        $this->view->errorMessageType = $messageStack->messageType;
      }
      return $this->render('view', array('fInfo' => $fInfo));
    } else {
      $messageStack->add_session('Wrong feature ID!');
      return $this->redirect(Yii::$app->urlManager->createUrl('features/index'));
    }
  }
 
  public function actionInstall() {
    $messageStack = \Yii::$container->get('message_stack');

    $features_id = Yii::$app->request->get('fID', 0);
    $features = tep_db_fetch_array(tep_db_query("select f.features_id, f.features_title, f.features_image, f.features_description, f.features_monthly_price, f.features_setup_price, count(d2f.features_id) as feature_enabled from " . TABLE_FEATURES . " f left join " . TABLE_DEPARTMENTS_TO_FEATURES . " d2f on f.features_id = d2f.features_id and d2f.departments_id = '" . (int)DEPARTMENTS_ID . "' where f.features_id = '" . (int) $features_id . "' group by f.features_id"));
    $fInfo = new \objectInfo($features, false);
    if ($fInfo->features_id > 0) {
      if (!$fInfo->feature_enabled) {
        $response = $this->call_http_url(SUPERADMIN_HTTP_URL . 'rest?dID=' . (int)DEPARTMENTS_ID . '&fID=' . (int)$features_id . '&action=install&http=' . str_replace('http://', '', HTTP_SERVER));
        if ($response == 'OK') {
          $messageStack->add_session($fInfo->features_title . ' has been successfully installed!', 'header', 'success');
        } else {
          $messageStack->add_session($response);
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(['features/view', 'fID' => $features_id]));
      } else {
        $messageStack->add_session($fInfo->features_title . ' is already installed!');
        return $this->redirect(Yii::$app->urlManager->createUrl(['features/view', 'fID' => $features_id]));
      }
    } else {
      $messageStack->add_session('Wrong feature ID!');
      return $this->redirect(Yii::$app->urlManager->createUrl('features/index'));
    }
  }

  public function actionUninstall() {
    $messageStack = \Yii::$container->get('message_stack');

    $features_id = Yii::$app->request->get('fID', 0);
    $features = tep_db_fetch_array(tep_db_query("select f.features_id, f.features_title, f.features_image, f.features_description, f.features_monthly_price, f.features_setup_price, count(d2f.features_id) as feature_enabled from " . TABLE_FEATURES . " f left join " . TABLE_DEPARTMENTS_TO_FEATURES . " d2f on f.features_id = d2f.features_id and d2f.departments_id = '" . (int)DEPARTMENTS_ID . "' where f.features_id = '" . (int) $features_id . "' group by f.features_id"));
    $fInfo = new \objectInfo($features, false);
    if ($fInfo->features_id > 0) {
      if ($fInfo->feature_enabled) {
        $response = $this->call_http_url(SUPERADMIN_HTTP_URL . 'rest?dID=' . (int)DEPARTMENTS_ID . '&fID=' . (int)$features_id . '&action=uninstall&http=' . str_replace('http://', '', HTTP_SERVER));
        if ($response == 'OK') {
          $messageStack->add_session($fInfo->features_title . ' has been successfully uninstalled!', 'header', 'success');
        } else {
          $messageStack->add_session($response);
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(['features/view', 'fID' => $features_id]));
      } else {
        $messageStack->add_session($fInfo->features_title . ' is already uninstalled!');
        return $this->redirect(Yii::$app->urlManager->createUrl(['features/view', 'fID' => $features_id]));
      }
    } else {
      $messageStack->add_session('Wrong feature ID!');
      return $this->redirect(Yii::$app->urlManager->createUrl('features/index'));
    }
  }

  static function call_http_url($url, $username = '', $password = '', $postfields = array()) {
    if ($ch = curl_init()) {
      $url = str_replace('&amp;', '&', $url);
      curl_setopt($ch, CURLOPT_URL, $url);
      if ($username && $password) {
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
      }
      curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      if (is_array($postfields) && count($postfields) > 0) {
        $req = 'post=1';
        foreach ($postfields as $key => $value) {
          $req .= '&' . $key . '=' . urlencode(stripslashes($value));
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
      }
      $response = curl_exec($ch);
      curl_close ($ch);
      return $response;
    }
  }
}

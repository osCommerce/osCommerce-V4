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

use backend\models\ProductNameDecorator;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use common\helpers\Html;

/**
 * Coupon admin controller to handle user requests.
 */
class Coupon_adminController extends Sceleton {

  public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_COUPON_ADMIN'];
  private static $dateOptions = ['active_on', 'start_between', 'end_between'];
  private static $by = [
    [
      'name' => 'TEXT_ANY',
      'value' => '',
      'selected' => '',
    ],
    [
      'name' => 'COUPON_CODE',
      'value' => 'coupon_code',
      'selected' => '',
    ],
    [
      'name' => 'TEXT_COUPON',
      'value' => 'coupon_name',
      'selected' => '',
    ],
    [
      'name' => 'COUPON_DESC',
      'value' => 'coupon_description',
      'selected' => '',
    ],
  ];
  private static $filterFields = ['search' => '', 'date' => '',
    'inactive' => 'intval',
    'pfrom' => 'floatval', 'pto' => 'floatval',
    'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
    ];

  public function beforeAction($action) {
    if (false === \common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) {
      $this->redirect(array('/'));
      return false;
    }
    return parent::beforeAction($action);
  }

  public function actionIndex() {

    \common\helpers\Translation::init('admin/coupon_admin');
    $this->selectedMenu = array('marketing', 'gv_admin', 'coupon_admin');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('coupon_admin/index'), 'title' => HEADING_TITLE);
    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('coupon_admin/voucheredit') . '" class="btn btn-primary">' . IMAGE_INSERT . '</a>';

    $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('coupon_admin/download-sample').'" class="btn btn-primary backup"><i class="icon-file-text"></i>' . TEXT_SAMPLE . '</a>';
    $this->topButtons[] = '<a href="javascript:void(0)" class="btn-import btn btn-primary backup"><i class="icon-file-text"></i>' . IMAGE_UPLOAD . '</a>';

    $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('coupon_admin/voucherreport').'" class="btn btn-primary"><i class="icon-file-text"></i>' . 'Redeem report' . '</a>';

    $this->view->headingTitle = HEADING_TITLE;
    $this->view->couponTable = array(
      array(
        'title' => Html::checkbox('select_all', false, ['id' => 'select_all']),
        'not_important' => 2
      ),
      array(
        'title' => DATE_CREATED,
        'not_important' => 0
      ),
      array(
        'title' => COUPON_CODE,
        'not_important' => 0
      ),
      array(
        'title' => COUPON_NAME,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_START_DATE,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_END_DATE,
        'not_important' => 0
      ),
      array(
        'title' => COUPON_AMOUNT,
        'not_important' => 0
      ),
    );
    $this->view->sortColumns = '1,2,3,4,5,6';

    $this->view->filters = new \stdClass();
    $this->view->filters->row = (int) Yii::$app->request->get('row', 0);
    $gets = Yii::$app->request->get();

    $by = self::$by;
    foreach ($by as $key => $value) {
      $by[$key]['name'] = defined($by[$key]['name'] )? constant($by[$key]['name']):strtolower(str_replace('_', ' ', $by[$key]['name']));
      if (isset($gets['by']) && $value['value'] == $gets['by']) {
        $by[$key]['selected'] = 'selected';
      }
    }
    $this->view->filters->by = $by;
    foreach (self::$dateOptions as $opt) {
      $this->view->filters->dateOptions[$opt] = defined('TEXT_' . strtoupper($opt)) ? constant('TEXT_' . strtoupper($opt)) : strtoupper($opt);
    }

    foreach (self::$filterFields as $v => $f) {
      if (!empty($gets[$v])) {
        if (is_callable($f)) {
          $this->view->filters->{$v} = call_user_func($f, $gets[$v]);
        } elseif (is_array($f) && !empty($f['filter']) && is_callable($f['filter'])) {
          $this->view->filters->{$v} = call_user_func($f['filter'], $gets[$v]);
        } else {
          $this->view->filters->{$v} = $gets[$v];
        }
      } else {
        $this->view->filters->{$v} = '';
      }
    }
    return $this->render('index');
  }

  public function actionList() {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $currencies = Yii::$container->get('currencies');

    \common\helpers\Translation::init('admin/coupon_admin');

    $draw = (int) Yii::$app->request->get('draw', 1);
    $start = (int) Yii::$app->request->get('start', 0);
    $length = (int) Yii::$app->request->get('length', 10);

    $formFilter = Yii::$app->request->get('filter');
    $gets = [];
    parse_str($formFilter, $gets);

    if (isset($gets['date']) && in_array($gets['date'], self::$dateOptions)) {
      $date = $gets['date'];
    } else {
      $date = 'active_on';
    }
    if (isset($gets['by']) && in_array($gets['by'], \yii\helpers\ArrayHelper::getColumn(self::$by, 'value'))) {
      $by = $gets['by'];
    } else {
      $by = '';
    }

    $listQuery = \common\models\Coupons::find()->joinWith(['description'])->select(\common\models\Coupons::tableName() . '.*');
    $inactive = false;

    foreach (self::$filterFields as $v => $f) {
      if (!empty($gets[$v])) {
        if (is_callable($f)) {
          if (is_array($gets[$v])) {
            foreach ($gets[$v] as $k => $vv) {
              $gets[$v][$k] = call_user_func($f, $vv);
            }
            $val = $gets[$v];
          } else {
            $val = call_user_func($f, $gets[$v]);
          }
        } elseif (is_array($f) && !empty($f['list']) && is_callable($f['list'])) {
          $val = call_user_func($f['list'], $gets[$v]);
        } else {
          $val = $gets[$v];
        }

        switch ($v) {
          case 'inactive':
            $inactive = true;
            break;
          case 'pfrom':
            $listQuery->andWhere(['>=', 'coupon_amount', $val]);
            break;
          case 'pto':
            $listQuery->andWhere(['<=', 'coupon_amount', $val]);
            break;
          case 'dfrom':
            if (in_array($date, ['start_between'])) {
              $listQuery->andWhere(['>=', 'coupon_start_date', $val]);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->andWhere([
                'or',
                ['>=', 'coupon_expire_date', $val],
                ['<', 'coupon_expire_date', '1980-01-01']
              ]);
            } else {
              $listQuery->andWhere(['>=', 'coupon_expire_date', $val]);
            }
            break;
          case 'dto':
            if (in_array($date, ['start_between'])) {
              $listQuery->andWhere(['<=', 'coupon_start_date', $val]);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->andWhere(['<=', 'coupon_start_date', $val]);
            } else {
              $listQuery->andWhere(['<=', 'coupon_expire_date', $val]);
            }
            break;
          case 'search':
            if ($by == '') { //all
              $tmp = [];
              foreach (\yii\helpers\ArrayHelper::getColumn(self::$by, 'value') as $field) {
                if (!empty($field) && is_string($field)) {
                  $tmp[] = ['like', $field, $val];
                }
              }
              if (!empty($tmp)) {
                $listQuery->andWhere(array_merge(['or'], $tmp));
              }
            } else {
              $listQuery->andWhere(['like', $by, $val]);
            }
            break;
        }
      }
    }
    if (!$inactive) {
      $listQuery->active();
    }

    $gets = Yii::$app->request->get();
    if (!empty($gets['search']['value'])) {
      $val = $gets['search']['value'];
      $tmp = [];
      foreach (\yii\helpers\ArrayHelper::getColumn(self::$by, 'value') as $field) {
        if (!empty($field) && is_string($field)) {
          $tmp[] = ['like', $field, $val];
        }
      }
      if (!empty($tmp)) {
        $listQuery->andWhere(array_merge(['or'], $tmp));
      }
    }

    if (!empty($gets['order'][0]['column'])) {
      $dir = 'asc';
      if (!empty($gets['order'][0]['dir']) && $gets['order'][0]['dir'] == 'desc') {
        $dir = 'desc';
      }
      switch ($gets['order'][0]['column']) {
        case 1:
          $listQuery->addOrderBy(" date_created " . $dir);
          $listQuery->addOrderBy(" coupon_code ");
          break;
        case 2:
          $listQuery->addOrderBy(" coupon_code " . $dir);
          break;
        case 3:
          $listQuery->addOrderBy(" coupon_name " . $dir);
          break;
        case 4:
          $listQuery->addOrderBy(" coupon_start_date " . $dir);
          break;
        case 5:
          $listQuery->addOrderBy(" coupon_expire_date " . $dir);
          break;
        case 6:
          $listQuery->addOrderBy(" coupon_amount " . $dir);
          break;
        default:
          $listQuery->addOrderBy(" date_created desc ");
          break;
      }
    } else {
      $listQuery->addOrderBy(" date_created desc ");
    }

    $responseList = array();
    if ($length == -1)
      $length = 10000;
    $current_page_number = ( $start / $length ) + 1;
    $query_numrows = $listQuery->count();

    $listQuery->offset($start)->limit($length);
    $listQuery->addSelect('coupon_name, coupon_description');

    if ( !Yii::$app->request->isAjax ){
        $listQuery->select('coupon_code');
        $listQuery->offset(null)->limit(null);
        $coupons = $listQuery->asArray()->all();

        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->sendContentAsFile(
            implode("\r\n",ArrayHelper::getColumn($coupons,'coupon_code')),
            'coupon_codes_'.date('ymd').'.txt',
            ['mimeType'=>'text/plain']
        );
        return;
    }
    $coupons = $listQuery->asArray()->all();

    foreach ($coupons as $coupon) {
      $row = [];
      $row[] = Html::checkbox('bulkProcess[]', false, ['value' => $coupon['coupon_id']])
          . Html::hiddenInput('coupons_' . $coupon['coupon_id'], $coupon['coupon_id'], ['class' => "cell_identify"])
          . ($coupon['coupon_active'] != 'Y' ? Html::hiddenInput('coupons_st' . $coupon['coupon_id'], 'dis_module', ['class' => "tr-status-class"]):'')
          ;

      if ($coupon['date_created'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($coupon['date_created']);
      } else {
        $row[] = '';
      }
       $row[] = $coupon['coupon_code'];
       $row[] = $coupon['coupon_name'];

      if ($coupon['coupon_start_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($coupon['coupon_start_date']);
      } else {
        $row[] = '';
      }
      if ($coupon['coupon_expire_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($coupon['coupon_expire_date']);
      } else {
        $row[] = '';
      }

      $coupon_amount = '';
      if ($coupon['coupon_type'] == 'P') {
        $coupon_amount = number_format($coupon['coupon_amount'], 2) . '%';
      } elseif($coupon['coupon_amount']>0) {
        $coupon_amount = $currencies->format($coupon['coupon_amount'], false, $coupon['coupon_currency']);
      }
      if ($coupon['free_shipping']){
          if ( !empty($coupon_amount) ){
              $coupon_amount .= ' + '.TEXT_FREE_SHIPPING;
          }else{
              $coupon_amount = TEXT_FREE_SHIPPING;
          }
      }
      $row[] = $coupon_amount;

      $responseList[] = $row;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $query_numrows,
      'recordsFiltered' => $query_numrows,
      'data' => $responseList
    );
    echo json_encode($response);
  }

  public function actionItempreedit() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/coupon_admin');

    $currencies = Yii::$container->get('currencies');

    $this->layout = false;

    $item_id = (int) Yii::$app->request->post('item_id', 0);

    $cInfo = \common\models\Coupons::find()->andWhere(['coupon_id' => (int) $item_id])->one();
    if (!$cInfo) {
      die();
    }

    echo '<div class="or_box_head">[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . '</div>';

    if ($cInfo->coupon_type == 'P') {
      $amount = number_format($cInfo->coupon_amount, 2) . '%';
    } else {
      $amount = $currencies->format($cInfo->coupon_amount, false, $cInfo->coupon_currency);
    }

    $prod_details = TEXT_NONE;
    $cat_details = TEXT_NONE;
    $prodExDetails = TEXT_NONE;
    $catExDetails = TEXT_NONE;
    if ($cInfo->exclude_products) {
      $prodExDetails = '<a href="#exclude_products" class="popUp" id="excProducts">' . IMAGE_VIEW . '</a>' .
          '<div id="exclude_products" style="display: none">' . (\common\helpers\Product::getAdminDetailsList($cInfo->exclude_products)) .
            '<script type="text/javascript">(function($){$(function(){$(\'#excProducts\').popUp();})})(jQuery)</script>' .
          '</div>'
          ;
    }
    if ($cInfo->restrict_to_products) {
      $prod_details = '<a href="#include_products" class="popUp" id="incProducts">' . IMAGE_VIEW . '</a>' .
          '<div id="include_products" style="display: none">' . (\common\helpers\Product::getAdminDetailsList($cInfo->restrict_to_products)) .
            '<script type="text/javascript">(function($){$(function(){$(\'#incProducts\').popUp();})})(jQuery)</script>' .
          '</div>'
          ;
    }
    if ($cInfo->exclude_categories) {
      $catExDetails = '<a href="#exclude_cats" class="popUp" id="excCats">' . IMAGE_VIEW . '</a>' .
          '<div id="exclude_cats" style="display: none">' . (\common\helpers\Categories::getAdminDetailsList($cInfo->exclude_categories)) .
            '<script type="text/javascript">(function($){$(function(){$(\'#excCats\').popUp();})})(jQuery)</script>' .
          '</div>';
    }
    if ($cInfo->restrict_to_categories) {
      $cat_details = '<a href="#include_cats" class="popUp" id="incCats">' . IMAGE_VIEW . '</a>' .
          '<div id="include_cats" style="display: none">' . (\common\helpers\Categories::getAdminDetailsList($cInfo->restrict_to_categories)) .
            '<script type="text/javascript">(function($){$(function(){$(\'#incCats\').popUp();})})(jQuery)</script>' .
          '</div>';
    }
    $coupon_name_query = tep_db_query("select coupon_description from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $cInfo->coupon_id . "' and language_id = '" . $languages_id . "'");
    $coupon_name = tep_db_fetch_array($coupon_name_query);

    if ($cInfo->tax_class_id == -1) {
        $taxcClass = TEXT_BY_ORDER_TAXES;
    } elseif ($cInfo->tax_class_id) {
        $taxcClass = \common\helpers\Tax::get_tax_class_title($cInfo->tax_class_id);
    } else {
        $taxcClass = TEXT_NONE;
    }

    echo '<div class="row_or_wrapp">';
    echo '<div class="row_or"><div>' . COUPON_DESC . ':</div><div>' . $coupon_name['coupon_description'] . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_AMOUNT . ':</div><div>' . $amount . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_STARTDATE . ':</div><div>' . \common\helpers\Date::date_short($cInfo->coupon_start_date) . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_FINISHDATE . ':</div><div>' . \common\helpers\Date::date_short($cInfo->coupon_expire_date) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_RESTRICT_TO_CUSTOMERS . ':</div><div>' . $cInfo->restrict_to_customers . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_USES_COUPON . ':</div><div>' . $cInfo->uses_per_coupon . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_USES_USER . ':</div><div>' . $cInfo->uses_per_user . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_PRODUCTS . ':</div><div>' . $prod_details . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_CATEGORIES . ':</div><div>' . $cat_details . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_EXCLUDE_PRODUCTS . ':</div><div>' . $prodExDetails . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_EXCLUDE_CATEGORIES . ':</div><div>' . $catExDetails . '</div></div>';
    echo '<div class="row_or"><div>' . COUPON_USES_SHIPPING . ':</div><div>' . ($cInfo->uses_per_shipping ? TEXT_BTN_YES : TEXT_BTN_NO) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_PRODUCTS_TAX_CLASS . ':</div><div>' . $taxcClass . '</div></div>';
    echo '<div class="row_or"><div>' . DATE_CREATED . ':</div><div>' . \common\helpers\Date::date_short($cInfo->date_created) . '</div></div>';
    echo '<div class="row_or"><div>' . DATE_MODIFIED . ':</div><div>' . \common\helpers\Date::date_short($cInfo->date_modified) . '</div></div>';
    echo '</div>';
    echo '<div class="btn-toolbar btn-toolbar-order">';
    echo '<a href="' . tep_href_link('coupon_admin/couponemail', 'cid=' . $cInfo->coupon_id, 'NONSSL') . '" class="btn btn-email-cus btn-no-margin">' . TEXT_EMAIL . '</a>';
    echo '<a href="' . Yii::$app->urlManager->createUrl(['coupon_admin/voucheredit', 'cid' => $cInfo->coupon_id]) . '" class="btn btn-edit">' . TEXT_EDIT . '</a>';
    echo '<a href="javascript:void(0)" onclick="deleteItemConfirm(' . $cInfo->coupon_id . ')" class="btn btn-delete btn-no-margin">' . TEXT_DELETE . '</a>';
    echo '<a href="' . tep_href_link('coupon_admin/voucherreport', 'cid=' . $cInfo->coupon_id, 'NONSSL') . '" class="btn btn-ord-cus">' . TEXT_REPORT . '</a>';
    echo '</div>';
  }

  public function actionVoucheredit() {
      global $languages_id;
    \common\helpers\Translation::init('admin/coupon_admin');

    $this->selectedMenu = array('marketing', 'gv_admin', 'coupon_admin');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('coupon_admin/index'), 'title' => HEADING_TITLE);

    $cid = (int) Yii::$app->request->get('cid');
    $coupon_name = [];
    $coupon_desc = [];

    $languages = \common\helpers\Language::get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $languages[$i]['logo'] = $languages[$i]['image'];

      $language_id = $languages[$i]['id'];
      $coupon_query = tep_db_query("select coupon_name,coupon_description from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $cid . "' and language_id = '" . $language_id . "'");
      $coupon = tep_db_fetch_array($coupon_query);
      if (isset($coupon['coupon_name'])) {
        $coupon_name[$language_id] = $coupon['coupon_name'];
      } else {
        $coupon_name[$language_id] = '';
      }
      if (isset($coupon['coupon_description'])) {
        $coupon_desc[$language_id] = $coupon['coupon_description'];
      } else {
        $coupon_desc[$language_id] = '';
      }
    }

    $coupon_free_ship = false;
    $coupon = \common\models\Coupons::findOne(['coupon_id' => $cid]);
    if ($coupon) {
      if ($coupon['coupon_type'] == 'P') {
        $coupon['coupon_amount'] = number_format($coupon['coupon_amount'], 2) . '%';
      }
    } else {
      $coupon = [
        'coupon_amount' => '',
        'coupon_currency' => DEFAULT_CURRENCY,
        'free_shipping' => 0,
        'coupon_minimum_order' => '',
        'coupon_code' => '',
        'coupon_for_recovery_email' => 0,
        'pos_only' => 0,
        'spend_partly' => 0,
        'uses_per_coupon' => '',
        'uses_per_user' => '',
        'uses_per_shipping' => '',
        'flag_with_tax' => 0,
        'restrict_to_products' => '',
        'products_max_allowed_qty' => '',
        'products_id_per_coupon' => '',
        'restrict_to_categories' => '',
        'restrict_to_manufacturers' => '',
        'coupon_groups' => '',
        'restrict_to_countries' => '',
        'tax_class_id' => 0,
        'coupon_start_date' => date('Y-m-d'),
        'coupon_expire_date' => date('Y-m-d', strtotime('+ 1 month')),
      ];
    }
    $coupon_currency = tep_draw_pull_down_menu('coupon_currency', \common\helpers\Currencies::get_currencies(1), $coupon['coupon_currency'], 'class="form-control"');

    $csvImportedData = \common\models\CouponsCustomerCodesList::find()->andWhere(['coupon_id' => (int) $cid])->one();

    if (!empty($coupon['check_platforms'])) {
        $this->view->platform_assigned = \common\models\CouponsToPlatform::find()
            ->select(['platform_id'])
            ->andWhere(['coupon_id' => (int) $cid])
            ->asArray()
            ->indexBy('platform_id')
            ->column();
    }


    $restrict_to_products_names = '';
    $products = \common\models\ProductsDescription::find()
        ->select(['products_name'])
        ->where(['IN', 'products_id', explode(',', $coupon['restrict_to_products'])])
        ->andWhere(['language_id' => $languages_id])
        ->andWhere(['platform_id' => \common\classes\platform::defaultId()])
        ->asArray()->all();
    foreach ($products as $product) {
        if ($product['products_name']) {
            $restrict_to_products_names .= '- ' . addslashes($product['products_name']) . "\n";
        }
    }
      if (!$restrict_to_products_names) $restrict_to_products_names = defined("TEXT_ALL") ? TEXT_ALL : 'All';

      $restrict_to_categories_names = '';
      $categories = \common\models\CategoriesDescription::find()
          ->select(['categories_name'])
          ->where(['IN', 'categories_id', explode(',', $coupon['restrict_to_categories'])])
          ->andWhere(['language_id' => $languages_id])
          ->asArray()->all();
      foreach ($categories as $category) {
          if ($category['categories_name']) {
              $restrict_to_categories_names .= '- ' . addslashes($category['categories_name']) . "\n";
          }
      }
      if (!$restrict_to_categories_names) $restrict_to_categories_names = defined("TEXT_ALL") ? TEXT_ALL : 'All';

      $exclude_products_names = '';
      $products = \common\models\ProductsDescription::find()
          ->select(['products_name'])
          ->where(['IN', 'products_id', explode(',', $coupon['exclude_products'] ?? null)])
          ->andWhere(['language_id' => $languages_id])
          ->andWhere(['platform_id' => \common\classes\platform::defaultId()])
          ->asArray()->all();
      foreach ($products as $product) {
          if ($product['products_name']) {
              $exclude_products_names .= '- ' . addslashes($product['products_name']) . "\n";
          }
      }
      if (!$exclude_products_names) $exclude_products_names = defined("OPTION_NONE") ? OPTION_NONE : 'None';

      $exclude_categories_names = '';
      $categories = \common\models\CategoriesDescription::find()
          ->select(['categories_name'])
          ->where(['IN', 'categories_id', explode(',', $coupon['exclude_categories'] ?? null)])
          ->andWhere(['language_id' => $languages_id])
          ->asArray()->all();
      foreach ($categories as $category) {
          if ($category['categories_name']) {
              $exclude_categories_names .= '- ' . addslashes($category['categories_name']) . "\n";
          }
      }
      if (!$exclude_categories_names) $exclude_categories_names = defined("OPTION_NONE") ? OPTION_NONE : 'None';

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_voucher_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';
      if (!$cid) {
          $this->topButtons[] = '<span class="btn btn-primary js-batch-create">' . TEXT_SAVE_BATCH . '</span>';
      }

    return $this->render('voucheredit', [
          'restrict_to_products_names' => $restrict_to_products_names,
          'restrict_to_categories_names' => $restrict_to_categories_names,
          'exclude_products_names' => $exclude_products_names,
          'exclude_categories_names' => $exclude_categories_names,
          'cid' => $cid,
          'languages' => $languages,
          'coupon_name' => $coupon_name,
          'coupon_desc' => $coupon_desc,
          'coupon_for_recovery_email' => $coupon['coupon_for_recovery_email'],
          'pos_only' => $coupon['pos_only'],
          'spend_partly' => $coupon['spend_partly'],
          'coupon_currency' => $coupon_currency,
          'coupon' => $coupon,
          'coupon_start_date' => ($coupon['coupon_start_date'] > 0 ? \common\helpers\Date::date_short($coupon['coupon_start_date']) : ''),
          'coupon_expire_date' => ($coupon['coupon_expire_date'] > 0 ? \common\helpers\Date::date_short($coupon['coupon_expire_date']) : ''),
        'has_csv_data' => $csvImportedData ? true : false,
        'customers_coupons_csv' => \common\helpers\Coupon::getCustomersCouponsEmailsList($cid),
        'coupon_taxes' => [-1 => TEXT_BY_ORDER_TAXES, 0 => TEXT_NONE] + \common\models\TaxClass::find()
                ->select('tax_class_title, tax_class_id')->orderBy('tax_class_title')
                ->asArray()->indexBy('tax_class_id')->column(),
    ]);
  }

  public function actionVoucherSubmit() {

    \common\helpers\Translation::init('admin/coupon_admin');

    $cid = (int) Yii::$app->request->post('coupon_id');
    $coupon_count = (int)Yii::$app->request->post('coupon_count',1);
    if ( $coupon_count<=0 ) $coupon_count = 1;

    $check_platforms = (int)Yii::$app->request->post('check_platforms', 0);

    $coupon_startdate = '0';
    if (!empty($_POST['coupon_startdate'])) {
      $coupon_startdate = \common\helpers\Date::prepareInputDate($_POST['coupon_startdate']);
    }
    $coupon_finishdate = '0';
    if (!empty($_POST['coupon_finishdate'])) {
      $coupon_finishdate = \common\helpers\Date::prepareInputDate($_POST['coupon_finishdate']);
    }

    $coupon_code = tep_db_prepare_input($_POST['coupon_code'], false);
    $batch_coupon_code_prefix = trim($coupon_code);
    if (trim($coupon_code) === '') {
      $coupon_code = \common\helpers\Coupon::create_coupon_code();
    } else {
      //2do check for duplicate active coupon codes
    }

    $batch_mode = $coupon_count>1 && empty($cid);
    $couponCSVLoadedCouponFileName = Yii::$app->request->post('coupon_csv_loaded', false);
    if ($couponCSVLoadedCouponFileName ) {
      $coupon_code = '';
      $batch_mode = false;
      $coupon_count = 1;
    }

    $languages = \common\helpers\Language::get_languages();

    $batch_range = [0,0];

    do {
        if ( $batch_mode ) $coupon_code = \common\helpers\Coupon::create_prefixed_code($batch_coupon_code_prefix);

    $coupon_type = "F";
    if (substr(\Yii::$app->request->post('coupon_amount'), -1) == '%') {
      $coupon_type = 'P';
    }

    $sql_data_array = array('coupon_code' => $coupon_code,
      'check_platforms' => $check_platforms,
      'coupon_amount' => tep_db_prepare_input(\Yii::$app->request->post('coupon_amount')),
      'coupon_currency' => tep_db_prepare_input(\Yii::$app->request->post('coupon_currency')),
      'coupon_type' => $coupon_type,
      'free_shipping' => \Yii::$app->request->post('free_shipping', 0)?1:0,
      'uses_per_coupon' => tep_db_prepare_input(\Yii::$app->request->post('uses_per_coupon')),
      'uses_per_user' => tep_db_prepare_input(\Yii::$app->request->post('uses_per_user')),
      'single_per_order' => (int)Yii::$app->request->post('single_per_order', 0),
      'uses_per_shipping' => tep_db_prepare_input(\Yii::$app->request->post('uses_per_shipping')),
      'coupon_minimum_order' => tep_db_prepare_input(\Yii::$app->request->post('coupon_minimum_order')),
      'restrict_to_products' => tep_db_prepare_input(\Yii::$app->request->post('restrict_to_products')),
      'restrict_to_categories' => tep_db_prepare_input(\Yii::$app->request->post('restrict_to_categories')),
      'restrict_to_customers' => tep_db_prepare_input(\Yii::$app->request->post('restrict_to_customers')),
      'exclude_products' => tep_db_prepare_input(\Yii::$app->request->post('exclude_products')),
      'exclude_categories' => tep_db_prepare_input(\Yii::$app->request->post('exclude_categories')),
      'products_max_allowed_qty' => ((int)\Yii::$app->request->post('products_max_allowed_qty')>0?intval(\Yii::$app->request->post('products_max_allowed_qty')):''),
      'products_id_per_coupon' => ((int)\Yii::$app->request->post('products_id_per_coupon')>0?intval(\Yii::$app->request->post('products_id_per_coupon')):''),
      'disable_for_special' => intval(\Yii::$app->request->post('disable_for_special')),
      'coupon_for_recovery_email' => intval(\Yii::$app->request->post('coupon_for_recovery_email')),
      'pos_only' => intval(\Yii::$app->request->post('pos_only', 0)),
      'spend_partly' => intval(\Yii::$app->request->post('spend_partly')),
      'coupon_start_date' => $coupon_startdate,
      'coupon_expire_date' => $coupon_finishdate,
      'date_created' => 'now()',
      'date_modified' => 'now()',
      'restrict_to_manufacturers' => implode(',', tep_db_prepare_input(\Yii::$app->request->post('restrict_to_manufacturers') ?? [])),
      'coupon_groups' => implode(',', tep_db_prepare_input(\Yii::$app->request->post('coupon_groups') ?? [])),
      'restrict_to_countries' => implode(",", tep_db_prepare_input(\Yii::$app->request->post('restrict_to_countries') ?? [])),
      'tax_class_id' => tep_db_prepare_input(\Yii::$app->request->post('configuration_value')),
      'flag_with_tax' => tep_db_prepare_input(\Yii::$app->request->post('flag_with_tax')),);

    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $sql_data_marray[$i] = array('coupon_name' => tep_db_prepare_input(\Yii::$app->request->post('coupon_name')[$language_id] ?? null),
        'coupon_description' => tep_db_prepare_input(\Yii::$app->request->post('coupon_description')[$language_id] ?? null)
      );
    }

    if ($cid > 0) {
      unset($sql_data_array['date_created']);
      tep_db_perform(TABLE_COUPONS, $sql_data_array, 'update', "coupon_id='" . $cid . "'");
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $check_lang = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int) $cid . "' AND language_id = '" . (int) $language_id . "'"
        ));
        if ($check_lang['c'] > 0) {
          tep_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i], 'update', "coupon_id = '" . (int) $cid . "' AND language_id = '" . (int) $language_id . "'");
        } else {
//            $update = tep_db_query("insert into " . TABLE_COUPONS_DESCRIPTION . " set coupon_name = '" . tep_db_prepare_input($_POST['coupon_name'][$language_id]) . "', coupon_description = '" . tep_db_prepare_input($_POST['coupon_desc'][$language_id]) . "', coupon_id = '" . $cid . "', language_id = '" . $language_id . "'");
          $sql_data_marray[$i]['coupon_id'] = $cid;
          $sql_data_marray[$i]['language_id'] = $language_id;

          tep_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
        }
      }
    } else {
      tep_db_perform(TABLE_COUPONS, $sql_data_array);
      $cid = tep_db_insert_id();

      if ($couponCSVLoadedCouponFileName != false) {
        $res = \common\helpers\Coupon::saveCSVCustomersCoupons($couponCSVLoadedCouponFileName, $cid);
        if (!$res) {
          //empty incorrect file upoloaded - delete coupon
          \common\models\Coupons::deleteAll(['coupon_id' => $cid]);
          $cid = 0;
          $message = TEXT_COUPON_INCORRECT_DUPLICATE_FILE;
        }
      }
      if ($cid) {
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $sql_data_marray[$i]['coupon_id'] = $cid;
          $sql_data_marray[$i]['language_id'] = $language_id;
          tep_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
        }
      }

    }

        //coupon to platforms
        try {
            \common\models\CouponsToPlatform::deleteAll('coupon_id =  :cid ', [':cid' => (int)$cid]);
            if ($check_platforms > 0) {

                $platforms = Yii::$app->request->post('platform', []);
                if (is_array($platforms)) {
                    $platforms = array_unique(array_map('intval', $platforms));
                    \Yii::$app->db->createCommand(
                        'insert into ' . \common\models\CouponsToPlatform::tableName() .
                        ' (coupon_id, platform_id) ' .
                            \common\models\Platforms::find()
                            ->select([new \yii\db\Expression((int)$cid), 'platform_id'])
                            ->andwhere(['platform_id' => $platforms])
                            ->createCommand()->rawSql
                        )->execute();
                }
            }

        } catch ( \Exception $e) {
            \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG');
        }

        foreach (\common\helpers\Hooks::getList('coupon_admin/voucher-submit') as $filename) {
          include($filename);
        }

        if ( $batch_mode ) {
            $batch_range[1] = $cid;
            if (empty($batch_range[0])){
                $batch_range[0] = $cid;
            }
            $cid = 0;
        }else{
            break;
        }
        $coupon_count--;
    }while( $batch_mode && $coupon_count>0 );

    $message = TEXT_COUPON_UPDATED_NOTICE;
    if ( $batch_mode ) {
        $message .= '<br>'.
            Html::beginForm(['export-codes'],'post',['target'=>'_blank']).
            Html::hiddenInput('batch_range_1', $batch_range[0]).
            Html::hiddenInput('batch_range_2', $batch_range[1]).
            'Download generated coupon codes <button type="submit" class="btn"><i class="icon-download"></i> Download</button>'.
            Html::endForm()
        ;
    }
    $messageType = 'success';
    if ( !$batch_mode && $cid==0){
      $messageType = 'error';
    }
    ?>
    <div class="popup-box-wrap pop-mess">
      <div class="around-pop-up"></div>
      <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
          <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
          <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
    <?= $message ?>
          </div>
        </div>
        <div class="noti-btn">
          <div></div>
          <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
        </div>
      </div>
      <script>
        $('body').scrollTop(0);
        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
          $(this).parents('.pop-mess').remove();
          <?php if ($batch_mode) { ?>
            window.location.href="<?php echo Yii::$app->urlManager->createUrl(['coupon_admin/index']);?>";
          <?php } ?>
        });
      </script>
    </div>
    <?php
      if ( $batch_mode ){
          //echo '<script>setTimeout(function(){ window.location.href="' . Yii::$app->urlManager->createUrl(['coupon_admin/index']) . '";}, 1000);</script>';
      }else {
          echo '<script>setTimeout(function(){ window.location.href="' . Yii::$app->urlManager->createUrl(['coupon_admin/voucheredit', 'cid' => $cid]) . '";}, 2000);</script>';
      }
  }

  public function actionExportCodes()
  {
      $this->layout = false;

      $range1 = Yii::$app->request->post('batch_range_1');
      $range2 = Yii::$app->request->post('batch_range_2');
      $code_array = \common\models\Coupons::find()
          ->select('coupon_code')
          ->where(['>=','coupon_id',(int)$range1])
          ->andWhere(['<=','coupon_id',(int)$range2])
          ->orderBy(['coupon_id'=>SORT_ASC])
          ->asArray()
          ->all();

      $coupon_name = \common\models\CouponsDescription::find()
          ->where(['coupon_id'=>(int)$range1])
          ->andWhere(['language_id'=>\Yii::$app->settings->get('languages_id') ])
          ->select('coupon_name')
          ->scalar();

      Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
      Yii::$app->response->sendContentAsFile(
              implode("\r\n",ArrayHelper::getColumn($code_array,'coupon_code')),
              empty($coupon_name)?'coupon_codes_'.date('ymd').'.txt':'coupon_codes_'.$coupon_name.'_'.date('ymd').'.txt',
              ['mimeType'=>'text/plain']
      );
  }

  public function actionConfirmitemdelete() {
    \common\helpers\Translation::init('admin/coupon_admin');

    $this->layout = false;

    $item_id = (int) Yii::$app->request->post('item_id');

    $cc_query = tep_db_query("select coupon_id, coupon_code, coupon_amount, coupon_currency, coupon_type, coupon_start_date,coupon_expire_date,uses_per_user,uses_per_coupon,restrict_to_products, restrict_to_categories, date_created,date_modified from " . TABLE_COUPONS . " where coupon_id = '" . (int) $item_id . "'");
    $cc_list = tep_db_fetch_array($cc_query);
    $cInfo = new \objectInfo($cc_list);

    echo tep_draw_form('item_delete', 'coupon_admin', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
    echo '<div class="or_box_head">' . '[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . '</div>';
    echo '<div class="col_desc">' . TEXT_CONFIRM_DELETE . '</div>';
    echo '<div class="btn-toolbar btn-toolbar-order">';
    echo '<button class="btn btn-no-margin btn-delete">' . IMAGE_DELETE . '</button>';
    echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
    echo '</div>';
    echo tep_draw_hidden_field('item_id', $item_id);
    echo '</form>';
  }

  public function actionItemdelete() {
    $item_id = (int) Yii::$app->request->post('item_id');
    tep_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id='" . $item_id . "'");
  }

  public function actionVoucherreport() {
    // $this->view->headingTitle = HEADING_TITLE1;
    $this->view->headingTitle = 'Reedem report';
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('coupon_admin/index'), 'title' => 'Reedem report');

    $this->selectedMenu = array('marketing', 'gv_admin', 'coupon_admin');

    \common\helpers\Translation::init('admin/coupon_admin');
    //$this->layout = false;
    $coupon_id = intval(Yii::$app->request->get('cid', 0));
    $this->view->catalogTable = array(
      array(
        'title' => CUSTOMER_NAME,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_ORDER_ID,
        'not_important' => 0
      ),
      array(
        'title' => IP_ADDRESS,
        'not_important' => 0
      ),
      array(
        'title' => REDEEM_DATE,
        'not_important' => 0
      ),
        array(
            'title' => 'Discount Amount',
            'not_important' => 0
        ),
    );
    if ( empty($coupon_id) ){
        array_splice($this->view->catalogTable,4, null, array(array('title'=>'Coupon code', 'not_important' => 0)));
    }
    $this->view->filters = new \stdClass();
    $this->view->filters->coupon_id = (int) Yii::$app->request->get('cid');
    $this->view->row_id = (int) Yii::$app->request->get('row');

      $gets = Yii::$app->request->get();
      $by = self::$by;
      foreach ($by as $key => $value) {
          $by[$key]['name'] = defined($by[$key]['name'] )? constant($by[$key]['name']):strtolower(str_replace('_', ' ', $by[$key]['name']));
          if (isset($gets['by']) && $value['value'] == $gets['by']) {
              $by[$key]['selected'] = 'selected';
          }
      }
      $this->view->filters->by = $by;
      foreach (self::$dateOptions as $opt) {
          $this->view->filters->dateOptions[$opt] = defined('TEXT_' . strtoupper($opt)) ? constant('TEXT_' . strtoupper($opt)) : strtoupper($opt);
      }

      foreach (['search' => '',
//                'date' => '',
//                'pfrom' => 'floatval', 'pto' => 'floatval',
//                'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
//                'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
               ] as $v => $f) {
          if (!empty($gets[$v])) {
              if (is_callable($f)) {
                  $this->view->filters->{$v} = call_user_func($f, $gets[$v]);
              } elseif (is_array($f) && !empty($f['filter']) && is_callable($f['filter'])) {
                  $this->view->filters->{$v} = call_user_func($f['filter'], $gets[$v]);
              } else {
                  $this->view->filters->{$v} = $gets[$v];
              }
          } else {
              $this->view->filters->{$v} = '';
          }
      }

    return $this->render('voucherreport', array(
          'coupon_id' => $coupon_id,
    ));
  }

  public function actionReportUsageList() {

    $this->layout = false;

    \common\helpers\Translation::init('admin/coupon_admin');

    $formFilter = Yii::$app->request->get('filter');
    $output = [];
    parse_str($formFilter, $output);

    $filter = '';

    $coupon_id = intval($output['cid']);
    if ( $coupon_id>0 ){
        $filter = " AND crt.coupon_id = '" . (int) $coupon_id . "' ";
    }

    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);

    if ($length == -1)
      $length = 10000;
    $query_numrows = 0;
    $responseList = array();

      $join_description = false;
      $gets = $output;
      if (isset($gets['date']) && in_array($gets['date'], self::$dateOptions)) {
          $date = $gets['date'];
      } else {
          $date = 'active_on';
      }
      if (isset($gets['by']) && in_array($gets['by'], \yii\helpers\ArrayHelper::getColumn(self::$by, 'value'))) {
          $by = $gets['by'];
      } else {
          $by = '';
      }
      foreach (['search' => '',
//                   'date' => '',
//                   'pfrom' => 'floatval', 'pto' => 'floatval',
//                   'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
//                   'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
               ] as $v => $f) {
          if (!empty($gets[$v])) {
              if (is_callable($f)) {
                  if (is_array($gets[$v])) {
                      foreach ($gets[$v] as $k => $vv) {
                          $gets[$v][$k] = call_user_func($f, $vv);
                      }
                      $val = $gets[$v];
                  } else {
                      $val = call_user_func($f, $gets[$v]);
                  }
              } elseif (is_array($f) && !empty($f['list']) && is_callable($f['list'])) {
                  $val = call_user_func($f['list'], $gets[$v]);
              } else {
                  $val = $gets[$v];
              }
              switch ($v) {
                  case 'search':
                      if ($by == '') { //all
                          $tmp = [];
                          foreach (\yii\helpers\ArrayHelper::getColumn(self::$by, 'value') as $field) {
                              if (!empty($field) && is_string($field)) {
                                  //$tmp[] = ['like', $field, $val];
                                  $by_prefix = 'cc.';
                                  if ( in_array($field,['coupon_name','coupon_description'])) $by_prefix = 'ccd.';
                                  $tmp[] = " {$by_prefix}{$field} like '%".tep_db_input($val)."%' ";
                              }
                          }
                          if (!empty($tmp)) {
                              //$listQuery->andWhere(array_merge(['or'], $tmp));
                              $filter .= " AND (".implode("or",$tmp).") ";
                          }
                      } else {
                          //$listQuery->andWhere(['like', $by, $val]);
                          $by_prefix = 'cc.';
                          if ( in_array($by,['coupon_name','coupon_description'])) $by_prefix = 'ccd.';
                          $filter .= " AND {$by_prefix}{$by} LIKE '%".tep_db_input($val)."%' ";
                      }
                      $join_description = true;
                      break;
              }
          }
      }


    if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
      $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
      $filter .= "AND (crt.redeem_ip LIKE '%{$keywords}%' OR c.customers_firstname LIKE '%{$keywords}%' OR c.customers_lastname LIKE '%{$keywords}%') ";
    }

    if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
      $_dir = $_GET['order'][0]['dir'] == 'asc' ? 'asc' : 'desc';
      switch ($_GET['order'][0]['column']) {
        case 0:
          $orderBy = "c.customers_firstname {$_dir}, c.customers_lastname {$_dir} ";
          break;
        case 1:
          $orderBy = "crt.order_id {$_dir} ";
          break;
        case 2:
          $orderBy = "redeem_ip {$_dir} ";
          break;
        default:
          $orderBy = "redeem_date {$_dir}";
          break;
      }
    } else {
      $orderBy = "redeem_date desc";
    }

    $cc_query_raw = "select distinct crt.*, " .
        " cc.coupon_code, ".
        " c.customers_id, c.customers_firstname, c.customers_lastname, " .
        " ot_coupon.value_inc_tax as discount_value, ot_coupon.text_inc_tax as discount_text, ".
        " o.orders_id " .
        "from " . TABLE_COUPON_REDEEM_TRACK . " crt " .
        " inner join ".TABLE_COUPONS." cc ON cc.coupon_id=crt.coupon_id ".
        ($join_description?
            " inner join ".TABLE_COUPONS_DESCRIPTION." ccd ON cc.coupon_id=ccd.coupon_id and ccd.language_id='".\Yii::$app->settings->get('languages_id')."' ":
            ''
        ).
        " left join " . TABLE_CUSTOMERS . " c ON c.customers_id=crt.customer_id " .
        " left join " . TABLE_ORDERS . " o ON o.orders_id=crt.order_id " .
        " left join " . TABLE_ORDERS_TOTAL . " ot_coupon ON o.orders_id=ot_coupon.orders_id and ot_coupon.class='ot_coupon' and abs(crt.spend_amount-ot_coupon.value_inc_tax)<=0.01 " .
        "where 1 " .
        "{$filter} " .
        "order by {$orderBy}";
    //"select coupon_id, coupon_code, coupon_amount, coupon_currency, coupon_type, coupon_start_date,coupon_expire_date,uses_per_user,uses_per_coupon,restrict_to_products, restrict_to_categories, date_created,date_modified from " . TABLE_COUPONS . " $search_condition order by $orderBy ";

    if (!Yii::$app->request->isAjax) {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->setDownloadHeaders('Redeemed Codes.csv','application/vnd.ms-excel');
        $writer = new \backend\models\EP\Writer\CSV(['filename'=>'php://output', 'output_encoding'=>'UTF-8']);
        $writer->setColumns([
            'customers_firstname' => 'Customer Firstname',
            'customers_lastname' => 'Customer Firstname',
            'orders_id' => 'Order Id',
            'redeem_date' => 'Redeem date',
            'coupon_code' => 'Coupon Code',
            'discount_value' => 'Discount Amount',
            'redeems_count' => 'Overall Code Reedem Count',
        ]);
        $cc_query = tep_db_query($cc_query_raw);
        $redeem_counter = [];
        while ($cc_list = tep_db_fetch_array($cc_query)) {
            if ( !isset($redeem_counter[$cc_list['coupon_id']]) ){
                $count_redemptions = tep_db_fetch_array(tep_db_query(
                    "select count(*) as cnt from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $cc_list['coupon_id'] . "'"
                ));
                $redeem_counter[$cc_list['coupon_id']] = (int)$count_redemptions['cnt'];
            }
            $cc_list['redeems_count'] = $redeem_counter[$cc_list['coupon_id']];
            $writer->write($cc_list);
        }
        return;
    }
    $current_page_number = ( $start / $length ) + 1;
    $_split = new \splitPageResults($current_page_number, $length, $cc_query_raw, $query_numrows, 'unique_id');
    $cc_query = tep_db_query($cc_query_raw);
    while ($cc_list = tep_db_fetch_array($cc_query)) {

      $responseRow = array(
        ($cc_list['customers_id'] ? '<a target="_blank" href="' . Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $cc_list['customers_id']]) . '">' . $cc_list['customers_firstname'] . ' ' . $cc_list['customers_lastname'] . '</a>' : $cc_list['customer_id']) .
        '<input class="cell_identify" type="hidden" value="' . $cc_list['unique_id'] . '">',
        ($cc_list['orders_id'] ? '<a target="_blank" href="' . Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $cc_list['orders_id']]) . '">' . $cc_list['order_id'] . '</a>' : $cc_list['order_id']),
        $cc_list['redeem_ip'],
        \common\helpers\Date::date_short($cc_list['redeem_date']),
          (string)$cc_list['discount_text'],
      );
        if ( empty($coupon_id) ){
            array_splice($responseRow,4, null, array($cc_list['coupon_code']));
        }
        $responseList[] = $responseRow;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $query_numrows,
      'recordsFiltered' => $query_numrows,
      'data' => $responseList
    );
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    Yii::$app->response->data = $response;
  }

  public function actionReportUsageInfo() {
    \common\helpers\Translation::init('admin/coupon_admin');
    $this->layout = false;

    $item_id = Yii::$app->request->post('item_id');
    $redeem_info = tep_db_fetch_array(tep_db_query(
            "SELECT crt.*, cd.coupon_name " .
            "FROM " . TABLE_COUPON_REDEEM_TRACK . " crt " .
            " LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON cd.coupon_id=crt.coupon_id AND cd.language_id='" . \Yii::$app->settings->get('languages_id') . "' " .
            "WHERE crt.unique_id='" . (int) $item_id . "'"
    ));

    $count_redemptions = tep_db_fetch_array(tep_db_query(
            "select count(*) as cnt from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . ArrayHelper::getValue($redeem_info, 'coupon_id') . "'"
    ));
    $redemptions_total = $count_redemptions['cnt'];

    $count_customers = tep_db_fetch_array(tep_db_query(
            "select count(*) as cnt from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . ArrayHelper::getValue($redeem_info, 'coupon_id') . "' and customer_id = '" . ArrayHelper::getValue($redeem_info, 'customer_id') . "'"
    ));
    $redemptions_customer = $count_customers['cnt'];

    echo '<div class="or_box_head">' . '[' . ArrayHelper::getValue($redeem_info, 'coupon_id') . ']' . COUPON_NAME . ' ' . ArrayHelper::getValue($redeem_info, 'coupon_name') . '</div>';
    echo '<div class="row_or_wrapp">';
    echo '<div class="row_or">' . '<b>' . TEXT_REDEMPTIONS . '</b>' . '</div>';
    echo '<div class="row_or"><div>' . TEXT_REDEMPTIONS_TOTAL . '</div><div>' . $redemptions_total . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_REDEMPTIONS_CUSTOMER . '=</div><div>' . $redemptions_customer . '</div></div>';
    echo '</div>';
  }

  public function actionCouponemail() {
    $messageStack = \Yii::$container->get('message_stack');
    $this->selectedMenu = array('marketing', 'gv_admin', 'coupon_admin');
    \common\helpers\Translation::init('admin/coupon_admin');
    $this->view->headingTitle = HEADING_TITLE_SEND;
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('coupon_admin/couponemail'), 'title' => $this->view->headingTitle);
    $msg = '';

    $send_coupon_id = intval(Yii::$app->request->get('cid', 0));

    if (Yii::$app->request->isPost) {
      $this->layout = false;
      $customers_email_address = Yii::$app->request->post('customers_email_address', '');
      $email_subject = Yii::$app->request->post('email_subject', '');
      $email_content = Yii::$app->request->post('email_content', '');
      $confirmed = Yii::$app->request->post('confirm_mul', 0);
      $mail_sent_to = TEXT_NONE;
      $send_status = 'success';
      if (empty($customers_email_address)) {
        $messageStack->add(ERROR_NO_CUSTOMER_SELECTED);
        $send_status = 'error';
      } else {

        switch ($customers_email_address) {
          case '***':
            if ($confirmed) {
              $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS);
              $mail_sent_to = TEXT_ALL_CUSTOMERS;
            }
            break;
          case '**D':
            if ($confirmed) {
 /** @var \common\extensions\Subscribers\Subscribers $subscr  */
                if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
                    $mail_query = $subscr::get_db_query(['where' => 'all_lists = 1']);
                } else {
                    //!! where 0
                    $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where 0 and customers_newsletter = '1'");
                }
                $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
            }
            break;
          default:
            $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "'");
            $mail_sent_to = $customers_email_address;
            break;
        }
        $send_counter = 0;
        $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
        $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);

        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');
        while ($mail = tep_db_fetch_array($mail_query)) {
          //Let's build a message object using the email class
          \common\helpers\Mail::send(
              $mail['customers_firstname'] . ' ' . $mail['customers_lastname'], $mail['customers_email_address'],
              $email_subject, $email_content,
              $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS
          );
          $send_counter++;
        }
        $msg = sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to);
        $messageStack->add($msg , 'header', 'success');
      }

      return '<div class="pop-up-content">
                        <div class="popup-content pop-mess-cont pop-mess-cont-' . $send_status . '">
                        ' . $msg . '
                        </div>
                  </div>
                  <div class="noti-btn">
                            <div></div>
                            <div><span class="btn btn-primary" onclick="$(\'.popup-box-wrap:last\').remove();return false">' . TEXT_BTN_OK . '</span></div>
                        </div>';
    }

    $customers = array();
    $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
    $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
 /** @var \common\extensions\Subscribers\Subscribers $subscr  */
    if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
        $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
    }
    $mail_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where 1 order by customers_lastname");
    while ($customers_values = tep_db_fetch_array($mail_query)) {
      $customers[] = array(
        'id' => $customers_values['customers_email_address'],
        'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')',
      );
    }

    $coupon_query = tep_db_query(
        "select c.coupon_code, cd.coupon_name, cd.coupon_description from " . TABLE_COUPONS . " c " .
        " left join " . TABLE_COUPONS_DESCRIPTION . " cd ON cd.coupon_id=c.coupon_id and cd.language_id = '" . \Yii::$app->settings->get('languages_id') . "' " .
        "where c.coupon_id = '" . $send_coupon_id . "'"
    );
    if (tep_db_num_rows($coupon_query) > 0) {
      $coupon_data = tep_db_fetch_array($coupon_query);
    } else {
      $coupon_data = array();
    }

    $email_params = array();
    $email_params['STORE_NAME'] = STORE_NAME;
    $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link('', '', 'NONSSL'/* , $store['store_url'] */));

    $email_params['COUPON_CODE'] = $coupon_data['coupon_code'];
    $email_params['COUPON_NAME'] = $coupon_data['coupon_name'];
    $email_params['COUPON_DESCRIPTION'] = $coupon_data['coupon_description'];

    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Send coupon', $email_params);

    return $this->render('couponemail', array(
          'customers_variants' => $customers,
          'customers_selected' => Yii::$app->request->get('customers', ''),
          'email_from' => EMAIL_FROM,
          'email_subject' => $email_subject,
          'email_text' => $email_text,
          'send_coupon_action' => tep_href_link('coupon_admin/couponemail', \common\helpers\Output::get_all_get_params(array('action'))),
    ));
  }

  public function actionTreeview() {

    \common\helpers\Translation::init('admin/coupon_admin');
    $this->layout = false;
    ob_start();
    ?>
    <link rel="stylesheet" type="text/css" href="<?= DIR_WS_ADMIN ?>plugins/dtree/dtree.css" />
    <script language="javascript" type="text/javascript" src="<?= DIR_WS_ADMIN ?>plugins/dtree/dtree.js"></script>
    <div class="dtree" style="padding: 10px;"><form>
        <p><a href="javascript: d.openAll();"><?= TEXT_OPEN_ALL ?></a> | <a href="javascript: d.closeAll();"><?= TEXT_CLOSE_ALL ?></a></p>
        <div class="holder" style="overflow-y: scroll;"></div>

            <script type='text/javascript'>

                var d = new dTree('d');
                d.add(0,-1,'Catalog','','');
                window.productsArray = {};
                window.categoriesArray = {};
    <?php

    $defLId = \common\helpers\Language::get_default_language_id();
    $categories_query_raw = "SELECT c.categories_id, cd.categories_name, c.parent_id FROM " . TABLE_CATEGORIES_DESCRIPTION . " AS cd INNER JOIN " . TABLE_CATEGORIES . " as c ON cd.categories_id = c.categories_id WHERE cd.language_id =" . (int) $defLId . " ORDER BY c.sort_order";
    $categories_query = tep_db_query($categories_query_raw);
    while ($categories = tep_db_fetch_array($categories_query)) {
      echo "d.add(" . $categories['categories_id'] . "," . $categories['parent_id'] . "," . \json_encode((string)$categories['categories_name']) . ",'', '<input type=checkbox name=categories value=" . $categories['categories_id'] . ">');\n"; //,," . $categories['categories_id'] . ",,,); \n";
        echo "categoriesArray[" . $categories['categories_id'] . "] = '" . addslashes($categories['categories_name']) . "';\n";
    } //end while

    $products_query_raw = "SELECT distinct pc.categories_id, pd.products_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " as pc INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pd ON pc.products_id = pd.products_id where pd.language_id = '" . (int) $defLId . "' and pd.platform_id='".(int)\common\classes\platform::defaultId()."'";
    $products_query = tep_db_query($products_query_raw);

    while ($products = tep_db_fetch_array($products_query)) {
      echo "d.add(" . $products['products_id'] . "0000," . $products['categories_id'] . "," . \json_encode((string)$products['products_name']) . ",'', '<input type=checkbox name=products value=" . $products['products_id'] . ">');\n"; //,," . $products['products_id'] . ",,,); \n";
        echo "productsArray[" . $products['products_id'] . "] = '" . addslashes($products['products_name']) . "';\n";
    }//end while

    if (\Yii::$app->request->get('id', '') != '' && \Yii::$app->request->get('input', '') == 'exclude') {
      $catJsEl = "document.querySelector('[data-id=exclude_categories_'+".\Yii::$app->request->get('id', '')."+']').value";
      $catJsElNames = "document.querySelector('[data-id=exclude_categories_names_'+".\Yii::$app->request->get('id', '')."+']').value";
      $prodJsEl = "document.querySelector('[data-id=exclude_products_'+".\Yii::$app->request->get('id', '')."+']').value";
      $prodJsElNames = "document.querySelector('[data-id=exclude_products_names_'+".\Yii::$app->request->get('id', '')."+']').value";
    }
    else if (\Yii::$app->request->get('id', '') != '') {
      $catJsEl = "document.querySelector('[data-id=restrict_to_categories_'+".\Yii::$app->request->get('id', '')."+']').value";
      $catJsElNames = "document.querySelector('[data-id=restrict_to_categories_names_'+".\Yii::$app->request->get('id', '')."+']').value";
      $prodJsEl = "document.querySelector('[data-id=restrict_to_products_'+".\Yii::$app->request->get('id', '')."+']').value";
      $prodJsElNames = "document.querySelector('[data-id=restrict_to_products_names_'+".\Yii::$app->request->get('id', '')."+']').value";
    }

     else if (\Yii::$app->request->get('input', '') == 'exclude') {
      $catJsEl = 'document.new_voucher.exclude_categories.value';
      $catJsElNames = 'document.new_voucher.exclude_categories_names.value';
      $prodJsEl = 'document.new_voucher.exclude_products.value';
      $prodJsElNames = 'document.new_voucher.exclude_products_names.value';
    } else {
      $catJsEl = 'document.new_voucher.restrict_to_categories.value';
      $catJsElNames = 'document.new_voucher.restrict_to_categories_names.value';
      $prodJsEl = 'document.new_voucher.restrict_to_products.value';
      $prodJsElNames = 'document.new_voucher.restrict_to_products_names.value';
    }
    ?>
        $('.dtree .holder').append(d.toString());

            catIds = <?php echo $catJsEl;?>.split(',').map(id => id.trim());//processed multiple times in threads
            prodIds = <?php echo $prodJsEl;?>.split(',').map(id => id.trim());//processed multiple times in threads
            catIds.forEach(id => {
                $('.dtree input[name="categories"][value="' + id + '"]').prop('checked', true)
            })
            prodIds.forEach(id => {
                $('.dtree input[name="products"][value="' + id + '"]').prop('checked', true)
            })

        </script>
            <button class="btn btn-primary" onClick="cycleCheckboxes(this.form)" style="float: right"><?= TEXT_APPLY ?></button>
            <span class="btn btn-cancle" onClick="return closePopup();"><?= IMAGE_CANCEL ?></span>
      </form>
      <script type='text/javascript'>

        $('.holder').css('max-height', document.body.clientHeight - 200);
        function cycleCheckboxes(what) {
           <?php echo $prodJsEl;?> = "";
           <?php echo $prodJsElNames;?> = "";
           <?php echo $catJsEl;?> = "";
           <?php echo $catJsElNames;?> = "";
          for (var i = 0; i < what.elements.length; i++) {
            if ((what.elements[i].name.indexOf('products') > -1)) {
              if (what.elements[i].checked) {
                <?php echo $prodJsEl;?> += what.elements[i].value + ',';
                <?php echo $prodJsElNames;?> += '- ' + productsArray[what.elements[i].value] + "\n";
              }
            }
          }

          for (var i = 0; i < what.elements.length; i++) {
            if ((what.elements[i].name.indexOf('categories') > -1)) {
              if (what.elements[i].checked) {
                <?php echo $catJsEl;?>  += what.elements[i].value + ',';
                <?php echo $catJsElNames;?>  += '- ' + categoriesArray[what.elements[i].value] + "\n";
              }
            }
          }

            if (!document.new_voucher.exclude_categories_names.value) document.new_voucher.exclude_categories_names.value = '<?php echo OPTION_NONE; ?>';
            if (!document.new_voucher.exclude_products_names.value) document.new_voucher.exclude_products_names.value = '<?php echo OPTION_NONE; ?>';
            if (!document.new_voucher.restrict_to_categories_names.value) document.new_voucher.restrict_to_categories_names.value = '<?php echo TEXT_ALL; ?>';
            if (!document.new_voucher.restrict_to_products_names.valu) document.new_voucher.restrict_to_products_names.valu = '<?php echo TEXT_ALL; ?>';


          closePopup();
        }
      </script>
    <?php
    $buf = ob_get_contents();
    ob_end_clean();
    return $this->render('treeview', ['content' => $buf]);
  }

    private function getImportFields() {
        return [
            'Code',
            'Name',
            'Description',
            'Type',
            'Amount',
            'Currency',
            'Amount with Tax',
            'Minimum Order',
            'Include Shipping',
            'Tax Class',
            'Coupon for Recovery Cart',
            'Disable for special products',
            'Only for customer(email)',
            'Uses per Coupon',
            'Uses per Customer',
            'Valid From',
            'Valid To',
            'Valid Categories List',
            'Valid Product List',
            'Exclude products',
            'Exclude categories',
            'Valid Countries',
        ];
    }
    
    private function getSampleData() {
        return [
            [
                'FIX299',
                'Fixed',
                'Fixed Discount',
                'F',
                '2.99',
                DEFAULT_CURRENCY,
                'YES',
                '50.00',
                'YES',
                '',
                '',
                '',
                '',
                '1000',
                '1',
                '2021-01-01',
                '2021-01-31',
                'Category Name 1;Category Name 2',
                'SKU1;SKU2;SKU3',
                '',
                '',
                'GB;US',
            ],
            [
                'PERCENT10',
                'Percent',
                'Percent Discount',
                'P',
                '10',
                '',
                'YES',
                '',
                '',
                '',
                '',
                '',
                '',
                '1000',
                '1',
                '2021-01-01',
                '2021-01-31',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'FREESHIP',
                'Free Shipping',
                'Free Shipping Code',
                'S',
                '',
                '',
                'YES',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
        ];
    }

    public function actionDownloadSample() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $writer = new \backend\models\EP\Formatter\CSV('write', array(), 'Discount Coupons Upload.csv');
        $writer->write_array($this->getImportFields());
        foreach ($this->getSampleData() as $row) {
            $writer->write_array($row);
        }
    }
    
    public function actionImport() {
        if (isset($_FILES['file']['tmp_name'])) {

            $taxClassesByIds = \common\models\TaxClass::find()->select(['tax_class_title', 'tax_class_id'])->asArray()->indexBy('tax_class_id')->column();
            $taxClasses = array_flip($taxClassesByIds);

            $languages = \common\helpers\Language::get_languages(true);
            
            $filename = $_FILES['file']['tmp_name'];
            
            //$uploadedHeaders = $this->getImportFields();//static header
            $CSV = new \backend\models\EP\Formatter\CSV('read', array(), $filename);
            $uploadedHeaders = $CSV->getHeaders();//dynamic header
            $CSV->close();
            
            $CSV = new \backend\models\EP\Formatter\CSV('read', array(), $filename);
            $uploadedKeys = array_flip($uploadedHeaders);
            $CSV->setReadRemapArray($uploadedHeaders);
            while ($data = $CSV->read_array()) {

                $object = \common\models\Coupons::find()->where(['coupon_code' => $data[$uploadedKeys['Code']]])->one();
                if (is_object($object)) {
                    continue;
                }
                
                $object = new \common\models\Coupons();
                $object->loadDefaultValues();
                $object->coupon_code = $data[$uploadedKeys['Code']];
                $type = $data[$uploadedKeys['Type']];
                switch ($data[$uploadedKeys['Type']]) {
                    case 'F':
                    case 'P':
                    case 'S':
                        $type = $data[$uploadedKeys['Type']];
                        break;
                    default:
                        $type = 'P';
                        break;
                }
                $object->coupon_type = $type;
                $object->date_created = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
                $object->coupon_amount = (float)$data[$uploadedKeys['Amount']];
                $object->coupon_currency = (!empty($data[$uploadedKeys['Currency']]) ? $data[$uploadedKeys['Currency']] : DEFAULT_CURRENCY);
                $object->flag_with_tax = ($data[$uploadedKeys['Amount with Tax']] == 'YES' ? 1 : 0);
                $object->coupon_minimum_order = (float)$data[$uploadedKeys['Minimum Order']];
                $object->uses_per_shipping = ($data[$uploadedKeys['Include Shipping']] == 'YES' ? 1 : 0);
                $object->tax_class_id = (isset($taxClasses[$data[$uploadedKeys['Tax Class']]]) ? $taxClasses[$data[$uploadedKeys['Tax Class']]] : 
                    (isset($taxClassesByIds[(int)$data[$uploadedKeys['Tax Class']]]) ? (int)$data[$uploadedKeys['Tax Class']] : 0) );
                $object->coupon_for_recovery_email = ($data[$uploadedKeys['Coupon for Recovery Cart']] == 'YES' ? 1 : 0);
                $object->pos_only = ($data[$uploadedKeys['For POS only']] == 'YES' ? 1 : 0);
                $object->disable_for_special = ($data[$uploadedKeys['Disable for special products']] == 'YES' ? 1 : 0);
                $object->restrict_to_customers = $data[$uploadedKeys['Only for customer(email)']];
                $object->uses_per_coupon = (int)$data[$uploadedKeys['Uses per Coupon']];
                $object->uses_per_user = (int)$data[$uploadedKeys['Uses per Customer']];
                $object->coupon_start_date = ( empty($data[$uploadedKeys['Valid From']]) ? '0000-00-00 00:00:00' : date('Y-m-d', strtotime($data[$uploadedKeys['Valid From']])) );
                $object->coupon_expire_date = ( empty($data[$uploadedKeys['Valid To']]) ? '0000-00-00 00:00:00' : date('Y-m-d', strtotime($data[$uploadedKeys['Valid To']])) );
                $object->coupon_active = 'Y';
                
                if (!empty($data[$uploadedKeys['Valid Categories List']])) {
                    $catList = explode(";", $data[$uploadedKeys['Valid Categories List']]);
                    $catChecked = [];
                    foreach ($catList as $cat) {
                        if (!empty($cat)) {
                            $findCat = \common\models\CategoriesDescription::find()->where(['categories_name' => $cat])->one();
                            if ($findCat instanceof \common\models\CategoriesDescription) {
                                $catChecked[] = $findCat->categories_id;
                            }
                        }
                    }
                    if (count($catChecked) > 0) {
                        $object->restrict_to_categories = implode(",", $catChecked);
                    }
                }
                if (!empty($data[$uploadedKeys['Valid Product List']])) {
                    $prodList = explode(";", $data[$uploadedKeys['Valid Product List']]);
                    $prodChecked = [];
                    foreach ($prodList as $prod) {
                        if (!empty($prod)) {
                            $findProd = \common\models\Products::find()->where(['products_model' => $prod])->one();
                            if ($findProd instanceof \common\models\Products) {
                                $prodChecked[] = $findProd->products_id;
                            }
                        }
                    }
                    if (count($prodChecked) > 0) {
                        $object->restrict_to_products = implode(",", $prodChecked);
                    }
                }
                if (!empty($data[$uploadedKeys['Exclude categories']])) {
                    $catList = explode(";", $data[$uploadedKeys['Exclude categories']]);
                    $catChecked = [];
                    foreach ($catList as $cat) {
                        if (!empty($cat)) {
                            $findCat = \common\models\CategoriesDescription::find()->where(['categories_name' => $cat])->one();
                            if ($findCat instanceof \common\models\CategoriesDescription) {
                                $catChecked[] = $findCat->categories_id;
                            }
                        }
                    }
                    if (count($catChecked) > 0) {
                        $object->exclude_categories = implode(",", $catChecked);
                    }
                }
                if (!empty($data[$uploadedKeys['Exclude products']])) {
                    $prodList = explode(";", $data[$uploadedKeys['Exclude products']]);
                    $prodChecked = [];
                    foreach ($prodList as $prod) {
                        if (!empty($prod)) {
                            $findProd = \common\models\Products::find()->where(['products_model' => $prod])->one();
                            if ($findProd instanceof \common\models\Products) {
                                $prodChecked[] = $findProd->products_id;
                            }
                        }
                    }
                    if (count($prodChecked) > 0) {
                        $object->exclude_products = implode(",", $prodChecked);
                    }
                }
                if (!empty($data[$uploadedKeys['Valid Countries']])) {
                    $countList = explode(";", $data[$uploadedKeys['Valid Countries']]);
                    $countChecked = [];
                    foreach ($countList as $count) {
                        if (!empty($count)) {
                            $findCount = \common\models\Countries::find()->where(['countries_iso_code_2' => $count])->one();
                            if ($findCount instanceof \common\models\Countries) {
                                $countChecked[] = $findCount->countries_id;
                            }
                        }
                    }
                    if (count($countChecked) > 0) {
                        $object->restrict_to_countries = implode(",", $countChecked);
                    }
                }
                
                $object->save(false);
                $coupon_id = $object->coupon_id;
                if ($coupon_id > 0) {
                    foreach ($languages as $_lang) {
                        $descObject = new \common\models\CouponsDescription();
                        $descObject->loadDefaultValues();
                        $descObject->coupon_id = $coupon_id;
                        $descObject->language_id = $_lang['id'];
                        $descObject->coupon_name = $data[$uploadedKeys['Name']];
                        $descObject->coupon_description = $data[$uploadedKeys['Description']];
                        $descObject->save(false);
                    }
                }
            }
            $CSV->close();
            unlink($filename);
        }
    }

}

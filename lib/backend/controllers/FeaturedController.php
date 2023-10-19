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
use common\models\FeaturedTypes;
use common\helpers\Html;
use Yii;

class FeaturedController extends Sceleton {

  public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_FEATURED'];
  private static $dateOptions = ['active_on', 'start_between', 'end_between'];
  private static $by = [
    [
      'name' => 'TEXT_ANY',
      'value' => '',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_ID',
      'value' => 'featured.products_id',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_MODEL',
      'value' => 'products_model',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_NAME',
      'value' => 'products_name',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_UPC',
      'value' => 'products_upc',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_EAN',
      'value' => 'products_ean',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_ISBN',
      'value' => 'products_isbn',
      'selected' => '',
    ],
  ];

  private static $filterFields = ['search' => '', 'date' => '',
    'featured_type_id' => 'intval',
    'inactive' => 'intval',
    'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
  ];

  public function actionIndex() {
    $this->selectedMenu = array('marketing', 'featured');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('featured/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;
    $this->topButtons[] = '<a href="' . \Yii::$app->urlManager->createUrl(['featured/featurededit']) . '" class="btn btn-primary">' . IMAGE_INSERT . '</a>';
    
    $this->view->featuredTable = array(
      array(
        'title' => ' ',
        'not_important' => 2
      ),
        array(
        'title' => ' ',
        'not_important' => 2
      ),
      array(
        'title' => Html::checkbox('select_all', false, ['id' => 'select_all']),
        'not_important' => 2
      ),
      array(
        'title' => HEADING_TYPE,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_INFO_DATE_ADDED,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS,
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
        'title' => TABLE_HEADING_STATUS,
        'not_important' => 1
      ),
    );
    $this->view->sortColumns = '3,4,5,6,7,8';

    $languages_id = \Yii::$app->settings->get('languages_id');
    $featuredTypesArr = \common\models\FeaturedTypes::find()->where([
          'language_id' => $languages_id
        ])
        ->select('featured_type_name, featured_type_id')
        ->asArray()->indexBy('featured_type_id')->column();
    if (!is_array($featuredTypesArr)) {
      $featuredTypesArr = [];
    }
    $featuredTypesArr[0] = BOX_CATALOG_FEATURED;
    $featuredTypesArr[-1] = TEXT_ALL;
    ksort($featuredTypesArr);
    $this->view->types = $featuredTypesArr;

    $this->view->filters = new \stdClass();
    $this->view->filters->row = (int) Yii::$app->request->get('row', 0);
    $gets = Yii::$app->request->get();

    $by = self::$by;
    foreach ($by as $key => $value) {
      $by[$key]['name'] = defined($by[$key]['name']) ? constant($by[$key]['name']) : strtolower(str_replace('_', ' ', $by[$key]['name']));
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
    return $this->render('index', ['selected_type_id' => (int)\Yii::$app->request->get('featured_type_id', -1)]);
  }

  public function actionList() {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);
    $filter = Yii::$app->request->get('filter', []);
    $filterArr = [];
    parse_str($filter, $filterArr);
    $featuredTypeId = (int) ($filterArr['featured_type_id'] ?? 0);
    $currencies = Yii::$container->get('currencies');

    $responseList = array();
    if ($length == -1) {
      $length = 10000;
    }
    $query_numrows = 0;


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

    $listQuery = \common\models\Featured::find()->joinWith(['backendProductDescription', 'featuredType'])->select(\common\models\Featured::tableName() . '.*');
    $inactive = false;

    foreach (self::$filterFields as $v => $f) {
      if (isset($gets[$v]) && $gets[$v]!='') {
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
          case 'featured_type_id':
            if ($val>=0) {
              $listQuery->andWhere([\common\models\Featured::tableName() . '.featured_type_id' => $val]);
            }
            break;
          case 'dfrom':
            if (in_array($date, ['start_between'])) {
              $listQuery->startAfter($val);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->endAfter($val);
            } else { //end between
              $listQuery->endAfter($val);
            }
            break;
          case 'dto':
            if (in_array($date, ['start_between'])) {
              $listQuery->startBefore($val);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->startBefore($val);
            } else { //end between
              $listQuery->endBefore($val);
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
      $listQuery->andWhere('status=1');
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
    $canSort = false;
    $currentSort = [];
    if (!empty($gets['order']) && is_array($gets['order'])) {
      foreach ($gets['order'] as $sort) {
        $dir = 'asc';
        if (!empty($sort['dir']) && $sort['dir'] == 'desc') {
          $dir = 'desc';
        }
        switch ($sort['column']) {
          case 0:
            $canSort = true;
              $currentSort[] = "sort_order, featured_date_added";
            $listQuery->addOrderBy("sort_order, featured_date_added");
          case 3:
              $currentSort[] = "featured_type_name " . $dir;
            $listQuery->addOrderBy(" featured_type_name " . $dir);
            break;
          case 4:
              $currentSort[] = "featured_date_added " . $dir;
            $listQuery->addOrderBy(" featured_date_added " . $dir);
            break;
          case 5:
              $currentSort[] = "products_name " . $dir;
            $listQuery->addOrderBy(" products_name " . $dir);
            break;
          case 6:
              $currentSort[] = "start_date " . $dir;
            $listQuery->addOrderBy(" start_date " . $dir);
            break;
          case 7:
              $currentSort[] = "expires_date " . $dir;
            $listQuery->addOrderBy(" expires_date " . $dir);
            break;
          case 8:
              $currentSort[] = "status ".$dir;
            $listQuery->addOrderBy(" status " . $dir);
            break;
          default:
              $currentSort[] = "featured_date_added desc";
            $listQuery->addOrderBy(" featured_date_added desc ");
            break;
        }
      }
      $listQuery->addOrderBy(" products_name ");
    } else {
        $currentSort[] = "sort_order, featured_date_added";
      $listQuery->addOrderBy("sort_order, featured_date_added");
    }

    $responseList = array();
    $current_page_number = ( $start / $length ) + 1;
    $query_numrows = $listQuery->count();
    if ($query_numrows < $start) {
      $start = 0;
    }
    $listQuery->offset($start)->limit($length);
    $listQuery->addSelect('products_name, featured_type_name');
    
//echo $listQuery->createCommand()->rawSql; die;

    $featureds = $listQuery->asArray()->all();

    foreach ($featureds as $featured) {
      $row = [];
      $row[] = $featured['sort_order'];
      if ($canSort) {
          $row[] = '<div class="handle_cat_list"><span class="handle" style="top: -15px; "><i class="icon-hand-paper-o"></i></span>'.
              '<input class="cell_id" type="hidden" value="' . $featured['featured_id'] . '">'.
              '</div>';
      }else{
          $row[] = '<input class="cell_id" type="hidden" value="' . $featured['featured_id'] . '">'.
          '<input class="current_sort" type="hidden" value="' . implode(', ', $currentSort) . '">';
      }

      $row[] = Html::checkbox('bulkProcess[]', false, ['value' => $featured['featured_id']])
          . Html::hiddenInput('featured_' . $featured['featured_id'], $featured['featured_id'], ['class' => "cell_identify"])
          . (!$featured['status'] ? Html::hiddenInput('featured_st' . $featured['featured_id'], 'dis_module', ['class' => "tr-status-class"]) : '')
      ;

      $row[] = (!isset($featured['featured_type_name'])? ($featured['featured_type_id'] == 0? BOX_CATALOG_FEATURED : ''):$featured['featured_type_name']);

      if ($featured['featured_date_added'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($featured['featured_date_added']);
      } else {
        $row[] = '';
      }
      $name = $featured['backendProductDescription']['products_name'] ?? '';
      foreach (['products_model', 'products_upc', 'products_ean', 'products_isbn'] as $value) {
        if (!empty($featured['product'][$value])) {
          $name .= '<br>' . $featured['product'][$value];
        }
      }

      $row[] = $name;

      if ($featured['start_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short(($featured['start_date']));
      } else {
        $row[] = '';
      }
      if ($featured['expires_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short($featured['expires_date']);
      } else {
        $row[] = '';
      }

      $row[] = Html::checkbox('specials_status' . $featured['featured_id'], $featured['status'], ['value' => $featured['featured_id'], 'class' => ($length < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' )]);

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

  function actionItempreedit($item_id = NULL) {
    $this->layout = FALSE;

    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/featured');

    if ($item_id === NULL) {
      $item_id = (int) Yii::$app->request->post('item_id', 0);
    }

    $backParams = [];
    parse_str(Yii::$app->request->post('bp'), $backParams);
    $backParams = array_filter($backParams);


    $sInfo = $this->getItemInfo($item_id);
    if ($sInfo->featured_id == 0) {
        exit();
    }
    ?>
    <div class="or_box_head or_box_head_no_margin">
      <?php echo $sInfo->backendProductDescription->products_name; ?><br>
      <?php echo $sInfo->featuredType->featured_type_name ?? null; ?>
    </div>
    <div class="row_or_wrapp">
      <div class="row_or">
        <div><?php echo TEXT_INFO_DATE_ADDED; ?></div>
        <div><?php echo \common\helpers\Date::date_format($sInfo->featured_date_added, DATE_FORMAT_SHORT); ?></div>
      </div>
      <div class="row_or">
        <div><?php echo TEXT_INFO_LAST_MODIFIED; ?></div>
        <div><?php echo \common\helpers\Date::date_format($sInfo->featured_last_modified, DATE_FORMAT_SHORT); ?></div>
      </div>
      <div class="row_or">
        <div><?php echo TEXT_START_DATE; ?></div>
        <div><?php echo \common\helpers\Date::datetime_short($sInfo->start_date, DATE_FORMAT_SHORT); ?></div>
      </div>
      <div class="row_or">
        <div><?php echo TEXT_INFO_EXPIRES_DATE; ?></div>
        <div><?php echo \common\helpers\Date::datetime_short($sInfo->expires_date, DATE_FORMAT_SHORT); ?></div>
      </div>
      <div class="row_or">
        <div><?php echo TEXT_INFO_STATUS_CHANGE; ?></div>
        <div><?php echo \common\helpers\Date::date_format($sInfo->date_status_change, DATE_FORMAT_SHORT); ?></div>
      </div>
    </div>
    <div class="btn-toolbar btn-toolbar-order">
      <a class="btn btn-edit btn-no-margin" href="<?php echo Yii::$app->urlManager->createUrl(['featured/featurededit', 'id' => $sInfo->featured_id, 'bp' => $backParams]); ?>"><?php echo IMAGE_EDIT ?></a><button class="btn btn-delete" onclick="return deleteItemConfirm(<?php echo $item_id; ?>)"><?php echo IMAGE_DELETE; ?></button>
    </div>
    </div>
    <?php
  }

  /**
   * @deprecated
   */
  function actionItemedit() {
    global $login_id;
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/featured');

    $item_id = (int) Yii::$app->request->post('item_id');
    $featured_type_id = (int) Yii::$app->request->post('featured_type_id');

    $expires_date = '';
    $status_checked_active = false;
    $products_name = '';

    if ($item_id === 0) {
      $header = IMAGE_INSERT;
    } else {
      $header = IMAGE_EDIT;

      $product_query = tep_db_query("select pd.products_name, s.* from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_FEATURED . " s where pd.language_id = '" . $languages_id . "' and pd.products_id = s.products_id and s.featured_id = '" . $item_id . "' and s.featured_type_id = '" . $featured_type_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' " . \common\helpers\Affiliate::whereIfExists('s'));
      $product = tep_db_fetch_array($product_query);

      if ((int) $product['status'] > 0) {
        $status_checked_active = true;
      }
      $products_name = $product['products_name'];

      $expires_date = \common\helpers\Date::date_short($product['expires_date']);
    }

    $this->layout = false;
    return $this->render('edit.tpl', [
          'header' => $header,
          'item_id' => $item_id,
          'expires_date' => $expires_date,
          'status_checked_active' => $status_checked_active,
          'product' => $products_name,
    ]);
  }

  function actionSubmit() {
    \common\helpers\Translation::init('admin/featured');

    $item_id = (int) Yii::$app->request->post('item_id');
    $products_id = (int) Yii::$app->request->post('products_id');
    $featured_type_id = (int) Yii::$app->request->post('featured_type_id');
    $status = tep_db_prepare_input(Yii::$app->request->post('status', 0));
    $expires_date = \common\helpers\Date::prepareInputDate(Yii::$app->request->post('expires_date'), true);
    $start_date = \common\helpers\Date::prepareInputDate(Yii::$app->request->post('start_date'), true);
      $dateFormat = date_create();
    if (!$start_date || $start_date=='' || $start_date=='NULL') {

      $start_date = $dateFormat?$dateFormat->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT):'';
    }
    $currentDatetime = $dateFormat ? $dateFormat->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT) : '';

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ret = ['result' => 0 ];

    $m = $item_id>0 ? \common\models\Featured::findOne($item_id) : null;
    if (!$m) {
      $m = new \common\models\Featured();
      $m->featured_date_added = $currentDatetime;
    }
    if ($m) {
      $ret = ['result' => 1 ];
      try {
        $m->products_id = $products_id;
        $m->featured_type_id = $featured_type_id;
        $m->status = $status;
        if (!empty($expires_date)) {
          $m->expires_date = $expires_date;
        }
        if (!empty($start_date)) {
          $m->start_date = $start_date;
        }
        $m->featured_last_modified = $currentDatetime;
        $m->save();
        $ret['item_id'] = $m->featured_id;
      } catch (\Exception $e) {
        $ret = ['result' => 0 ];
        $ret['message'] = TEXT_MESSAGE_ERROR . "\n(" . $e->getMessage() . ')';
      }
    }

    return $ret;
  }

  function actionConfirmitemdelete() {
    \common\helpers\Translation::init('admin/featured');
    $this->layout = FALSE;

    $item_id = (int) Yii::$app->request->post('item_id');

    $sInfo = $this->getItemInfo($item_id);


    echo tep_draw_form('item_delete', 'featured', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_FEATURED . '</div>';
    echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
    echo '<div class="col_desc"><strong>' . $sInfo->backendProductDescription->products_name . '</strong></div>';
    ?>
    <div class="btn-toolbar btn-toolbar-order">
    <?php
    echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
    echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
    echo tep_draw_hidden_field('item_id', $item_id);
    ?>
    </div>
    </form>
    <?php
  }

  function actionItemdelete() {
    $this->layout = FALSE;

    $featured_id = (int) Yii::$app->request->post('item_id');

    $messageType = 'success';
    $message = TEXT_INFO_DELETED;

    tep_db_query("delete from " . TABLE_FEATURED . " where featured_id = '" . tep_db_input($featured_id) . "'");
    ?>
    <div class="popup-box-wrap pop-mess">
      <div class="around-pop-up"></div>
      <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
          <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
          <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
    <?php echo $message; ?>
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
        });
      </script>
    </div>


    <p class="btn-toolbar">
    <?php
    echo '<input type="button" class="btn btn-primary" value="' . IMAGE_BACK . '" onClick="return resetStatement()">';
    ?>
    </p>
    <?php
  }

  function tep_set_featured_status($featured_id, $status) {
    if ($status == '1') {
      return tep_db_query("update " . TABLE_FEATURED . " set status = '1', date_status_change = now() where featured_id = '" . (int) $featured_id . "'");
    } elseif ($status == '0') {
      return tep_db_query("update " . TABLE_FEATURED . " set status = '0', date_status_change = now() where featured_id = '" . (int) $featured_id . "'");
    } else {
      return -1;
    }
  }

  function getItemInfo($item_id) {
    $listQuery = \common\models\Featured::find()->joinWith(['backendProductDescription', 'featuredType'])->select(\common\models\Featured::tableName() . '.*');
    $listQuery->addSelect('products_name, featured_type_name');
    $listQuery->andWhere(['featured_id' => $item_id]);
    $sInfo = $listQuery->one();

    if (empty($sInfo)) {
      $sInfo = new \objectInfo([
        'featuredType' => new \objectInfo(['featured_type_name' => '']),
        'backendProductDescription' => new \objectInfo(['products_name' => '']),
        'featured_date_added' => '',
        'featured_last_modified' => '',
        'expires_date' => '',
        'date_status_change' => '',
        'start_date' => '',
        'featured_id' => 0,
      ]);
    }
    return $sInfo;
  }

  public function actionFeaturededit() {

    $featuredsId = (int) Yii::$app->request->get('id');
    $productsId = (int) Yii::$app->request->get('products_id');
    $bp = Yii::$app->request->get('bp', []);

    $this->view->headingTitle = BOX_CATALOG_FEATURED;
    $this->selectedMenu = array('marketing', 'featured');

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('featured/index'), 'title' => BOX_CATALOG_FEATURED);

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_item_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

    $sInfo = null;
    $params = [];
    if (!empty($featuredsId) || !empty($productsId)) {
      $template = 'featurededit';

      if (!empty($featuredsId)) {
        $sInfo = \common\models\Featured::find()->andWhere(['featured_id' => $featuredsId])->with(['backendProductDescription'])->one();
        if (!empty($sInfo->featured_id)) {
          $pInfo = $sInfo->product;
          unset($sInfo->product);
        }
      }else{
          $sInfo = new \common\models\Featured();
          $sInfo->loadDefaultValues();
      }
      if (empty($sInfo->featured_id) && !empty($productsId)) {
        $pInfo = \common\models\Products::find()->andWhere(['products_id' => $productsId])->with(['backendDescription'])->one();
      }

      if (!empty($pInfo)) {
        $params['sInfo'] = (object) \yii\helpers\ArrayHelper::toArray($sInfo);
        $params['pInfo'] = (object) \yii\helpers\ArrayHelper::toArray($pInfo);
        $params['backendProductDescription'] = \yii\helpers\ArrayHelper::toArray($pInfo->backendDescription, ['products_name']);
        $languages_id = \Yii::$app->settings->get('languages_id');
        $featuredTypesArr = \common\models\FeaturedTypes::find()->where([
            'language_id' => $languages_id
          ])
          ->select('featured_type_name, featured_type_id')
          ->asArray()->indexBy('featured_type_id')->column();
        if (!is_array($featuredTypesArr)) {
          $featuredTypesArr = [];
        }
        $featuredTypesArr[0] = BOX_CATALOG_FEATURED;
        ksort($featuredTypesArr);
        $params['featured_types'] = $featuredTypesArr;

      } else {
        $template = 'choose_product';
      }
    } else {

      $template = 'choose_product';
    }
    $params['back_url'] = \Yii::$app->urlManager->createUrl(['featured'] + $bp);
    
    if ($template == 'choose_product') {
        $catalog =  new \backend\components\ProductsCatalog();
        return $catalog->make();
    }
    return $this->render($template, $params);
  }

  public function actionSwitchStatus() {
    $id = Yii::$app->request->post('id');
    $status = Yii::$app->request->post('status');
    $this->tep_set_featured_status($id, ($status == 'true' ? 1 : 0));
  }

  public function actionDeleteSelected() {
    $this->layout = FALSE;

    $spIds = Yii::$app->request->post('bulkProcess', []);
    if (is_array($spIds) && !empty($spIds)) {
      $spIds = array_map('intval', $spIds);
      \common\models\Featured::deleteAll(['featured_id' => $spIds]);
    }
  }

    public function actionSortOrder()
    {
        /** @var $featured \common\models\Featured */
        $tmpRec = null;
        $i = 0;
        $tmpIndex = 0;
        $justAfterCurrent = null;
        $sort = array_map('intval',Yii::$app->request->post('sort_data', []));
        foreach (\common\models\Featured::find()->orderBy('sort_order, featured_date_added')->each() as $featured) {
            $fId = $featured->featured_id;
            if ($fId === $sort['current']) {
                $tmpRec = $featured;
                continue;
            }
            if ($fId === $sort['before']) {
                $tmpIndex = $i+1;
                $justAfterCurrent = true;

            } elseif ($fId === $sort['after'] || ($sort['after'] === 0 && $justAfterCurrent )) { // next after moved item
                $tmpIndex = $i;
                $i++;
                $justAfterCurrent = null;
            }

            $featured->sort_order = $i++;
            $featured->save();
        }
        $tmpRec->sort_order = $tmpIndex;
        $tmpRec->save();
    }

    public function actionSaveCurrentSort()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        /** @var $featured \common\models\Featured */
        $orderBy = trim(Yii::$app->request->post('order_by', ''));
        if (empty($orderBy)) return ['status' => 'error', 'message' => 'SortOrder is empty'];
        $i = 0;
        foreach (\common\models\Featured::find()->joinWith(['backendProductDescription', 'featuredType'])->select(\common\models\Featured::tableName() . '.*')->orderBy($orderBy)->each() as $featured) {
            $featured->sort_order = $i++;
            $featured->save();
        }
        return ['status' => 'success'];
    }

}

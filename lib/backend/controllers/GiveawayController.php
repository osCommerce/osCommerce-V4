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
use common\helpers\Html;

class GiveawayController extends Sceleton {

  public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_GIVE_AWAY'];
  private static $wTime = false;
  private static $dateOptions = ['active_on', 'start_between', 'end_between'];
  private static $by = [
    [
      'name' => TEXT_ANY,
      'value' => '',
      'selected' => '',
    ],
    [
      'name' => TEXT_MODEL,
      'value' => 'products_model',
      'selected' => '',
    ],
    [
      'name' => TEXT_PRODUCT_NAME,
      'value' => 'products_name',
      'selected' => '',
    ],
  ];

  private static $filterFields = ['search' => '', 'date' => '',
    'group' => 'intval',
    'pfrom' => 'floatval', 'pto' => 'floatval',
    'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'qtyfrom' => 'intval', 'qtyto' => 'intval', 'freefrom' => 'intval', 'freeto' => 'intval'];


  public function actionIndex() {

    $this->selectedMenu = array('marketing', 'giveaway');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => HEADING_TITLE);
    //$this->topButtons[] = '<a href="#" class="create_item" onClick="return editItem(0)">'.IMAGE_INSERT.'</a>';
    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['giveaway/itemedit']) . '" class="js_create_new_gwa btn btn-primary addprbtn"><i></i>' . IMAGE_NEW . '</a>';
    $this->view->headingTitle = HEADING_TITLE;
    $this->view->giveawayTable = array(
      array(
        'title' => Html::checkbox('select_all', false, ['id' => 'select_all']),
        'not_important' => 2
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_GROUP,
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
        'title' => TABLE_HEADING_PRODUCTS_PRICE,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_BUY_QUANTITY,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_GIVE_AWAY_FREE_QTY,
        'not_important' => 0
      ),
    );
    $this->view->sortColumns = '1,2,3,4,5,6,7';

    $this->view->filters = new \stdClass();
    $this->view->filters->row = (int) Yii::$app->request->get('row', 0);


    $this->view->filters->mode = Yii::$app->request->get('mode', '');

    if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
      $this->view->filters->showGroup = true;
      $this->view->filters->groups = [0 => TEXT_MAIN] + \common\helpers\Group::get_customer_groups_list();
    }

    $gets = Yii::$app->request->get();

    $by = self::$by;
    foreach ($by as $key => $value) {
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
    $currencies = Yii::$container->get('currencies');

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

    $gwaQuery = \common\models\GiveAwayProducts::find()->joinWith(['backendDescription', 'product', 'customerGroup'])->select(\common\models\GiveAwayProducts::tableName() . '.*');

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
          case 'group':
            $gwaQuery->andWhere([\common\models\GiveAwayProducts::tableName() . '.groups_id' => $val]);
            break;
          case 'pfrom':
            $gwaQuery->andWhere(['>=', 'shopping_cart_price', $val]);
            break;
          case 'pto':
            $gwaQuery->andWhere(['<=', 'shopping_cart_price', $val]);
            $gwaQuery->andWhere(['>=', 'shopping_cart_price', 0]); // always add >=0 to skip buy/get
            break;
          case 'dfrom':
            if (in_array($date, ['start_between'])) {
              $gwaQuery->andWhere(['>=', 'begin_date', $val]);
            } elseif (in_array($date, ['active_on'])) {
              $gwaQuery->andWhere([
                'or',
                ['>=', 'end_date', $val],
                ['<', 'end_date', '1980-01-01']
              ]);
            } else {
              $gwaQuery->andWhere(['>=', 'end_date', $val]);
            }
            break;
          case 'dto':
            if (in_array($date, ['start_between'])) {
              $gwaQuery->andWhere(['<=', 'begin_date', $val]);
            } elseif (in_array($date, ['active_on'])) {
              $gwaQuery->andWhere(['<=', 'begin_date', $val]);
            } else {
              $gwaQuery->andWhere(['<=', 'end_date', $val]);
            }
            break;
          case 'qtyfrom':
            $gwaQuery->andWhere(['>=', 'buy_qty', $val]);
            break;
          case 'qtyto':
            $gwaQuery->andWhere(['<=', 'buy_qty', $val]);
            break;
          case 'freefrom':
            $gwaQuery->andWhere(['>=', 'products_qty', $val]);
            break;
          case 'freeto':
            $gwaQuery->andWhere(['<=', 'products_qty', $val]);
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
                $gwaQuery->andWhere(array_merge(['or'], $tmp));
              }
            } else {
              $gwaQuery->andWhere(['like', $by, $val]);
            }

            /*
              if (in_array($by, ['all'])) {
              $gwaQuery->andWhere(['like', ['products_model', 'products_ean'], $val ]);
              } */

            break;
        }
      }
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
        $gwaQuery->andWhere(array_merge(['or'], $tmp));
      }
    }

    if (!empty($gets['order'][0]['column'])) {
      $dir = 'asc';
      if (!empty($gets['order'][0]['dir']) && $gets['order'][0]['dir'] == 'desc') {
        $dir = 'desc';
      }
      switch ($gets['order'][0]['column']) {
        case 1:
          $gwaQuery->addOrderBy(" products_name " . $dir);
          $gwaQuery->addOrderBy(" groups_name ");
          break;
        case 2:
          $gwaQuery->addOrderBy(" groups_name " . $dir);
          break;
        case 3:
          $gwaQuery->addOrderBy(" begin_date " . $dir);
          break;
        case 4:
          $gwaQuery->addOrderBy(" end_date " . $dir);
          break;
        case 5:
          $gwaQuery->addOrderBy(" shopping_cart_price " . $dir);
          break;
        case 6:
          $gwaQuery->addOrderBy(" buy_qty " . $dir);
          break;
        case 7:
          $gwaQuery->addOrderBy(" products_qty " . $dir);
          break;
        default:
          $gwaQuery->addOrderBy(" products_name ");
          break;
      }
    } else {
      $gwaQuery->addOrderBy(" products_name ");
    }

    $responseList = array();
    if ($length == -1)
      $length = 10000;
    $current_page_number = ( $start / $length ) + 1;
    $query_numrows = $gwaQuery->count();

    $gwaQuery->offset($start)->limit($length);
    $gwaQuery->addSelect('products_model, products_name, groups_name');
    $gaps = $gwaQuery->asArray()->all();

    foreach ($gaps as $gap) {
      $row = [];
      $row[] = Html::checkbox('bulkProcess[]', false, ['value' => $gap['gap_id']]) . Html::hiddenInput('gwa_' . $gap['gap_id'], $gap['gap_id'], ['class' => "cell_identify"]);
      $row[] = trim($gap['products_model'] . ' ' . $gap['products_name']);
      if (!empty($gap['groups_name'])) {
        $row[] = $gap['groups_name'];
      } else {
        $row[] = '';
      }
      if ($gap['begin_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($gap['begin_date']);
      } else {
        $row[] = '';
      }
      if ($gap['end_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($gap['end_date']);
      } else {
        $row[] = '';
      }
      if ($gap['shopping_cart_price'] > 0) {
        $row[] = $currencies->format($gap['shopping_cart_price']);
      } else {
        $row[] = '';
      }
      if ($gap['buy_qty'] > 0) {
        $row[] = (int) $gap['buy_qty'];
      } else {
        $row[] = '';
      }
      if ($gap['products_qty'] > 0) {
        $row[] = (int) $gap['products_qty'];
      } else {
        $row[] = '';
      }
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

  function actionItempreedit() {
    $this->layout = FALSE;

    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/giveaway');

    $item_id = (int) Yii::$app->request->post('item_id', 0);
    if ($item_id) {
      $backParams = [];
      parse_str(Yii::$app->request->post('bp'), $backParams);
      $backParams = array_filter($backParams);

      $product_query = tep_db_query("select p.products_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, gap.gap_id, gap.shopping_cart_price, gap.products_qty from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_GIVE_AWAY_PRODUCTS . " gap where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = gap.products_id and gap.gap_id = '" . (int) $item_id . "'");
      $product = tep_db_fetch_array($product_query);
      if (!empty($product)) {
        $gapInfo = new \objectInfo($product);

        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $gapInfo->products_id . "'");
        $products = tep_db_fetch_array($products_query);
        ?>
        <div class="or_box_head"><?php echo TEXT_GIVE_MANAGEMENT; ?></div>
        <div class="col_desc"> <?php echo '<b>' . $gapInfo->products_name . '</b>'; ?></div>

        <div class="col_desc box_al_center"> <?php echo \common\helpers\Image::info_image($products['products_image'], $gapInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></div>
        <div class="btn-toolbar btn-toolbar-order">
          <a class="btn btn-edit btn-no-margin" href="<?php echo Yii::$app->urlManager->createUrl(['giveaway/itemedit', 'products_id' => $gapInfo->products_id, 'bp' => $backParams]) ?>"><?php echo IMAGE_EDIT; ?></a><button class="btn btn-delete" onclick="return deleteItemConfirm(<?php echo $item_id; ?>)"><?php echo IMAGE_DELETE; ?></button>
        </div>
        <?php
      }
    }
  }

  function actionItemedit() {
    \common\helpers\Translation::init('admin/giveaway');
    \common\helpers\Translation::init('admin/categories');
    $this->selectedMenu = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_GIVE_AWAY'];
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('giveaway/index'), 'title' => HEADING_TITLE);

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_product_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

    $products_id = (int) Yii::$app->request->post('products_id', 0);
    if (!$products_id) {
      $products_id = (int) Yii::$app->request->get('products_id', 0);
    }
    $bp = Yii::$app->request->get('bp', []);

    if (!$products_id) {
        $catalog =  new \backend\components\ProductsCatalog();
        return $catalog->make();

    } else {

      $currencies = Yii::$container->get('currencies');

      $this->view->give_away = 0;
      $this->view->buy_qty = '';
      $this->view->products_qty = '';
      $this->view->use_in_qty_discount = 0;
      \common\helpers\Gifts::prepareGWA($this->view, $products_id);
      $product = \common\models\Products::find()->andWhere(['products_id' => $products_id])->with('backendDescription')->asArray()->one();
      if (!$product) {
            $catalog =  new \backend\components\ProductsCatalog();
            return $catalog->make();
      } else {
        //also probably disallow bundles PCconf etc)
        $productName = $product['backendDescription']['products_name'];
        if (!empty($product['products_model'])) {
          $productName = $product['products_model'] . ' "' . $productName . '"';
        }
        $this->view->groups = [];
        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $ext::getGroups();
        }
      /// re-arrange data arrays for design templates
// init price tabs
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        $this->view->useMarketPrices = $this->view->useMarketPrices ?? null;
        if ($this->view->useMarketPrices) {
          $this->view->currenciesTabs = [];
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'include' => 'test/test.tpl',
          ];
        }

    //// groups tabs and params
        if (\common\helpers\Extensions::isCustomerGroupsAllowed() ) {
          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
          }
          $this->view->price_tabs[] = $tmp;
          unset($tmp);
          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
              'maxHeight' => '400px',
          ];
        }


      return $this->render('edit', [
        'currencies' => $currencies,
        'products_id' => $products_id,
        'productName' => $productName,
        'back_url' => Yii::$app->urlManager->createUrl(['giveaway'] + $bp),
          ]);
      }
    }
  }

  function actionSubmit() {

    \common\helpers\Translation::init('admin/giveaway');

    $products_id = tep_db_prepare_input(Yii::$app->request->post('products_id'));

    $this->layout = FALSE;
    $error = FALSE;
    $message = MESSAGE_SAVED;
    $messageType = 'success';

    if ($error === FALSE) {
      try {
        $productModel = \common\models\Products::findOne((int) $products_id);
        $marketingData = new \backend\models\ProductEdit\SaveMarketingData($productModel);
        $marketingData->prepareSaveGWA();

      } catch (\Exception $e) {
        $error = TRUE;
        \Yii::error($e->getMessage() . ' ' . $e->getTraceAsString());
      }
    }

    if ($error === TRUE) {
      $messageType = 'warning';
      if ($message == '') {
        $message = WARN_UNKNOWN_ERROR;
      }
    }
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

    <?php
    // $this->actionItemPreEdit();
  }

  function actionConfirmitemdelete() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/giveaway');
    \common\helpers\Translation::init('admin/faqdesk');

    $this->layout = FALSE;

    $item_id = (int) Yii::$app->request->post('item_id');

    $message = $name = $title = '';

    $product_query = tep_db_query("select p.products_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, gap.gap_id, gap.shopping_cart_price, gap.products_qty from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_GIVE_AWAY_PRODUCTS . " gap where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = gap.products_id and gap.gap_id = '" . (int) $item_id . "'");
    $product = tep_db_fetch_array($product_query);
    $gapInfo = new \objectInfo($product);

    echo tep_draw_form('item_delete', 'giveaway/itemdelete', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</div>';
    echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
    echo '<div class="col_desc">' . $gapInfo->products_name . '</div>';
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

  function actionDeleteSelected() {
    $this->layout = FALSE;

    $gapIds = Yii::$app->request->post('bulkProcess', []);
    if (is_array($gapIds)){
      $gapIds = array_map('intval', $gapIds);
      \common\models\GiveAwayProducts::deleteAll(['gap_id' => $gapIds]);
    }
  }

  function actionItemdelete() {
    $this->layout = FALSE;

    $gap_id = (int) Yii::$app->request->post('item_id');

    $messageType = 'success';
    $message = TEXT_INFO_DELETED;

    tep_db_query("delete from " . TABLE_GIVE_AWAY_PRODUCTS . " where gap_id = '" . (int) $gap_id . "'");
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

}

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
use backend\models\ProductEdit\ViewPriceData;
use backend\models\ProductEdit\PostArrayHelper;
use common\models\SpecialsTypes;
use common\helpers\Date;
use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use \common\helpers\Group;

class SpecialsController extends Sceleton {

  public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_SPECIALS'];
    /**
     * @var \backend\models\ProductEdit\TabAccess
     */
    public $ProductEditTabAccess;
  private static $dateOptions = ['active_on', 'start_between', 'end_between'];
  private static $by = [
    [
      'name' => 'TEXT_ANY',
      'value' => '',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_ID',
      'value' => 'specials.products_id',
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
    'specials_type_id' => 'intval',
    'group_id' => 'intval',
    'inactive' => 'intval',
    'pfrom' => 'floatval', 'pto' => 'floatval',
    'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
  ];

    public function init()
    {
        parent::init();
        $this->ProductEditTabAccess = new \backend\models\ProductEdit\TabAccess();
    }

  public function actionIndex()
  {
    $this->selectedMenu = array('marketing', 'specials');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials/index'), 'title' => HEADING_TITLE);
    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['specials/specialedit']) . '" class="btn btn-primary" >' . IMAGE_INSERT . '</a>';
    $this->view->headingTitle = HEADING_TITLE;
    $this->view->specialsTable = array(
      array(
        'title' => Html::checkbox('select_all', false, ['id' => 'select_all']),
        'not_important' => 2
      ),
      array(
        'title' => DATE_CREATED,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS_PRICE_OLD,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS_PRICE,
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
        'title' => TEXT_QTY_LIMITS,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_STATUS,
        'not_important' => 1
      ),
    );
    $languages_id = \Yii::$app->settings->get('languages_id');
    $specialsTypesArr = \common\models\SpecialsTypes::find()->where([
          'language_id' => $languages_id
        ])
        ->select('specials_type_name, specials_type_id')
        ->asArray()->indexBy('specials_type_id')->column();
    if (!is_array($specialsTypesArr)) {
      $specialsTypesArr = [];
    }
    $specialsTypesArr[0] = '';
    $specialsTypesArr[-1] = TEXT_ALL;
    ksort($specialsTypesArr);
    $this->view->types = $specialsTypesArr;

    if (\common\helpers\Extensions::isCustomerGroupsAllowed() ) {
      $this->view->groups = [];
      /** @var \common\extensions\UserGroups\UserGroups $ext */
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
          $ext::getGroups();
      }

      $this->view->groups = array_merge([
        ['groups_id' => -1, 'groups_name' => TEXT_ALL],
        ['groups_id' => 0, 'groups_name' => TEXT_MAIN],
        ], $this->view->groups);
      $this->view->groups = \yii\helpers\ArrayHelper::map($this->view->groups, 'groups_id', 'groups_name');
    }

    $this->view->filters = new \stdClass();
    $this->view->filters->row = (int) Yii::$app->request->get('row', 0);
    $gets = Yii::$app->request->get();

    $this->view->sortColumns = '1,2,3,4,5,6,7,8';
     if (!empty($gets['order']) && is_array($gets['order'])) {
    } else {
      \Yii::$app->controller->view->sortNow = '0,6';
      \Yii::$app->controller->view->sortNowDir = "desc,asc";
    }
    if (empty($gets)) {
      $gets['inactive'] = 1;
    }

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
    return $this->render('index', ['selected_type_id' => (int)\Yii::$app->request->get('specials_type_id', -1), 'group_id' => (int)\Yii::$app->request->get('group_id', -1)]);
  }

  public function actionIndexPopup() {
    \common\helpers\Translation::init('admin/categories');
    $prid = (int) \Yii::$app->request->get('prid', 0);
    if ($prid<=0) { return ''; }

    $this->view->specialsTable = array(
      array(
        'title' => DATE_CREATED,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRICE_EXCLUDING_TAX,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRICE_INCLUDING_TAX,
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
        'title' => TEXT_QTY_LIMITS,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_STATUS,
        'not_important' => 1
      ),
      array(
        'title' => TABLE_HEADING_ACTION,
        'not_important' => 1
      ),
    );
    $this->view->sortColumns = '0,1,2,3,4,5,6';
    \Yii::$app->controller->view->sortNow = '0,6';
    \Yii::$app->controller->view->sortNowDir = "desc,asc";

    $listQuery = \common\models\Specials::find()->with(['prices'])->select(\common\models\Specials::tableName() . '.*');
    $listQuery->andWhere([\common\models\Specials::tableName() . '.products_id' => $prid]);
    $listQuery->joinWith(['backendProductDescription'])->addSelect('products_name');
//    $listQuery->orderBy('status desc, start_date<now(), start_date, specials_disabled  desc');

    $items = $special = $listQuery
        //->asArray()
        ->all();
    
    $p = \common\models\Products::find()->andWhere(['products_id' => (int) $prid]);    
    $pInfo = $p->asArray()->one();

    $tax = \common\helpers\Tax::get_tax_rate_value($pInfo['products_tax_class_id']);

    /** @var \common\classes\Currencies $currencies */
    $currencies = Yii::$container->get('currencies');
    $params['price'] = $currencies->format($pInfo['products_price']);
    $params['priceGross'] = $currencies->display_price($pInfo['products_price'], $tax);
    $params['hash'] =  \Yii::$app->request->get('_hash_', false);

    
    return $this->renderAjax('index-popup', $params + ['prid' => $prid, 'items' => $items, 'tax' => $tax]);
  }

  public function actionList() {
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);

    /** @var \common\classes\Currencies $currencies */
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

    $listQuery = \common\models\Specials::find()->joinWith(['backendProductDescription', 'specialsType'])->select(\common\models\Specials::tableName() . '.*');
    $inactive = false;
    $checkGroup = 0;

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
          case 'specials_type_id':
            if ($val>=0) {
              $listQuery->andWhere([\common\models\Specials::tableName() . '.specials_type_id' => $val]);
            }
            break;
          case 'group_id':
            if ($val>=0) {
              if ($val==0) {
                $listQuery->andWhere(['not exists',
                  (new \yii\db\Query())->from(\common\models\SpecialsPrices::tableName())
                    ->andWhere(
                      \common\models\SpecialsPrices::tableName() . '.specials_id=' .
                      \common\models\Specials::tableName() . '.specials_id'
                      )
                    ->andWhere([
                      'specials_new_products_price' => -1,
                      'groups_id' => 0,
                    ])
                  ])
                    ->andWhere('specials_new_products_price>0');
              } else {
                $listQuery->andWhere(['exists',
                  (new \yii\db\Query())->from(\common\models\SpecialsPrices::tableName())
                    ->andWhere(
                      \common\models\SpecialsPrices::tableName() . '.specials_id=' .
                      \common\models\Specials::tableName() . '.specials_id' .
                      ' and specials_new_products_price<>-1'
                      )
                    ->andWhere([
                      'groups_id' => (int)$val,
                    ])
                  ]);
                $checkGroup = (int)$val;
              }
            }
            break;
          case 'pfrom':
            $listQuery->joinWith('prices');
            $listQuery->andWhere(['>=', \common\models\SpecialsPrices::tableName(). '.specials_new_products_price', $val]);
            $listQuery->distinct();
            break;
          case 'pto':
            $listQuery->joinWith('prices');
            $listQuery->andWhere(['<=', \common\models\SpecialsPrices::tableName(). '.specials_new_products_price', $val]);
            $listQuery->andWhere(['>', \common\models\SpecialsPrices::tableName(). '.specials_new_products_price', -0.0001]);
            $listQuery->distinct();
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

    if (!empty($gets['order']) && is_array($gets['order'])) {
      foreach ($gets['order'] as $sort) {
          $dir = 'asc';
          if (!empty($sort['dir']) && $sort['dir'] == 'desc') {
            $dir = 'desc';
          }
          switch ($sort['column']) {
            case 1:
              $listQuery->addOrderBy(" specials_date_added " . $dir);
              break;
            case 2:
              $listQuery->addOrderBy(" products_name " . $dir);
              break;
            case 3:
              $listQuery->addOrderBy(" products_price " . $dir);
              break;
            case 4:
              $listQuery->addOrderBy(" specials_new_products_price " . $dir);
              break;
            case 5:
              $listQuery->addOrderBy(" start_date " . $dir);
              break;
            case 6:
              $listQuery->addOrderBy(" expires_date " . $dir);
              break;
            case 7:
              $listQuery->addOrderBy(" total_qty " . $dir);
              break;
            case 8:
              $listQuery->addOrderBy(" status " . $dir . ', specials_enabled desc, specials_disabled');
              break;
            default:
              $listQuery->addOrderBy(" specials_date_added desc ");
              break;
          }
      }
      $listQuery->addOrderBy(" products_name ");
    } else {
      $listQuery->addOrderBy(" specials_date_added desc, specials_enabled desc, specials_disabled ");
    }

    $responseList = array();
    $current_page_number = ( $start / $length ) + 1;
    $query_numrows = $listQuery->count();
    if ($query_numrows < $start) {
      $start = 0;
    }
    $listQuery->offset($start)->limit($length);
    $listQuery->addSelect('products_name, products_price, specials_type_name');
    
 //echo $listQuery->createCommand()->rawSql; die;
    $specials = $listQuery
        //->asArray()
        ->all();
    $groups = \common\helpers\Group::get_customer_groups();


    foreach ($specials as $sInfo) {
      $special = $sInfo->attributes;
      $special['product'] = $sInfo->product->attributes;
      $special['productPrices'] = \yii\helpers\ArrayHelper::index($sInfo->productPrices, 'groups_id', 'currencies_id');
      $special['specialsType'] = $sInfo->specialsType->attributes ?? null;
      $special['backendProductDescription'] = $sInfo->backendProductDescription->attributes;

      $row = [];

      $soldOut = $sold = 0;
      if (!empty($special['total_qty'])) {
        $sold = \common\helpers\Specials::getSoldOnlyQty(['specials_id' => $special['specials_id']]);
        $soldOut = ($sold>=$special['total_qty']);
      }

      $row[] = Html::checkbox('bulkProcess[]', false, ['value' => $special['specials_id']])
          . Html::hiddenInput('coupons_' . $special['specials_id'], $special['specials_id'], ['class' => "cell_identify"])
          . (!$special['status'] ? Html::hiddenInput('coupons_st' . $special['specials_id'], 'dis_module', ['class' => "tr-status-class"]) : 
                       ($soldOut ? Html::hiddenInput('coupons_sts' . $special['specials_id'], 'alert alert-danger', ['class' => "tr-status-class"]) :''))
          . Html::hiddenInput('pid', $special['products_id'], ['class' => "product-id"])
      ;

      if ($special['specials_date_added'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($special['specials_date_added']);
      } else {
        $row[] = '';
      }
      $name = $special['backendProductDescription']['products_name'] ?? '';
      if (!empty($special['specialsType']['specials_type_name'])) {
        $name =  $special['specialsType']['specials_type_name'] . '<br>' . $name;
      }
      foreach (['products_model', 'products_upc', 'products_ean', 'products_isbn'] as $value) {
        if (!empty($special['product'][$value])) {
          $name .= '<br>' . $special['product'][$value];
        }
      }

      $row[] = $name;

      //product prices
      $allGross = $allBoth = $allNet = '';
      $tax = \common\helpers\Tax::get_tax_rate_value($special['product']['products_tax_class_id']);
      if (!defined('USE_MARKET_PRICES')  || USE_MARKET_PRICES != 'True') {
        $price = $special['product']['products_price'];
        $p['text'] = $currencies->format($price, true);
        $p['text_inc'] = $currencies->format($price*(1+ $tax/100), true);
        $allGross .= TEXT_MAIN . ': ' . $p['text_inc'] . " \n";
        $allNet .= TEXT_MAIN . ': ' . $p['text'] . " \n";
        $allBoth .= TEXT_MAIN . ': ' . $p['text'] . ' ' . $p['text_inc'] . " \n";

        $newPrice = $p['text'];
        $newPriceInc = $p['text_inc'];
      } else {
        $groups[0]['groups_name'] = TEXT_MAIN;
        $groups[0]['groups_discount'] = 0;
      }
      if (is_array($special['productPrices'])) {
        $prices = $special['productPrices'];
        foreach ($prices as $currencies_id  => $cur ) {
          foreach ($cur as $gid => $p1) {
            if ((!defined('USE_MARKET_PRICES')  || USE_MARKET_PRICES != 'True') && $gid == 0) {
              continue;
            }
            $p = [];
            /** @var \common\classes\Currencies $currencies */
            if ($p1['products_group_price']==-2) {
              $price = $special['product']['products_price']*(1-ArrayHelper::getValue($groups, ['gid','groups_discount'])/100);
              $p['text'] = $currencies->format($price, true, ($currencies_id>0?\common\helpers\Currencies::getCurrencyCode($currencies_id):''));
              $p['text_inc'] = $currencies->format($price*(1+ $tax/100), true, ($currencies_id>0?\common\helpers\Currencies::getCurrencyCode($currencies_id):''));
            } else {
              $p['text'] = $currencies->format($p1['products_group_price'], true, ($currencies_id>0?\common\helpers\Currencies::getCurrencyCode($currencies_id):''));
              $p['text_inc'] = $currencies->format($p1['products_group_price']*(1+ $tax/100), true, ($currencies_id>0?\common\helpers\Currencies::getCurrencyCode($currencies_id):''));
            }

            $allGross .= $groups[$gid]['groups_name'] . ': ' . $p['text_inc'] . " \n";
            $allNet .= $groups[$gid]['groups_name'] . ': ' . $p['text'] . " \n";
            $allBoth .= $groups[$gid]['groups_name'] . ': ' . $p['text'] . ' ' . $p['text_inc'] . " \n";
            if ($checkGroup == $gid) {
              $newPrice = $p['text'];
              $newPriceInc = $p['text_inc'];
            }
          }
        }        
      }
    $row[] = "<div title='$allBoth'><span style='display:block' class='net-price price'>{$newPrice}</span> <span class='sale-info'></span> <span class='gross-price price'>" . $newPriceInc. '</span></div>';

      ///special prices
      $allGross = $allBoth = $allNet = '';
      $prices = \common\helpers\Specials::getPrices($sInfo, $tax);
      if (is_array($prices)) {
        foreach ($prices as $cur ) {
          foreach ($cur as $p) {
            $allGross .= $p['group_name'] . ': ' . $p['text_inc'] . " \n";
            $allNet .= $p['group_name'] . ': ' . $p['text'] . " \n";
            $allBoth .= $p['group_name'] . ': ' . $p['text'] . ' ' . $p['text_inc'] . " \n";
          }
        }
        if (empty($prices[0][$checkGroup]['text'])) {
          if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $newPrice = TEXT_DISABLED;
          } elseif ($checkGroup==0) {
            $newPrice = sprintf(TEXT_PRICE_SWITCH_DISABLE, TEXT_MAIN);
          } else {
            $newPrice = sprintf(TEXT_PRICE_SWITCH_DISABLE, $prices[0]['group_name']);
          }
          $newPriceInc = '';
        } else {
          $newPrice = $prices[0][$checkGroup]['text'];
          $newPriceInc = $prices[0][$checkGroup]['text_inc'];
        }
      }


      $row[] = "<div title='$allBoth'><span style='display:block' class='net-price price'>{$newPrice}</span> <span class='sale-info'></span> <span class='gross-price price'>{$newPriceInc}</span></div>";

      $expired  = $scheduled = false;

      if ($special['start_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short(($special['start_date']));
        if ($special['start_date'] > date("Y-m-d H:i:s") && !$special['status'] ) {
          $scheduled = true;
        }
      } else {
        $row[] = '';
      }
      if ($special['expires_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short($special['expires_date']);
        if ($special['expires_date'] < date("Y-m-d H:i:s")) {
          $expired = true;
        }
      } else {
        $row[] = '';
      }

      $row[] =  (!empty($special['total_qty'] || !empty($special['max_per_order']))? $special['total_qty'] . '/' . $special['max_per_order'] . ($sold?'<span class="right-link">(' . $sold . ')</span>':''): '');

      $row[] = \common\helpers\Specials::statusDescriptionText($special['specials_enabled'], $special['specials_disabled'], $expired, $scheduled);
          /*($expired? TEXT_EXPIRED . '<BR>':
        ($special['specials_disabled']? TEXT_DISABLED. '<br>':
         ($scheduled?TEXT_SCHEDULED . '<br>':'')))
        . (!$expired ||$special['status'] ?
          Html::checkbox('specials_status' . $special['specials_id'], $special['status'], ['value' => $special['specials_id'], 'class' => ($length < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' )]):'')*/;

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
    \common\helpers\Translation::init('admin/specials');
    /** @var \common\classes\Currencies $currencies */
    $currencies = Yii::$container->get('currencies');
    $groups = [];
    /** @var \common\extensions\UserGroups\UserGroups $ext */
    if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
      $groups = $ext::getGroupsArray();
    }
    //$groups = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), array_filter($groups, function($e) { return (!isset($e['per_product_price']) || $e['per_product_price']); }));
    $groups = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $groups);
    
    $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
    $this->layout = false;
    $item_id = (int) Yii::$app->request->post('item_id', 0);
    $sInfo = \common\models\Specials::find()->andWhere(['specials_id' => $item_id])->with(['prices', 'backendProductDescription'])->one();
    if (!empty($sInfo->specials_id)) {
      $backParams = [];
      parse_str(Yii::$app->request->post('bp'), $backParams);
      $backParams = array_filter($backParams);
    ?>
    <div class="or_box_head or_box_head_no_margin"><?php echo $sInfo->backendProductDescription->products_name; ?></div>
    <div class="row_or_wrapp">
    <?php
    echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($sInfo->specials_date_added) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($sInfo->specials_last_modified) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_STATUS_CHANGE . '</div><div class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->date_status_change) . '</div></div><hr>';
    $p = \common\models\Products::find()->andWhere(['products_id' => (int) $sInfo->products_id]);
    $pInfo = $p->asArray()->one();
    $tax = \common\helpers\Tax::get_tax_rate_value($pInfo['products_tax_class_id']);
    $prices = \common\helpers\Specials::getPrices($sInfo, $tax);


    if (is_array($prices)) {
        $res = '';
        $res .= '<div class="row_or"><div>' . TEXT_INFO_NEW_PRICE . '</div></div>';

        foreach ($groups as $group) {
            //$salesDetails = \common\helpers\Specials::getStatus($sInfo->specials_id, $tax, $group['groups_id'], 0);
            $_exists = false;
            $_price_details = '';
            if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
                $_price_details .= '<div class="m-prices">';

                foreach ($currencies->currencies as $value) {
                    if (!empty($group['per_product_price']) || isset($prices[$value['id']][$group['groups_id']])) {
                        $_exists = true;
                        //$salesDetails = \common\helpers\Specials::getStatus($sInfo->specials_id, $tax, $group['groups_id'], $value['id']);
                        $salesDetails = $prices[$value['id']][$group['groups_id']];
                        $_price_details .= '<div class="currency">' . ($salesDetails['text'] ?? TEXT_DISABLED) . '</div>';
                        if (abs($salesDetails['value'] - $salesDetails['value_inc']) >= 0.01) {
                            $_price_details .= '<div class="currency">' . ($salesDetails['text_inc'] ?? '') . '</div>';
                        }
                    }
                }
                $_price_details .= '</div>';
            } else {
                if (!empty($group['per_product_price']) || isset($prices[0][$group['groups_id']])) {
                    $_exists = true;
                    $salesDetails = $prices[0][$group['groups_id']] ?? null;

                    $_price_details .= '<div class="row_or"><div style="font-weight:normal;" class="currency currency-net">' . ($salesDetails['text'] ?? TEXT_DISABLED) . '</div>';
                    if (abs(ArrayHelper::getValue($salesDetails, 'value') - ArrayHelper::getValue($salesDetails, 'value_inc')) >= 0.01) {
                        $_price_details .= '<div class="currency">&nbsp;' . ($salesDetails['text_inc'] ?? '') . '</div>';
                    }
                    $_price_details .= '</div>';
                }
            }
            if ($_exists) {
                $res .= '<div class="row_or"><div class="group-name" style="vertical-align: top;">' . ($group['groups_name']) . '</div>';
                $res .= $_price_details;
                $res .= '</div>';
            }
        }
        echo $res;
    }
    echo '<div class="row_or">&nbsp;</div><div class="row_or"><div>' . TEXT_START_DATE . '</div><div  class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->start_date) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_EXPIRES_DATE . '</div><div  class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->expires_date) . '</div></div>';
    ?>
    </div>
    <div class="btn-toolbar btn-toolbar-order">
      <a class="btn btn-edit btn-no-margin" href="<?php echo Yii::$app->urlManager->createUrl(['specials/specialedit', 'id' => $sInfo->specials_id, 'bp' => $backParams]); ?>"><?php echo IMAGE_EDIT ?></a><button class="btn btn-delete" onclick="return deleteItemConfirm(<?php echo $item_id; ?>)"><?php echo IMAGE_DELETE; ?></button>
      <?php if (\common\helpers\Acl::checkExtensionAllowed('ReportOrderedProducts')) { ?>
      <a class="btn btn-no-margin" href="<?php echo Yii::$app->urlManager->createUrl(['ordered-products-report', 'specials_id' => $sInfo->specials_id, 'start_date' => Date::formatCalendarDate($sInfo->specials_date_added)]); ?>"><?php echo IMAGE_REPORT ?></a>
      <?php } ?>
    </div>
    <?php
    }
  }

/**
 * @deprecated
 */
  function actionItemedit() {
    $this->layout = FALSE;

    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/specials');

    $item_id = (int) Yii::$app->request->post('item_id');

    $currencies = Yii::$container->get('currencies');

    //$_params = Yii::app()->getParams();
    //if( !isset( $_params->currencies ) ) Yii::app()->setParams( array( 'currencies' => $currencies ) );

    $header = '';
    $script = '';
    $delete_btn = '';
    $form_html = '';

    $fields = array();

    $languages = \common\helpers\Language::get_languages();

    if ($item_id === 0) {
      // Insert
      $header = 'Insert';

      $sInfo = new \objectInfo(array());

      $specials_array = array();
      $specials_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id");
      while ($specials = tep_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }

      $special_product_html = \common\helpers\Product::draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array);


      $fields[] = array('type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html);

      $fields[] = array('name' => 'products_price', 'type' => 'hidden', 'value' => '');


      if (USE_MARKET_PRICES == 'True') {

        foreach ($currencies->currencies as $key => $value) {

          $specials_products_price_html = tep_draw_input_field(
              'specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']',
              \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id']), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
        }

        $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
        while ($data = tep_db_fetch_array($data_query)) {
          $data_html = tep_draw_input_field('specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2'), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $data['groups_name'], 'value' => $data_html);
        }
      } else {
        $fields[] = array('name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => '');
      }

      $fields[] = array('name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => '');
    } else {
      // Update
      $header = 'Edit';

      $product_query = tep_db_query("select p.products_id, s.specials_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'");
      $product = tep_db_fetch_array($product_query);

      $sInfo = new \objectInfo($product);

      $specials_array = array();
      $specials_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id");
      while ($specials = tep_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }

      if (isset($sInfo->products_name)) {
        $special_product_html = $sInfo->products_name . ' <small>(' . $currencies->format(\common\helpers\Product::get_products_price($sInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])) . ')</small>';
      } else {
        $special_product_html = \common\helpers\Product::draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array);
      }

      $fields[] = array('type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html);


      $status_checked_disabled = FALSE;
      $status_checked_active = FALSE;

      if ((int) $sInfo->status > 0) {
        $status_checked_active = TRUE;
      } else {
        $status_checked_disabled = TRUE;
      }

      $status_html = tep_draw_checkbox_field("status", '1', $status_checked_active, '', 'class="check_on_off"');
      /*                $status_html .= "Active " . tep_draw_radio_field( 'status', 1, $status_checked_active );
        $status_html .= '<br>';
        $status_html .= "Inactive " . tep_draw_radio_field( 'status', '0', $status_checked_disabled ); */



      $fields[] = array('type' => 'field', 'title' => TABLE_HEADING_STATUS, 'value' => $status_html);

      if (USE_MARKET_PRICES == 'True') {

        $specials_products_price_html = '';
        foreach ($currencies->currencies as $key => $value) {
          $specials_products_price_html = tep_draw_input_field('specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']', (($specials_new_products_price[$currencies->currencies[$key]['id']]) ? stripslashes($specials_new_products_price[$currencies->currencies[$key]['id']]) : \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'])), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
        }

        $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
        while ($data = tep_db_fetch_array($data_query)) {
          $group_html = tep_draw_input_field('specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2'), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $data['groups_name'], 'value' => $group_html);
        }
      } else {
        $fields[] = array('name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => \common\helpers\Product::get_specials_price($sInfo->specials_id));

        $fields[] = array('name' => 'products_price', 'type' => 'hidden', 'value' => ( isset($sInfo->products_price) ? $sInfo->products_price : '' ));
      }

      if ($sInfo->expires_date == '0000-00-00 00:00:00') {
        $expires_date = '';
      } else {
        $expires_date = explode("-", $sInfo->expires_date);
        @$Y = $expires_date[0];
        @$M = $expires_date[1];
        @$d = $expires_date[2];
        @$D = explode(" ", $d);
        $expires_date = $M . "/" . $D[0] . "/" . $Y;
      }

      if ($expires_date == "//")
        $expires_date = '';

      $fields[] = array('name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => \common\helpers\Date::date_short($sInfo->expires_date));

      $fields[] = array('type' => 'field', 'title' => '', 'value' => TEXT_SPECIALS_PRICE_TIP);
    }

    echo tep_draw_form(
        'save_item_form',
        'specials/submit',
        \common\helpers\Output::get_all_get_params(array('action')),
        'post',
        'id="save_item_form" onSubmit="return saveItem();"') .
    tep_draw_hidden_field('item_id', $item_id);
    ?>
    <div class="or_box_head"><?php echo $header; ?></div>

    <?php
    foreach ($fields as $field) {
      if (isset($field['title']))
        $field_title = $field['title'];
      else
        $field_title = '';
      if (isset($field['name']))
        $field_name = $field['name'];
      else
        $field_name = '';
      if (isset($field['value']))
        $field_value = $field['value'];
      else
        $field_value = '';
      if (isset($field['type']))
        $field_type = $field['type'];
      else
        $field_type = 'text';
      if (isset($field['class']))
        $field_class = $field['class'];
      else
        $field_class = '';
      if (isset($field['required']))
        $field_required = '<span class="fieldRequired">* Required</span>';
      else
        $field_required = '';
      if (isset($field['maxlength']))
        $field_maxlength = 'maxlength="' . $field['maxlength'] . '"';
      else
        $field_maxlength = '';
      if (isset($field['size']))
        $field_size = 'size="' . $field['size'] . '"';
      else
        $field_size = '';
      if (isset($field['post_html']))
        $field_post_html = $field['post_html'];
      else
        $field_post_html = '';
      if (isset($field['pre_html']))
        $field_pre_html = $field['pre_html'];
      else
        $field_pre_html = '';
      if (isset($field['cols']))
        $field_cols = $field['cols'];
      else
        $field_cols = '70';
      if (isset($field['rows']))
        $field_rows = $field['rows'];
      else
        $field_rows = '15';

      if ($field_type == 'hidden') {
        $form_html .= tep_draw_hidden_field($field_name, $field_value);
      } elseif ($field_type == 'field') {
        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>';
        echo '       <div class="main_value">       ';
        echo "        $field_value";
        echo '       </div>       ';
        echo ' </div>';
      } elseif ($field_type == 'textarea') {

        $field_html = tep_draw_textarea_field($field_name, 'soft', $field_cols, $field_rows, $field_value);

        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>       ';
        echo '       <div class="main_value">       ';
        echo "        $field_pre_html $field_html  $field_required $field_post_html";
        echo '       </div>       ';
        echo ' </div>';
      } else {
        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>       ';
        echo '       <div class="main_value">       ';
        echo "        $field_pre_html <input type='$field_type' name='$field_name' value='$field_value' $field_maxlength $field_size class='$field_class'> $field_post_html $field_required";
        echo '       </div>       ';
        echo ' </div>';
      }
    }
    ?>
    <div class="btn-toolbar btn-toolbar-order">
      <input class="btn btn-no-margin" type="submit" value="<?php echo IMAGE_SAVE; ?>"><?php echo $delete_btn; ?><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="<?php echo IMAGE_CANCEL; ?>">
    </div>

    <?php echo $form_html; ?>
    </form>
    <script>
      $(document).ready(function () {
        $(".widget-content .check_on_off").bootstrapSwitch(
        {
          onText: "<?= SW_ON ?>",
          offText: "<?= SW_OFF ?>",
          handleWidth: '20px',
          labelWidth: '24px'
        }
        );

        $(".datepicker").datepicker({
          changeMonth: true,
          changeYear: true,
          showOtherMonths: true,
          autoSize: false,
          minDate: '1',
          dateFormat: '<?= DATE_FORMAT_DATEPICKER ?>',

        });
      })
    </script>
    <?php
  }

  function actionValidate() {

    if (!defined('SALE_STRICT_DATE') || SALE_STRICT_DATE!='True') {
      $ret = ['valid' => 1];
    } else {
      $post = \Yii::$app->request->post();
      $currencies = Yii::$container->get('currencies');
      $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

      $specials_expires_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'expires_date', 'dbdef' => '', 'post' => 'special_expires_date'], $_def_curr_id, 0);
      $specials_expires_date = \common\helpers\Date::prepareInputDate($specials_expires_date, true);
      $specials_start_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'start_date', 'dbdef' => 'NULL', 'post' => 'special_start_date'], $_def_curr_id, 0);
      $specials_start_date = \common\helpers\Date::prepareInputDate($specials_start_date, true);
      if (empty($specials_expires_date)) {
        $specials_expires_date = '9999-01-01';
      }
      if (empty($specials_start_date)) {
        $specials_start_date = '0000-00-00';
      }

      $listQuery = \common\models\Specials::find()->alias('s')->joinWith(['prices'])->select('s.*');
      $listQuery->andWhere(['products_id' => (int)$post['products_id']]);
      if (intval($post['specials_id'])>0) {
        $listQuery->andWhere(['<>', 's.specials_id', intval($post['specials_id'])]);
      }
      $listQuery->datesInRange($specials_start_date, $specials_expires_date);
  //echo $listQuery->createCommand()->rawSql;
      $q = $listQuery->asArray()->all();
      if (empty($q )) {
        $ret = ['valid' => 1];
      } else {
        $ret = ['list' => '<span class="date start-date">' . TEXT_START_DATE . ': ' . \common\helpers\Date::datetime_short($specials_start_date) . '</span>' . ' <span class="date start-date">' . TEXT_SPECIALS_EXPIRES_DATE . ' ' . \common\helpers\Date::datetime_short($specials_expires_date) . '</span><br>' . TEXT_OVERLAPPED_DATE_RANGE .':<br>' , 'valid' => 0];
        foreach ($q as $price) {
          $ret['list'] .= '<br>';
          //$ret['list'] .= $listQuery->createCommand()->rawSql .' <br>';
          $ret['list'] .= ' <span class="date start-date" title="' . $price['specials_id'] . '">' . TEXT_START_DATE . ': ' . \common\helpers\Date::datetime_short($price['start_date']) . '</span>';
          $ret['list'] .= ' <span class="date start-date">' . TEXT_SPECIALS_EXPIRES_DATE . ' ' . \common\helpers\Date::datetime_short($price['expires_date']) . '</span>';
          $ret['list'] .= ' <a href="' . Yii::$app->urlManager->createUrl(['specials/specialedit', 'id' => $price['specials_id'] ]) . '" target="_blank"><span class="group">' . TEXT_MAIN . ' <span class="price group-price0">' . $currencies->format($price['specials_new_products_price']) . '</span></span></a>';
        }
      }
    }
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $ret;
  }


  function actionSubmit() {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ret = ['result' => 0 ];
    \common\helpers\Translation::init('admin/specials');

    $products_id = (int) Yii::$app->request->post('products_id');
    $res = \common\helpers\Specials::saveFromPost($products_id, 0);
    if (is_string($res)) {
      $ret['message'] = $res;
    } elseif ($res === true ) {
      $ret = ['result' => 1 ];
    } elseif (is_int ($res) && $res>0 ) {
      $ret = [
        'result' => 1,
        'id' => $res
        ];
    } else {
      $ret['message'] = TEXT_MESSAGE_ERROR;
    }

    return $ret;
  }


  function actionConfirmitemdelete() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/specials');
    $this->layout = FALSE;

    $item_id = (int) Yii::$app->request->post('item_id');

    $message = $name = $title = '';
    $parent_id = 0;

    $specials_query = tep_db_query("select p.products_id, s.specials_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'");
    $specials = tep_db_fetch_array($specials_query);

    $sInfo = new \objectInfo($specials);

    echo '<div class="or_box_head top_spec">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</div>';
    echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
    echo '<div class="col_desc"><strong>' . $sInfo->products_name . '</strong></div>';
    echo tep_draw_form('item_delete', FILENAME_SPECIALS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
    ?>
    <div class="btn-toolbar btn-toolbar-order">
    <?php
    echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
    echo '<button class="btn btn-cancel" onclick="return resetStatement()">' . IMAGE_CANCEL . '</button>';

    echo tep_draw_hidden_field('item_id', $item_id);
    ?>
    </div>
    </form>
    <?php
  }

  function actionItemdelete() {
    $this->layout = FALSE;

    $specials_id = (int) Yii::$app->request->post('item_id');

    $messageType = 'success';
    $message = TEXT_INFO_DELETED;

    tep_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . (int) $specials_id . "'");

    if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
      tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . tep_db_input($specials_id) . "'");
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


    <p class="btn-toolbar">
    <?php
    echo '<input type="button" class="btn btn-primary" value="' . IMAGE_BACK . '" onClick="return resetStatement()">';
    ?>
    </p>
    <?php
  }

  public function actionSpecialedit() {

    $specialsId = (int) Yii::$app->request->get('id');
    $productsId = (int) Yii::$app->request->get('products_id');
    $popup = (int) Yii::$app->request->get('popup', 0);
    $popupEdit = (int) Yii::$app->request->get('popup_edit', 0);
    $bp = Yii::$app->request->get('bp', []);

    $this->view->headingTitle = BOX_CATALOG_SPECIALS;
    $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
    $this->selectedMenu = array('marketing', 'specials');
    \common\helpers\Translation::init('admin/categories');

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials/index'), 'title' => BOX_CATALOG_SPECIALS);

    $params = [];
    $sInfo = $pInfo = null;
    if (!empty($specialsId) || !empty($productsId)) {
      $template = 'specialedit';
      $currencies = Yii::$container->get('currencies');
      
      if (!empty($specialsId) ) {
        $sInfo = \common\models\Specials::find()->andWhere(['specials_id' => $specialsId])->with(['prices', 'backendProductDescription'])->one();
        if (!empty($sInfo->specials_id)) {
          $pInfo = $sInfo->product;
          unset($sInfo->product);
        }
      }
      if (empty($sInfo->specials_id) && !empty($productsId)) {
        $pInfo = \common\models\Products::find()->andWhere(['products_id' => $productsId])->with(['backendDescription'])->one();
      }

      if (!empty($pInfo)) {
        //fill in tabs details
        $params['currencies'] =  $currencies;
        $_tax =  \common\helpers\Tax::get_tax_rate_value($pInfo->products_tax_class_id)/100;
        $_roundTo = $currencies->get_decimal_places(DEFAULT_CURRENCY);
        $params['sInfo'] =  (object)\yii\helpers\ArrayHelper::toArray($sInfo);
        $params['pInfo'] = (object)\yii\helpers\ArrayHelper::toArray($pInfo);
        if (!empty($sInfo->specials_id)) {
          $params['pInfo']->specials_id = $sInfo->specials_id;
        }

        $params['price'] = $currencies->format($pInfo->products_price);
        $params['priceGross'] = $currencies->format($pInfo->products_price+ round($pInfo->products_price*$_tax, 6), $_roundTo);
        $params['backendProductDescription'] = \yii\helpers\ArrayHelper::toArray($pInfo->backendDescription, ['products_name']);
        $params['default_currency'] = $currencies->currencies[DEFAULT_CURRENCY];
        //$this->view->defaultCurrency = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        $this->view->defaultSaleId = (empty($sInfo->specials_id)?0:$sInfo->specials_id);
        $this->view->defaultCurrency = $this->view->defaultCurrency ?? null;

        $priceViewObj = new ViewPriceData($pInfo);
        $priceViewObj->populateView($this->view);
        $this->view->tax_classes = [0 => TEXT_NONE];
        $tmp = \common\models\TaxClass::find()->select('tax_class_id, tax_class_title')->orderBy('tax_class_title')->asArray()->indexBy('tax_class_id')->all();
        if (!empty($tmp)) {
          $this->view->tax_classes += \yii\helpers\ArrayHelper::getColumn($tmp, 'tax_class_title');
        }

/// price and cost----
        /*
          if ( $pInfo->products_id_price && $pInfo->products_id!=$pInfo->products_id_price ) {
              $priceViewObj = new ViewPriceData(\common\models\Products::findOne($pInfo->products_id_price));
          } else {
              $priceViewObj = new ViewPriceData($pInfo);
          }
          $priceViewObj->populateView($this->view, $currencies);

*/

////--------------
          
// init price tabs
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
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
          $this->view->groups = [];
          /** @var \common\extensions\UserGroups\UserGroups $ext */
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
              $ext::getGroups();
          }

          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), array_filter($this->view->groups, function($e) { return $e['per_product_price']; }));
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            if (empty($value['apply_groups_discount_to_specials'])) {
                $value['groups_discount'] = 0;
            }
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
              'aboveTabs' => (!empty($sInfo->specials_id) && count($this->view->groups_m)<(1+count($this->view->groups))? '../productedit/edit-price-link.tpl':''),
              'all_hidden' => (count($this->view->groups_m)==1),
              'maxHeight' => '400px',
          ];
        }

        //$this->view->price_tabs['sInfo'] = $sInfo; //star, end dates, statu, flags are the same for all tabs now

        $languages_id = \Yii::$app->settings->get('languages_id');
        $specialsTypesArr = \common\models\SpecialsTypes::find()->where([
              'language_id' => $languages_id
            ])
            ->select('specials_type_name, specials_type_id')
            ->asArray()->indexBy('specials_type_id')->column();
        if (!is_array($specialsTypesArr)) {
          $specialsTypesArr = [];
        }
        $specialsTypesArr[0] = '';
        ksort($specialsTypesArr);
        $params['specials_types'] = $specialsTypesArr;

        $def = '';
        if (defined('SALES_DEFAULT_PROMO_TYPE')) {
          switch (SALES_DEFAULT_PROMO_TYPE) {
            case 'None':
              $def = ' (' . TEXT_DISABLED . ')';
            break;
            case 'Percent':
              $def = ' (' . TEXT_PERCENT . ')';
            break;
            case 'Fixed':
              $def = ' (' . TEXT_FIXED . ')';
            break;
          }
        }
        $params['promote_types'] = [
          -1 => TEXT_DISABLED,
          0 => TEXT_DEFAULT . $def,
          1 => TEXT_PERCENT,
          2 => TEXT_FIXED
        ];

        
      } else {
        $template = 'choose_product';
      }
    } else {
      
      $template = 'choose_product';
    }
    
    if ($popup) {
      $params['back_url'] = \Yii::$app->urlManager->createUrl(['specials/index-popup', 'prid' => $productsId]);
      //old jquery ajax with hash compatibility
      $hash =  \Yii::$app->request->get('_hash_', false);

      return $this->renderAjax($template, $params + ['popup' => 1, 'popup_edit' => $popupEdit, 'hash' => $hash]);
    } else {
      if (is_array($bp)) {
        $params['back_url'] = \Yii::$app->urlManager->createUrl(['specials'] + $bp);
      } else {
        $params['back_url'] = \Yii::$app->urlManager->createUrl(['specials']);
      }

      if ($template == 'choose_product') {
          $catalog =  new \backend\components\ProductsCatalog();
          return $catalog->make();
      }
      return $this->render($template, $params);
    }
  }

  public function actionSwitchStatus() {
    $id = (int)Yii::$app->request->post('id', 0);
    $status = (Yii::$app->request->post('status')== 'true' ? 1 : 0);
    if ($id > 0 ) {
      $special = \common\models\Specials::find()->andWhere(['specials_id' => $id])->one();
      if ($special && $special->status != $status) {
        try {
          // to disable active by date range you need to set disabled flag
          if ( !$status &&
              (empty($special->start_date) || ($special->start_date < date("Y-m-d H:i:s") )) // already started
              &&
              (!empty($special->expires_date) && ($special->expires_date >= date("Y-m-d H:i:s") )) // not expired

              ){
            $special->specials_disabled = 1;
          } elseif ( $status && $special->specials_disabled == 1 ){
            $special->specials_disabled = 0;
          }
          $special->status = $status;
          $special->date_status_change = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
          $special->save(false);
        } catch (\Exception $e) {
          \Yii::warning(" #### " . print_r($e->getMessage(), 1), 'TLDEBUG');
        }
      }
    }
  }


  public function actionDeleteSelected() {
    $this->layout = FALSE;

    $spIds = Yii::$app->request->post('bulkProcess', []);
    if (is_array($spIds) && !empty($spIds)) {
      $spIds = array_map('intval', $spIds);
      \common\models\Specials::deleteAll(['specials_id' => $spIds]);
      \common\models\SpecialsPrices::deleteAll(['specials_id' => $spIds]);
    }
  }


    /**
     * works only with customers groups.
     */
    public function actionProductPriceEdit() {

        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            return;
        }

        \common\helpers\Translation::init('admin/categories');
        $currencies = Yii::$container->get('currencies');
        $this->layout = false;

        $params = [];

        $params['currencies'] = $currencies;
        $params['currencies_id'] = $currencies_id = \Yii::$app->request->post('currencies_id', \Yii::$app->request->get('currencies_id', 0));
        $group_id = \Yii::$app->request->post('group_id', 0);
        $only_price = \Yii::$app->request->post('only_price', 0);

        $params['specials_id'] = $specialsId = (int) \Yii::$app->request->post('id', \Yii::$app->request->get('id', 0));
        $params['products_id'] = $productsId = (int) \Yii::$app->request->post('products_id', \Yii::$app->request->get('products_id', 0));
        $popup = (int) Yii::$app->request->get('popup', 0);

        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
        $this->view->headingTitle = BOX_CATALOG_SPECIALS;
        \common\helpers\Translation::init('admin/categories');

        
        $sInfo = $pInfo = null;
        $error = false;
        if (!empty($specialsId) && !empty($productsId)) {
            $template = 'specialedit-group-popup';
            $currencies = Yii::$container->get('currencies');

            if (!empty($specialsId)) {
                $sInfo = \common\models\Specials::find()->andWhere(['specials_id' => $specialsId])->with(['prices', 'backendProductDescription'])->one();
                if (!empty($sInfo->specials_id)) {
                    $pInfo = $sInfo->product;
                    unset($sInfo->product);
                }
            }
            if (empty($sInfo->specials_id) && !empty($productsId)) {
                $pInfo = \common\models\Products::find()->andWhere(['products_id' => $productsId])->with(['backendDescription'])->one();
            }

            if (!empty($pInfo)) {
              //fill in tabs details
              $params['sInfo'] =  (object)\yii\helpers\ArrayHelper::toArray($sInfo);
              $params['pInfo'] = (object)\yii\helpers\ArrayHelper::toArray($pInfo);
              if (!empty($sInfo->specials_id)) {
                $params['pInfo']->specials_id = $sInfo->specials_id;
              }
              $this->view->defaultSaleId = (empty($sInfo->specials_id)?0:$sInfo->specials_id);
              $this->view->defaultCurrency = $this->view->defaultCurrency ?? null;
            }


        } else {
            $error = true;
        }

        if ($error) {
            return '';
        }

////currencies tabs and params

        $this->view->price_tabs = $this->view->price_tabparams = [];
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
          $this->view->groups = [];
          /** @var \common\extensions\UserGroups\UserGroups $ext */
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
              $ext::getGroups();
          }

          $this->view->groups_m = $this->view->groups;
          $tabdata = $groups = $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            //unset($value['groups_id']);
            if (empty($value['apply_groups_discount_to_specials'])) {
                $value['groups_discount'] = 0;
            }
            $tmp[] = $value;
            if ($group_id == $value['id']) {
                $tabdata = $value;
            }
            if ($value['per_product_price']==0) {
                $groups[$value['id']] = $value['title'];
            }
          }
          //$this->view->price_tabs[] = $tmp;
          $this->view->price_tabs = $tabdata;
          unset($tmp);



        if ($only_price) {
            if ($group_id == 0) {
                return '';
            }


            if ( $pInfo->products_id_price && $pInfo->products_id != $pInfo->products_id_price ) {
                $priceViewObj = new ViewPriceData(\common\models\Products::findOne($pInfo->products_id_price));
            }else {
                $priceViewObj = new ViewPriceData($pInfo);
            }
            $priceViewObj->populateView($this->view);
            
            $this->view->tax_classes = [0 => TEXT_NONE];
            $tmp = \common\models\TaxClass::find()->select('tax_class_id, tax_class_title')->orderBy('tax_class_title')->asArray()->indexBy('tax_class_id')->all();
            if (!empty($tmp)) {
              $this->view->tax_classes += \yii\helpers\ArrayHelper::getColumn($tmp, 'tax_class_title');
            }

            if ($this->view->useMarketPrices) {
                $data = $this->view->price_tabs_data[$currencies_id][$group_id];
                $data['currencies_id'] = $currencies_id;
            } else {
                $data = $this->view->price_tabs_data[$group_id] ?? null;
            }
            $data['tabdata'] = $tabdata;
            $data['groups_id'] = $group_id;


            $this->ProductEditTabAccess->setProduct($pInfo);
            unset($this->view->price_tabs);
            unset($this->view->price_tabs_data);
            $this->view->price_tabs_data = $data;
            $params += [
                        'pInfo' => $pInfo,
                        'data' => $data,
                        'TabAccess' => $this->ProductEditTabAccess,
                        'idSuffix' => '_' . ($this->view->useMarketPrices?$currencies_id . '_':'') . $group_id,
                        'fieldSuffix' => ($this->view->useMarketPrices?'[' . $currencies_id . ']':'') . '[' . $group_id . ']',
                        'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
                        'only_price' => 1
                ];

            return $this->renderAjax($template, $params);
        } else {
            $groups = [0 => TEXT_CHOOSE_GROUP] + $groups;
            $params['groups'] = $groups;


            return $this->render($template, $params);
        }
    }
    
    public function actionGroupPriceSubmit() {

        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            return;
        }
/*

        products_id	"1240"
specials_id	"165"
currencies_id	"0"
group_id	"1"
special_price[cur_id]?[group_id]?	"9"
 */

        \common\helpers\Translation::init('admin/categories');
        $currencies = Yii::$container->get('currencies');
        $this->layout = false;
        $msg = '';
        $error = false;

        $currencies_id = \Yii::$app->request->post('currencies_id', 0);
        $group_id = \Yii::$app->request->post('group_id', 0);
        $specials_id = (int) \Yii::$app->request->post('specials_id', 0);
        $special_price = \Yii::$app->request->post('special_price', []);
        if (defined('USE_MARKET_PRICES') && (USE_MARKET_PRICES == 'True')) {
            $price = $special_price[$currencies_id][$group_id] ?? null;
        } else {
            $price = $special_price[$group_id] ?? null;
        }

        if (!is_null($price) && $price!='' && !empty($specials_id) && !empty($group_id)) {
            try {
                $spq = \common\models\SpecialsPrices::find()
                    ->andWhere([
                      'specials_id' => $specials_id,
                      'groups_id' => $group_id,
                      ]);
                if (!defined('USE_MARKET_PRICES') || (USE_MARKET_PRICES != 'True')) {
                    $currencies_id = 0;
                }
                $spq->andWhere([
                      'currencies_id' => $currencies_id
                    ]);

                $sp = $spq->one();
                if (empty($sp)) {
                    $sp = new \common\models\SpecialsPrices();
                    $sp->setAttributes([
                      'specials_id' => $specials_id,
                      'groups_id' => $group_id,
                      'currencies_id' => $currencies_id
                    ], false);
                }
                $sp->specials_new_products_price = $price;
                $sp->save(false);
            } catch (\Exception $e) {
                $error = true;
                $msg = $e->getMessage();
                \Yii::warning(" #### " .print_r($e->getMessage() . ' ' . $e->getTraceAsString(), true), 'TLDEBUG');
            }
            
        } else {
            $error = true;
            $msg = TEXT_ERROR_ON_SAVE;
        }


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => !$error, 'message' => $msg];






    }

}

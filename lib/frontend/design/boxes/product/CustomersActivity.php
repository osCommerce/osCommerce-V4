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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CustomersActivity extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $params = Yii::$app->request->get();
    $ret = '';

    if ($params['products_id']) {

        $str = md5(\common\helpers\System::get_ip_address() . $_SERVER['HTTP_USER_AGENT']);
        $parameters = ['products_id' => (int)$params['products_id'],
                       'customers_ip' => $str,
          ];
        try {
          $q = (new \yii\db\Query())->createCommand();
          if ((new \yii\db\Query())->from(TABLE_CURRENTLY_VIEWING)->where($parameters)->count()) {
            $q->update(TABLE_CURRENTLY_VIEWING, ['last_click' => (new \yii\db\Expression('now()'))], $parameters)->execute();
          } else {
            $q->insert(TABLE_CURRENTLY_VIEWING, $parameters)->execute();
          }
          (new \yii\db\Query())->createCommand()->delete(TABLE_CURRENTLY_VIEWING, 'last_click < date_sub(now(), INTERVAL 15 MINUTE) ')->execute();
        } catch (\Exception $ex) {
          \Yii::info($ex->getMessage(), TABLE_CURRENTLY_VIEWING . ' ERROR');
        }


/// NNN viewing 
      $vc = (new \yii\db\Query())->from(TABLE_CURRENTLY_VIEWING)->where(['products_id' => (int)$params['products_id']])->count();

/// NNN in shopping carts
      $sc = (new \yii\db\Query())->from(TABLE_CUSTOMERS_BASKET)->where(['like', 'products_id', (int)$params['products_id'] . '%', false])->count();
      if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE =='true') {
          $freezePrefix = '';
          if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
            $freezePrefix = 'freeze_';
          }
        $sc += (new \yii\db\Query())->from($freezePrefix . \common\models\OrdersProductsTemporaryStock::tableName())->where(['like', 'products_id', (int)$params['products_id'] . '%', false])->count();
      }
      //marketing values
      if (defined('PRODUCTS_ACTIVITY_VIEW_MIN') && intval(PRODUCTS_ACTIVITY_VIEW_MIN)>0) {
        if (defined('PRODUCTS_ACTIVITY_VIEW_COEFFICIENT') && floatval(PRODUCTS_ACTIVITY_VIEW_COEFFICIENT)>0) {
          $vc = round($vc * floatval(PRODUCTS_ACTIVITY_VIEW_COEFFICIENT));
        }
        if ($vc == 0) {
          $vc = rand(0, intval(PRODUCTS_ACTIVITY_VIEW_MIN)) + intval(PRODUCTS_ACTIVITY_VIEW_MIN);
        } else {
          $vc = max($vc, intval(PRODUCTS_ACTIVITY_VIEW_MIN));
        }
      }
      if (defined('PRODUCTS_ACTIVITY_VIEW_MAX') && intval(PRODUCTS_ACTIVITY_VIEW_MAX)>0) {
        $vc = min($vc, intval(PRODUCTS_ACTIVITY_VIEW_MAX));
      }
      //marketing values
      if (defined('PRODUCTS_ACTIVITY_CART_MIN') && intval(PRODUCTS_ACTIVITY_CART_MIN)>0) {
        if (defined('PRODUCTS_ACTIVITY_CART_COEFFICIENT') && floatval(PRODUCTS_ACTIVITY_CART_COEFFICIENT)>0) {
          $sc = round($sc * floatval(PRODUCTS_ACTIVITY_CART_COEFFICIENT));
        }
        if ($sc == 0) {
          $sc = rand(0, intval(PRODUCTS_ACTIVITY_CART_MIN)) + intval(PRODUCTS_ACTIVITY_CART_MIN);
        } else {
          $sc = max($sc, intval(PRODUCTS_ACTIVITY_CART_MIN));
        }
      }
      if (defined('PRODUCTS_ACTIVITY_CART_MAX') && intval(PRODUCTS_ACTIVITY_CART_MAX)>0) {
        $sc = min($sc, intval(PRODUCTS_ACTIVITY_CART_MAX));
      }

      if ($sc > $vc) {
        $vc = $sc;
      }

      if ($vc>1 || $sc>1) {
        $ret = IncludeTpl::widget(['file' => 'boxes/product/customers-activity.tpl', 'params' => [
          'viewing' => $vc-1,
          'purchasing' => $sc,
          'params'=> $this->params,
          'id' => $this->id,
          'widgetUrl' => Yii::$app->urlManager->createUrl(['get-widget/one', 'products_id' => $params['products_id']])
        ]]);
      }

    } 
    return $ret;
    
  }
}
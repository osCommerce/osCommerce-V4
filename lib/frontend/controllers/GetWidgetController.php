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
use frontend\design\Info;

/**
 * Site controller
 */
class GetWidgetController extends Sceleton
{

    public function actionIndex()
    {
        $params = tep_db_prepare_input(Yii::$app->request->post());

        $response = array();
        foreach ($params as $widget) {
            $widget_name = 'frontend\design\boxes\\' . $widget['name'];
            $response[] = $widget_name::widget(['params' => $widget['params']]);
        }

        return json_encode($response);
    }

    public function actionOne()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());
        $get['admin'] = $get['admin'] ?? false;
        $items_query = tep_db_query("select id, widget_name, widget_params from " . ($get['admin'] ? TABLE_DESIGN_BOXES_TMP : TABLE_DESIGN_BOXES) . " where id = '" . (int)$get['id'] . "'");
        if ($item = tep_db_fetch_array($items_query)){

            \common\helpers\Translation::init($get['action']);



            $block = '';
            $widget_array = array();

            $settings = array();
            $settings_query = tep_db_query("select * from " . ($get['admin'] ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where box_id = '" . (int)$item['id'] . "'");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }

            Info::addBlockToWidgetsList($item['widget_name']);

            if (class_exists('frontend\design\boxes\\' . $item['widget_name'])) {
                $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];
            } elseif ($ext_widget = \common\helpers\Acl::checkExtension($item['widget_name'], 'run', true)){
                $widget_name = $ext_widget;
            } else {
                return '';
            }

            $widget_array['id'] = (int)$item['id'];

            $settings[0]['params'] = $item['widget_params'];
            $widget_array['settings'] = $settings;

            $cookies = Yii::$app->request->cookies;

            if (
                !(
                    !($settings[0]['visibility_first_view'] ?? false) && Yii::$app->user->isGuest && !$cookies->has('was_visit') ||
                    !($settings[0]['visibility_more_view'] ?? false) && Yii::$app->user->isGuest && $cookies->has('was_visit') ||
                    !($settings[0]['visibility_logged'] ?? false) && !Yii::$app->user->isGuest ||
                    !($settings[0]['visibility_not_logged'] ?? false) && Yii::$app->user->isGuest
                ) ||

              Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_home'] ?? false) ||
              Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' && ($settings[0]['visibility_product'] ?? false) ||
              Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_catalog'] ?? false) ||
              Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_info'] ?? false) ||
              Yii::$app->controller->id == 'cart' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_cart'] ?? false) ||
              Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' && ($settings[0]['visibility_checkout'] ?? false) ||
              Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' && ($settings[0]['visibility_success'] ?? false) ||
              Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' && ($settings[0]['visibility_account'] ?? false) ||
              Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login' && ($settings[0]['visibility_login'] ?? false)
            ){
            } elseif(
              !(Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'design' ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'cart' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login') &&
              ($settings[0]['visibility_other'] ?? false)
            ) {
            } else {

                if (isset($get['to_pdf']) && $get['to_pdf']) {
                    $settings[0]['p_width'] = Info::blockWidth($item['id']);
                }

                $widget = $widget_name::widget($widget_array);


                if ($widget == ''){
                    if ($get['admin']) $block .= '<div class="no-widget-name">Here added ' . $item['widget_name'] . ' widget</div>';
                } else {
                    $block .= $widget;
                }

            }

            if(!(isset($get['get_json']) && $get['get_json'])) {


                $block .= '
            <script type="text/javascript">
  if (typeof cssArray === "undefined") {
    var cssArray = [];
  }
  tl(function(){
    ';

                foreach (Info::getCssArray(THEME_NAME, '.p-' . Yii::$app->controller->id . '-' . Yii::$app->controller->action->id) as $key => $item) {
                    $block .= '
    if (!cssArray["' . addslashes($key) . '"]) {
      cssArray["' . addslashes($key) . '"] = 1;
      $("style:last").append("' . str_replace("\n", '', addslashes($item)) . '")
    }
    
    ';
                }

                $block .= '
  })
</script>
            ';
            }


        }

        return $block;
    }

    public function actionOnePost()
    {
        $post = tep_db_prepare_input(Yii::$app->request->post());

        $widget = '';

        $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where id = '" . (int)$post['id'] . "'");
        if ($item = tep_db_fetch_array($items_query)){

            $widget_array = array();

            $settings = array();
            $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }

            $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];

            $widget_array['id'] = $item['id'];

            $settings[0]['params'] = $item['widget_params'];
            $widget_array['settings'] = $settings;


            $widget = $widget_name::widget($widget_array);

        }

        return $widget;
    }

    public function actionExtension() {
        $id = \Yii::$app->request->post('id', \Yii::$app->request->get('id'));

        $widget = '';

        $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where id = '" . (int) $id . "'");
        if ($item = tep_db_fetch_array($items_query)) {

            $widget_array = array();

            $settings = array();
            $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int) $item['id'] . "'");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }

            $widget_array['id'] = $item['id'];

            $settings[0]['params'] = $item['widget_params'];
            $widget_array['settings'] = $settings;

            if (($ext_widget = \common\helpers\Acl::runExtensionWidget($item['widget_name'], $widget_array)) !== false) {
                $widget = $ext_widget;
            }
        }

        return $widget;
    }

    public function actionCssSave()
    {
        $params = Yii::$app->request->post();
        $params['widget'] = 'all';
        $params['theme_name'] = THEME_NAME;

        $filePath = DIR_FS_CATALOG . 'themes/' . THEME_NAME . '/css/';
        $params['css'] = file_get_contents($filePath . 'develop.css');

        return \backend\design\Style::cssSave($params);
    }

}

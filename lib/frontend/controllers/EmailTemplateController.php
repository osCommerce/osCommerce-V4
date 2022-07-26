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

use frontend\design\Info;
use Yii;
/**
 * Site controller
 */
class EmailTemplateController extends Sceleton
{

    public function actionIndex()
    {
        $this->layout = false;

        $page_name = 'email';
        $params = tep_db_prepare_input(Yii::$app->request->get());
        if (isset($params['page_name'])){

            $templates = \common\models\ThemesSettings::find()
                ->select(['setting_value'])
                ->where([
                    'theme_name' => THEME_NAME,
                    'setting_group' => 'added_page',
                    'setting_name' => 'email',
                ])
                ->asArray()
                ->all();

            foreach ($templates as $template) {
                if (\common\classes\design::pageName($template['setting_value']) == $params['page_name']) {
                    $page_name = $params['page_name'];
                }
            }

        }

        $page_name = \common\classes\design::pageName($page_name);
        return $this->render('index.tpl', [
            'page_name' => $page_name,
            'params' => ['absoluteUrl' => true]
        ]);
        //return $this->render('index.tpl', ['description' => stripslashes($row['description']), 'title' => $title]);
    }

    public function actionVirtualGiftCardTemplate() {
        $this->layout = false;
        return $this->render('virtual-gift-card-template.tpl');
        
    }


    public function actionOrderTotals() {

        \common\helpers\Translation::init('email-template');

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        $currencies = \Yii::$container->get('currencies');

        $order = new \common\classes\Order($oID);

        $key = Yii::$app->request->get('key');
        /*if ($_SESSION['customer_id'] != $order->customer['id'] && !Info::isAdmin() && $key != 'UNJfMzvmwE6EVbL6') {
            return false;
        }*/

        if ($_GET['theme_name']) {
            $theme = $_GET['theme_name'];
        } else {
            $theme_array = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES . " where is_default = 1"));
            if ($theme_array['theme_name']){
                $theme = $theme_array['theme_name'];
            } else {
                $theme = 'theme-1';
            }
        }
        define('THEME_NAME', $theme);

        return \frontend\design\boxes\invoice\Totals::widget([
            'params' => [
              'order' => $order,
              'params' => [
                'order' => $order,
                'currencies' => $currencies,
                'oID' => $oID
              ],
              'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
              'oID' => $oID,
              'currencies' => $currencies,]

        ]);

    }
}

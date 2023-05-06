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

use common\components\InformationPage;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class InfoController extends Sceleton
{

    public function actionIndex()
    {
        global $breadcrumb;
        $languages_id = \Yii::$app->settings->get('languages_id');

        $info_id = (int)Yii::$app->request->get('info_id', 0);
        if(!$info_id) {
            throw new NotFoundHttpException('Page not found.');
        }

        if (!\common\helpers\PageStatus::isStatus('public', 'information', $info_id)) {
            throw new NotFoundHttpException('Page not found.');
        }

        $row = InformationPage::getFrontendDataVisible((int)$info_id);

        if (!is_array($row)) {
            throw new NotFoundHttpException('Page not found.');
        }

        if ($row['page_title'] == ''){
            $title = $row['info_title'];
        }else{
            $title = $row['page_title'];
        }
        if ( $title ) {
            $breadcrumb->add($title, tep_href_link(FILENAME_INFORMATION, 'info_id=' . $row['information_id']));
        }
        $params = tep_db_prepare_input(Yii::$app->request->get());
        if (isset($params['page_name']) && $params['page_name']){
            $page_name = $params['page_name'];
        } elseif (isset($row['template_name']) && $row['template_name']) {
            $page_name = $row['template_name'];
        } else {
            $page_name = 'info';
        }

        if ($page_name == '0_blank') {
            \frontend\design\Info::addBoxToCss('hidden-boxes');
        }

        \common\helpers\Seo::showNoindexMetaTag($row['noindex_option'], $row['nofollow_option']);
        if (!empty($row['rel_canonical'])) {
            \app\components\MetaCannonical::instance()->setCannonical($row['rel_canonical']);
        }

        $page_name = \common\classes\design::pageName($page_name);

        \frontend\design\Info::addBlockToPageName($page_name);

        $this->view->page_name = $page_name;

        return $this->render('index.tpl', [
            'description' => $row['description'],
            'title' => $title,
            'page' => 'info',
            'page_name' => $page_name
        ]);
    }

    public function actions()
    {
        $action = filter_var(Yii::$app->request->get('action', ''), FILTER_SANITIZE_STRING);
        // $params = array_diff($_GET, [$action]); may be cause of the error: convert string to array
        if (isset($_GET['action'])) unset($_GET['action']);
        $params = $_GET;
        return [
            'custom' => [
                'class' => '\frontend\controllers\CustomPageAction',
                'action' => $action,
                'params' => $params,
            ],
        ];
    }
    
    public function getHerfLang($platforms_languages){
        $pages = tep_db_query("select seo_page_name, languages_id from " . TABLE_INFORMATION . " where platform_id = '" . (int)PLATFORM_ID . "' and visible = 1 and information_id = '" . (int)$_GET['info_id'] . "' and languages_id in (" . implode(",", array_values($platforms_languages)) . ")");
        $list = $except = [];
        if (tep_db_num_rows($pages)){
            while($page = tep_db_fetch_array($pages)){
                if (!empty($page['seo_page_name'])){
                    $except[] = $_GET['info_id'];
                }
                $list[$page['languages_id']] = [$page['seo_page_name'], $except];
            }
        }
        return $list;
    }

    public function actionComponents()
    {
        $page_name = Yii::$app->request->get('page_name');

        return $this->render('components.tpl', [
            'page_name' => $page_name
        ]);
    }
}

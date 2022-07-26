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
use common\components\GoogleTools;
use common\classes\platform;

/**
 * default controller to handle user requests.
 */
class Google_analyticsController extends Sceleton {

    public $acl = ['BOX_HEADING_SEO', 'BOX_HEADING_GOOGLE_ANALYTICS'];

    /** @prop common\components\google\ModuleProvider $modulesProvider */
    private $modulesProvider;

    public function __construct($id, $module, GoogleTools $tool) {
        \common\helpers\Translation::init('admin/google_analytics');
        parent::__construct($id, $module);
        $this->modulesProvider = $tool->getModulesProvider();
    }

    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('seo_cms', 'google_analytics');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('google_analytics/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        $this->view->tabListReserved = [];
        $this->view->tabList = [];
        $this->view->row_id = Yii::$app->request->get('row_id', 0);

        $platforms = platform::getList(false);

        if (is_array($platforms)) {
            foreach ($platforms as $_platform) {
                $this->view->tabList[$_platform['id']] = [
                    array(
                        'title' => 'Module Name',
                        'not_important' => 0,
                    ),
                    array(
                        'title' => TABLE_HEADING_STATUS,
                        'not_important' => 3
                    ),
                    array(
                        'title' => TABLE_HEADING_ACTION,
                        'not_important' => 0
                    ),
                ];
            }
        }

        if (is_array(Yii::$app->session->getAllFlashes())) {
            foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                Yii::$app->controller->view->errorMessage = $message;
                Yii::$app->controller->view->errorMessageType = $key;
            }
        }

        Yii::$app->session->removeAllFlashes();

        $platfrom_id = Yii::$app->request->get('platform_id', platform::firstId());

        return $this->render('index', [
                    'platforms' => $platforms,
                    'first_platform_id' => $platfrom_id,
                    'isMultiPlatform' => platform::isMulti(),
        ]);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $search = Yii::$app->request->get('search', '');
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 15);
        $platform_id = Yii::$app->request->get('platform_id', 0);

        $modules = [];

        if ($platform_id) {
            foreach($this->modulesProvider->getInstalledModules($platform_id) as $module){
                $modules[] = array(
                    '<div class="simple_row click_double"><div class="module_title' . ($module->params['status'] ? '' : ' dis_module') . '">' . $module->params['module_name'] . tep_draw_hidden_field('module', $module->code, 'class="cell_identify" data-installed="true"') . '</div></div>',
                    '<input name="enabled" type="checkbox" data-module="' . $module->code . '" data-platform_id="' . $platform_id . '" class="check_on_off" ' . ($module->params['status'] ? 'checked' : '') . '><script>BootstrapIt(\'' . $module->code . '\', ' . $platform_id . ')</script>',
                    '<a href="' . \yii\helpers\Url::to(['google_analytics/settings', 'id' => $module->params['google_settings_id']]) . '" class="btn btn-primary btn-small btn-edit" title="' . IMAGE_EDIT . '">&nbsp;' . IMAGE_EDIT . '</a>&nbsp;<button class="btn btn-small" onClick="changeModule(\'' . $module->code . '\', ' . $platform_id . ', \'remove\')" title="' . TEXT_REMOVE . '">' . TEXT_REMOVE . '</button>'
                );
            }
            
            foreach ($this->modulesProvider->getUninstalledModules($platform_id) as $code => $module) {
                $modules[] = array(
                    '<div class="simple_row click_double"><div class="module_title dis_module">' . $module['name'] . tep_draw_hidden_field('module', $code, 'class="cell_identify" data-installed="true"') . '</div></div>',
                    '',
                    '<button class="btn btn-default btn-small" onClick="changeModule(\'' . $code . '\', ' . $platform_id . ', \'install\')">' . IMAGE_INSTALL . '</button>'
                );
            }
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => count($modules),
            'recordsFiltered' => count($modules),
            'data' => $modules,
            'head' => new \stdClass(),
        );
        echo json_encode($response);
    }

    public function actionChange() {
        $action = Yii::$app->request->post('action');
        $module = Yii::$app->request->post('module');
        $platform_id = Yii::$app->request->post('platform_id', 0);
        $status = Yii::$app->request->post('status', 'false') == 'true' ? 1 : 0;
        if ($platform_id) {
            $this->modulesProvider->perform($module, $action, $platform_id, $status);
        }
        echo 'ok';
    }

    public function actionSettings() {
        global $language;

        $id = Yii::$app->request->get('id', 0);

        $this->selectedMenu = array('seo_cms', 'google_analytics');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('google_analytics/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        $this->view->row_id = Yii::$app->request->get('row_id', 0);
        $this->view->platform_id = Yii::$app->request->get('platform_id', platform::defaultId());

        $context = '';
        if ($module = $this->modulesProvider->getInstalledById($id, true)) {
            $context = $module->render();
        }

        return $this->render('edit.tpl', ['context' => $context, 'id' => $id]);
    }

    public function actionSave() {

        if (Yii::$app->request->isPost) {

            $id = Yii::$app->request->post('id', 0);

            $module = $this->modulesProvider->getInstalledById($id, false);

            if ($module && $module->loaded(Yii::$app->request->post())) {
                $this->modulesProvider->save($module);
            }

            Yii::$app->session->setFlash('success', ICON_SUCCESS);
        }
        $row_id = Yii::$app->request->get('row_id', 0);
        $platform_id = Yii::$app->request->get('platform_id', platform::firstId());

        return $this->redirect(\yii\helpers\Url::to(['index', 'row_id' => $row_id, 'platform_id' => $platform_id]));
    }

}

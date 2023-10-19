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
use yii\web\Controller;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Sceleton extends Controller {

    public $enableCsrfValidation = false;
    
    /**
     * @var array the breadcrumbs of the current page.
     */
    public $navigation = array();

    /**
     * @var array 
     */
    public $topButtons = array();

    /**
     * @var stdClass the variables for smarty.
     */
    public $view = null;
    
    /**
     * Access Control List
     * @var array current access level
     */
    public $acl = null;

    /**
     * Selected items in menu
     * @var array 
     */
    public $selectedMenu = array();
    
    function __construct($id,$module=null) {
        if (($this->acl[0] ?? null) === 'BOX_HEADING_DEPARTMENTS') {
            //skip superadmin menu
        } elseif (!is_null($this->acl)) {
            $lastElement = is_array($this->acl) ? end($this->acl) : $this->acl;
            $wtf = \common\helpers\AdminBox::buildNavigation($lastElement);
            if (!empty($wtf)) {
                $this->acl = $wtf; // have no idea why $this->acl was always overrided before
            }
            \common\helpers\Acl::checkAccess($this->acl);
        }
        $this->layout = 'main.tpl';
        \Yii::$app->view->title = \Yii::$app->name;
        $this->view = new \stdClass();
        $this->view->translations = null;
        $this->view->headingTitle = null;
        $this->view->notificationCount = 0;
        $this->view->errorMessage = null;
        $this->view->usePopupMode = null;

        \common\helpers\MenuHelper::categoriesToMenuMessage();
        
        \common\helpers\Admin::appShopConnectedMessage();

        return parent::__construct($id,$module);
    }

    public function bindActionParams($action, $params)
    {
        if ($action->id == 'index') {
            \common\helpers\Translation::init('admin/' . $action->controller->id);
        } else {
            \common\helpers\Translation::init('admin/' . $action->controller->id . '/' . $action->id);
        }
        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('main');
        return parent::bindActionParams($action, $params);
    }
    
    public function beforeAction($action) {
        $events = new \backend\components\AdminEvents();
        $events->registerNotificationEvent();
        return parent::beforeAction($action);
    }
    
    public function actions() {
        $actions = parent::actions();
        $actions = array_merge($actions, \common\helpers\Acl::getExtensionActions($this->id));
        return $actions;
    }

    public function render($view, $params = [])
    {
        if (isset($this->navigation)) {
            $lastElement = end($this->navigation);
            if (isset($lastElement['title'])) {
                \Yii::$app->view->title  = strip_tags($lastElement['title']) . ' | '. \common\classes\platform::name(\common\classes\platform::defaultId()) .' | ' . \Yii::$app->name;
            }
        }
        \backend\design\Data::mainData();

        return parent::render($view, $params);
    }
}
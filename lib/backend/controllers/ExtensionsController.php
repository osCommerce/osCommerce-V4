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
use \common\helpers\Acl;

class ExtensionsController extends Sceleton
{
    function __construct($id, $mod=null) {
        $module = Yii::$app->request->get('module');
        if ($ext = \common\helpers\Acl::checkExtension($module, 'acl')) {
            $this->acl = $ext::getAcl('adminActionIndex');
        }
        parent::__construct($id, $mod);
    }
    
    public function actionIndex()
    {
        $module = Yii::$app->request->get('module');
        $action = Yii::$app->request->get('action', 'adminActionIndex');
        $errMsg = null;
        if ($ext = Acl::checkExtension($module, $action)) {
            if ($ext::allowed()) {
                if (method_exists($ext, 'initTranslation')) {
                    $ext::initTranslation('init_beforeaction');
                }
                if ($action!='actionRefreshTranslation' && !empty($acl=$ext::getAcl($action))) {
                    $this->acl = $acl;
                    Acl::checkAccess($acl);
                }
                if (!method_exists($ext, 'beforeAction') || $ext::beforeAction($action)) {
                    return $ext::$action();
                }
            } else {
                $errMsg = 'Extension is not allowed: ' . \yii\helpers\Html::encode($module);
            }
        } else {
            if (Acl::checkExtension($module)) {
                $errMsg = 'Extension has not this action: ' . \yii\helpers\Html::encode($module) . '::' . \yii\helpers\Html::encode($action);
            } else {
                $errMsg = 'Extension does not exist: ' . \yii\helpers\Html::encode($module);
            }
        }
        if (!empty($errMsg)) {
            \Yii::error($errMsg);
            if (\common\helpers\System::isDevelopment()) {
                die($errMsg);
            }
        }
    }

}
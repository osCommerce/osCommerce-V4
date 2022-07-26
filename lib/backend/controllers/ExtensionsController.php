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

class ExtensionsController extends Sceleton
{
    function __construct($id, $mod=null) {
        $module = Yii::$app->request->get('module');
        if ($ext = \common\helpers\Acl::checkExtension($module, 'acl')) {
            $this->acl = $ext::acl();
        }
        return parent::__construct($id, $mod);
    }
    
    public function actionIndex()
    {
        $module = Yii::$app->request->get('module');
        $action = Yii::$app->request->get('action', 'adminActionIndex');
        if ($ext = \common\helpers\Acl::checkExtension($module, $action)) {
            if ($ext::allowed()) {
                if (method_exists($ext, 'initTranslation')) {
                    $ext::initTranslation('init_beforeaction');
                }
                return $ext::$action();
            } elseif (\common\helpers\System::isDevelopment()) {
                die ('You have not rights for this extension: ' . \yii\helpers\Html::encode($module));
            }
        } elseif (\common\helpers\System::isDevelopment()) {
            die ('Extension has not this action: ' . \yii\helpers\Html::encode($module) . '::' . \yii\helpers\Html::encode($action));
        }
    }

}
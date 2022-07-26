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

namespace backend\components;

use Yii;

class AdminEvents {
    
    public $enabled;
    
    public function __construct() {
        $this->enabled = true;
    }
    
    public function registerNotificationEvent(){
        if ($this->enabled){
            $_notifier = new \backend\models\AdminNotifier();
            if ($_count = $_notifier->getUnreadCount()){
                if (!Yii::$app->request->isAjax){
                    Yii::$app->controller->view->notificationCount = $_count;
                } else {
                    Yii::$app->on('notifier-list', function() use ($_notifier) {
                        Yii::$app->response->clearOutputBuffers();
                        echo \backend\widgets\AdminNotifierWidget::widget(['notifier' => $_notifier]);
                        exit();
                    });
                    if (Yii::$app->request->get('action') == 'show-notifier-list'){
                        Yii::$app->trigger('notifier-list');
                    }
                }
            }
        }
    }
    
}
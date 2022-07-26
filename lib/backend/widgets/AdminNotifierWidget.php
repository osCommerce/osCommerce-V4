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

namespace backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class AdminNotifierWidget extends Widget {
    
    public $notifier;
    
    public function init() {
        parent::init();
    }

    public function run() {
                
        $notifications = $this->notifier->getUnreadNotifications();
        $response = '';
        if ($notifications){
            $notificationsByType = ArrayHelper::index($notifications, null, 'type');
            foreach($notificationsByType as $type => $notifications){
                $items = [];
                foreach($notifications as $notification){
                    $items[] =  $notification->date_added . "  ". TEXT_MESSAGE . " " . $notification->message;
                }
                $response .= Html::ul($items, ['class' => "admin-notifications-list alert-{$type}"]);
            }
            return $response;
        }
        return;
    }

}
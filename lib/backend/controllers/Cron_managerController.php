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

/**
 * default controller to handle user requests.
 */
class Cron_managerController extends Sceleton  {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_CRON_MANAGER'];
    
    public function actionIndex() {
      
        $this->selectedMenu = array('settings', 'cron_manager');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cron_manager/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
      
        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', array('messages' => $messages));
      
    }
    
}

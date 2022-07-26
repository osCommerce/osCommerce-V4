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

class Server_infoController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_SERVER_INFO'];
    
    public function actionIndex() {

        $this->selectedMenu = array('settings', 'tools', 'server_info');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('server_info/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();

		$phpinfo = str_replace('border: 1px', '', $phpinfo);
		preg_match('/<body>(.*)<\/body>/is', $phpinfo, $regs);
        return $this->render('index', array('system' => \common\helpers\System::get_system_information(), 'reg'=> $regs[1]));
    }

}

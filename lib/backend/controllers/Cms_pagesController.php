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
use backend\components\Information;

class Cms_pagesController extends Sceleton {

    public function actionIndex() {
        $this->selectedMenu = array('cms', 'cms_pages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cms_pages/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        return $this->render('index');
    }

}

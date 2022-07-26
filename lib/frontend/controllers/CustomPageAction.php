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

use Yii;
use yii\web\ViewAction;

/**
 * Site custom action 
 */
class CustomPageAction extends ViewAction
{

    public $action;
    public $params = [];
    
    public function run()
    {   
        $modify = $this->action;
        $modify = strtolower($modify);
        $modify = str_replace(' ', '_', $modify);
        $modify = preg_replace('/[^a-z0-9_-]/', '', $modify);
        $this->action = $modify;
        return $this->render('custom', ['params' => $this->params]);
    }
}

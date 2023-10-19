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

namespace frontend\design\boxes\order;

use common\helpers\Html;
use Yii;
use yii\base\Widget;

class OrderNumber extends Widget
{
    public $file;
    public $params;
    public $settings;
    public $manager;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        \common\helpers\Translation::init('email-template');

        return TITLE_PRINT_ORDER . $this->params['order']->info['orders_id'];
    }
}